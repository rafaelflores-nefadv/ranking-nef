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
        Schema::create('goals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('scope', ['global', 'team', 'seller'])->default('global');
            $table->uuid('season_id');
            $table->uuid('team_id')->nullable();
            $table->uuid('seller_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('target_value', 12, 2);
            $table->date('starts_at');
            $table->date('ends_at');
            $table->timestamps();

            $table->foreign('season_id')->references('id')->on('seasons')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('cascade');

            $table->index(['scope', 'season_id']);
            $table->index(['team_id', 'season_id']);
            $table->index(['seller_id', 'season_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
