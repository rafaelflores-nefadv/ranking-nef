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
        Schema::table('seasons', function (Blueprint $table) {
            $table->enum('recurrence_type', ['daily', 'weekly', 'monthly', 'bimonthly', 'quarterly', 'semiannual', 'annual', 'fixed_date', 'days'])->nullable()->after('ends_at');
            $table->date('fixed_end_date')->nullable()->after('recurrence_type');
            $table->integer('duration_days')->nullable()->after('fixed_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->dropColumn(['recurrence_type', 'fixed_end_date', 'duration_days']);
        });
    }
};
