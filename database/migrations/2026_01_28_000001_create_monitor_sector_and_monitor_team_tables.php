<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitor_sector', function (Blueprint $table) {
            $table->uuid('monitor_id');
            $table->uuid('sector_id');
            $table->timestamps();

            $table->primary(['monitor_id', 'sector_id']);
            $table->foreign('monitor_id')->references('id')->on('monitors')->onDelete('cascade');
            $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('restrict');

            $table->index('monitor_id');
            $table->index('sector_id');
        });

        Schema::create('monitor_team', function (Blueprint $table) {
            $table->uuid('monitor_id');
            $table->uuid('team_id');
            $table->timestamps();

            $table->primary(['monitor_id', 'team_id']);
            $table->foreign('monitor_id')->references('id')->on('monitors')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');

            $table->index('monitor_id');
            $table->index('team_id');
        });

        // Backfill: manter compatibilidade com monitores existentes
        $now = now();
        $monitors = DB::table('monitors')->select(['id', 'sector_id', 'settings'])->get();

        foreach ($monitors as $monitor) {
            if (!empty($monitor->sector_id)) {
                DB::table('monitor_sector')->updateOrInsert(
                    [
                        'monitor_id' => $monitor->id,
                        'sector_id' => $monitor->sector_id,
                    ],
                    [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            $settings = [];
            if (!empty($monitor->settings)) {
                $decoded = json_decode($monitor->settings, true);
                if (is_array($decoded)) {
                    $settings = $decoded;
                }
            }

            $teamIds = $settings['teams'] ?? [];
            if (!is_array($teamIds)) {
                $teamIds = [];
            }

            foreach ($teamIds as $teamId) {
                if (!$teamId) {
                    continue;
                }
                DB::table('monitor_team')->updateOrInsert(
                    [
                        'monitor_id' => $monitor->id,
                        'team_id' => $teamId,
                    ],
                    [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('monitor_team');
        Schema::dropIfExists('monitor_sector');
    }
};

