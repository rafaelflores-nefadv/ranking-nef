<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\RankingVoiceService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Agendar processamento automático de ocorrências da API
Schedule::command('process:api-occurrences')
    ->name('process-api-occurrences')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();

// Agendar leitura por voz do ranking
Schedule::call(function () {
    app(RankingVoiceService::class)->dispatchIfDue();
})
    ->name('ranking-voice')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();

// Agendar verificação e renovação automática de temporadas
Schedule::command('seasons:check-and-renew')
    ->name('check-and-renew-seasons')
    ->daily()
    ->at('00:00')
    ->withoutOverlapping()
    ->onOneServer();

/*
 * Configuração do Cron (para produção):
 * 
 * Adicione esta linha ao crontab do servidor:
 * 
 * * * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
 * 
 * Exemplo para Windows (Task Scheduler):
 * - Programa: php
 * - Argumentos: C:\Projetos\ranking-nef\artisan schedule:run
 * - Executar a cada minuto
 */
