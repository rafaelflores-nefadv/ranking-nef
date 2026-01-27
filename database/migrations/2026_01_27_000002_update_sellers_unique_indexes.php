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
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropUnique('sellers_email_unique');
            $table->unique(['sector_id', 'email'], 'sellers_sector_email_unique');
            $table->unique(['sector_id', 'external_code'], 'sellers_sector_external_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropUnique('sellers_sector_email_unique');
            $table->dropUnique('sellers_sector_external_code_unique');
            $table->unique('email');
        });
    }
};
