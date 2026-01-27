<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_integrations', function (Blueprint $table) {
            $table->uuid('sector_id')->nullable()->after('id');
            $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('restrict');
        });

        $defaultSectorId = DB::table('sectors')
            ->where('is_active', true)
            ->orderBy('created_at')
            ->value('id');

        $integrations = DB::table('api_integrations')->select('id')->get();
        foreach ($integrations as $integration) {
            $tokenSectorId = DB::table('api_tokens')
                ->where('integration_id', $integration->id)
                ->whereNotNull('sector_id')
                ->orderBy('created_at')
                ->value('sector_id');

            $sectorId = $tokenSectorId ?: $defaultSectorId;

            if ($sectorId) {
                DB::table('api_integrations')
                    ->where('id', $integration->id)
                    ->update(['sector_id' => $sectorId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('api_integrations', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropColumn('sector_id');
        });
    }
};
