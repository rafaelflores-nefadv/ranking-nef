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
        // Índices para sellers
        Schema::table('sellers', function (Blueprint $table) {
            $table->index('points');
            $table->index('team_id');
            $table->index('season_id');
            $table->index('status');
        });

        // Índices para api_occurrences
        Schema::table('api_occurrences', function (Blueprint $table) {
            $table->index('processed');
            $table->index('ocorrencia');
            $table->index('email_funcionario');
            $table->index(['processed', 'created_at']);
        });

        // Índices para scores
        Schema::table('scores', function (Blueprint $table) {
            $table->index('seller_id');
            $table->index('score_rule_id');
            $table->index('created_at');
        });

        // Índices para score_rules
        Schema::table('score_rules', function (Blueprint $table) {
            $table->index('ocorrencia');
            $table->index('is_active');
            $table->index(['ocorrencia', 'is_active']);
        });

        // Índices para seasons
        Schema::table('seasons', function (Blueprint $table) {
            $table->index('is_active');
        });

        // configs já tem unique('key'), não precisa de índice adicional
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropIndex(['points']);
            $table->dropIndex(['team_id']);
            $table->dropIndex(['season_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('api_occurrences', function (Blueprint $table) {
            $table->dropIndex(['processed']);
            $table->dropIndex(['ocorrencia']);
            $table->dropIndex(['email_funcionario']);
            $table->dropIndex(['processed', 'created_at']);
        });

        Schema::table('scores', function (Blueprint $table) {
            $table->dropIndex(['seller_id']);
            $table->dropIndex(['score_rule_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('score_rules', function (Blueprint $table) {
            $table->dropIndex(['ocorrencia']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['ocorrencia', 'is_active']);
        });

        Schema::table('seasons', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });
    }
};
