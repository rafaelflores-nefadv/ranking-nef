<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaultSectorId = (string) Str::uuid();

        DB::table('sectors')->insert([
            'id' => $defaultSectorId,
            'name' => 'Geral',
            'slug' => 'geral',
            'description' => 'Setor padrão criado pela migração.',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('sector_id')->nullable()->after('id');
            $table->index('sector_id');
            $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('restrict');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->uuid('sector_id')->nullable()->after('id');
            $table->index('sector_id');
            $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('restrict');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->uuid('sector_id')->nullable()->after('id');
            $table->string('external_code')->nullable()->after('email');
            $table->index('sector_id');
            $table->index(['sector_id', 'email']);
            $table->index(['sector_id', 'external_code']);
            $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('restrict');
        });

        Schema::table('score_rules', function (Blueprint $table) {
            $table->uuid('sector_id')->nullable()->after('id');
            $table->index('sector_id');
            $table->index(['sector_id', 'ocorrencia']);
            $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('restrict');
        });

        Schema::table('api_tokens', function (Blueprint $table) {
            $table->uuid('sector_id')->nullable()->after('integration_id');
            $table->string('collaborator_identifier_type')->default('email')->after('sector_id');
            $table->index('sector_id');
            $table->index(['sector_id', 'is_active']);
            $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('restrict');
        });

        Schema::table('api_occurrences', function (Blueprint $table) {
            $table->uuid('sector_id')->nullable()->after('id');
            $table->uuid('api_token_id')->nullable()->after('sector_id');
            $table->string('collaborator_identifier_type')->default('email')->after('api_token_id');
            $table->index('sector_id');
            $table->index('api_token_id');
            $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('restrict');
            $table->foreign('api_token_id')->references('id')->on('api_tokens')->onDelete('set null');
        });

        Schema::table('scores', function (Blueprint $table) {
            $table->uuid('sector_id')->nullable()->after('id');
            $table->index('sector_id');
            $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('restrict');
        });

        Schema::table('notification_histories', function (Blueprint $table) {
            $table->uuid('sector_id')->nullable()->after('id');
            $table->index('sector_id');
            $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('restrict');
        });

        Schema::table('monitors', function (Blueprint $table) {
            $table->uuid('sector_id')->nullable()->after('id');
            $table->index('sector_id');
            $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('restrict');
        });

        DB::table('users')
            ->where('role', '!=', 'admin')
            ->update(['sector_id' => $defaultSectorId]);

        DB::table('teams')->update(['sector_id' => $defaultSectorId]);
        DB::table('sellers')->update(['sector_id' => $defaultSectorId]);
        DB::table('score_rules')->update(['sector_id' => $defaultSectorId]);
        DB::table('api_tokens')->update([
            'sector_id' => $defaultSectorId,
            'collaborator_identifier_type' => 'email',
        ]);
        DB::table('api_occurrences')->update([
            'sector_id' => $defaultSectorId,
            'collaborator_identifier_type' => 'email',
        ]);
        DB::table('scores')->update(['sector_id' => $defaultSectorId]);
        DB::table('notification_histories')->update(['sector_id' => $defaultSectorId]);
        DB::table('monitors')->update(['sector_id' => $defaultSectorId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitors', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropIndex(['sector_id']);
            $table->dropColumn('sector_id');
        });

        Schema::table('notification_histories', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropIndex(['sector_id']);
            $table->dropColumn('sector_id');
        });

        Schema::table('scores', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropIndex(['sector_id']);
            $table->dropColumn('sector_id');
        });

        Schema::table('api_occurrences', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropForeign(['api_token_id']);
            $table->dropIndex(['sector_id']);
            $table->dropIndex(['api_token_id']);
            $table->dropColumn(['sector_id', 'api_token_id', 'collaborator_identifier_type']);
        });

        Schema::table('api_tokens', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropIndex(['sector_id']);
            $table->dropIndex(['sector_id', 'is_active']);
            $table->dropColumn(['sector_id', 'collaborator_identifier_type']);
        });

        Schema::table('score_rules', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropIndex(['sector_id']);
            $table->dropIndex(['sector_id', 'ocorrencia']);
            $table->dropColumn('sector_id');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropIndex(['sector_id']);
            $table->dropIndex(['sector_id', 'email']);
            $table->dropIndex(['sector_id', 'external_code']);
            $table->dropColumn(['sector_id', 'external_code']);
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropIndex(['sector_id']);
            $table->dropColumn('sector_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropIndex(['sector_id']);
            $table->dropColumn('sector_id');
        });

        DB::table('sectors')->where('slug', 'geral')->delete();
    }
};
