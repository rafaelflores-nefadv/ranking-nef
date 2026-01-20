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
        Schema::create('api_occurrences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email_funcionario');
            $table->string('ocorrencia');
            $table->string('credor')->nullable();
            $table->string('equipe')->nullable();
            $table->boolean('processed')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('processed');
            $table->index('ocorrencia');
            $table->index('email_funcionario');
            $table->index(['processed', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_occurrences');
    }
};
