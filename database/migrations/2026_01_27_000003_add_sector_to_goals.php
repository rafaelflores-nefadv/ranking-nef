<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->uuid('sector_id')->nullable()->after('id');
            $table->index('sector_id');
            $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('restrict');
        });

        $defaultSectorId = DB::table('sectors')->where('slug', 'geral')->value('id');
        if ($defaultSectorId) {
            DB::table('goals')->update(['sector_id' => $defaultSectorId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropIndex(['sector_id']);
            $table->dropColumn('sector_id');
        });
    }
};
