<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_photo_path')->nullable()->after('email');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->string('profile_photo_path')->nullable()->after('email');
        });

        DB::table('users')
            ->whereNull('profile_photo_path')
            ->whereNotNull('avatar')
            ->update(['profile_photo_path' => DB::raw('avatar')]);

        DB::table('sellers')
            ->whereNull('profile_photo_path')
            ->whereNotNull('avatar')
            ->update(['profile_photo_path' => DB::raw('avatar')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_photo_path');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn('profile_photo_path');
        });
    }
};
