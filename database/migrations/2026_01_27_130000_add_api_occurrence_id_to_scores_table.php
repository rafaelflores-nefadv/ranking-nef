<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->uuid('api_occurrence_id')->nullable()->after('id');
            $table->unique('api_occurrence_id');
            $table->foreign('api_occurrence_id')
                ->references('id')
                ->on('api_occurrences')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->dropForeign(['api_occurrence_id']);
            $table->dropUnique(['api_occurrence_id']);
            $table->dropColumn('api_occurrence_id');
        });
    }
};

