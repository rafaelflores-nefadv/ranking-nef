<?php

namespace App\Console\Commands;

use App\Jobs\ProcessApiOccurrencesJob;
use App\Models\Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessApiOccurrencesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:api-occurrences';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa ocorrências da API pendentes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            // Ler configuração auto_process_occurrences
            $autoProcess = Config::where('key', 'auto_process_occurrences')
                ->value('value');

            // Converter string para boolean
            $isEnabled = filter_var($autoProcess, FILTER_VALIDATE_BOOLEAN);

            if (!$isEnabled) {
                Log::info('Processamento automático de ocorrências desativado pela configuração');
                $this->info('Processamento automático desativado. Verifique a configuração auto_process_occurrences.');
                return Command::SUCCESS;
            }

            // Disparar o Job
            Log::info('Iniciando processamento de ocorrências da API');
            $this->info('Disparando ProcessApiOccurrencesJob...');

            ProcessApiOccurrencesJob::dispatch();

            Log::info('ProcessApiOccurrencesJob disparado com sucesso');
            $this->info('Job disparado com sucesso!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            Log::error('Erro ao processar ocorrências da API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Erro ao processar ocorrências: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
