<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset 
                            {--force : Força a execução sem confirmação}
                            {--keep-configs : Mantém as configurações do sistema}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reseta o banco de dados mantendo apenas os usuários de acesso';

    /**
     * Tabelas que devem ser mantidas (não serão limpas)
     */
    protected array $protectedTables = [
        'users',
        'password_reset_tokens',
        'sessions',
        'migrations',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  ATENÇÃO: Esta ação irá remover TODOS os dados exceto usuários. Deseja continuar?')) {
                $this->info('Operação cancelada.');
                return 0;
            }
        }

        $this->info('🔄 Iniciando reset do banco de dados...');
        $this->newLine();

        try {
            DB::beginTransaction();

            // Desabilitar verificação de chaves estrangeiras temporariamente
            Schema::disableForeignKeyConstraints();

            // Lista de tabelas para limpar
            $tablesToClean = $this->getTablesToClean();

            $this->info('📋 Tabelas que serão limpas:');
            foreach ($tablesToClean as $table) {
                $this->line("   - {$table}");
            }
            $this->newLine();

            // Limpar cada tabela
            $bar = $this->output->createProgressBar(count($tablesToClean));
            $bar->start();

            foreach ($tablesToClean as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            // Reabilitar verificação de chaves estrangeiras
            Schema::enableForeignKeyConstraints();

            DB::commit();

            $this->info('✅ Banco de dados resetado com sucesso!');
            $this->info('👥 Usuários mantidos: ' . DB::table('users')->count());

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            Schema::enableForeignKeyConstraints();
            
            $this->error('❌ Erro ao resetar banco de dados: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Retorna a lista de tabelas que devem ser limpas
     */
    protected function getTablesToClean(): array
    {
        $tables = [
            'sellers',
            'teams',
            'seasons',
            'scores',
            'score_rules',
            'api_occurrences',
            'notification_histories',
            'team_user',
            'goals',
            'monitors',
            'api_integrations',
            'api_tokens',
        ];

        // Se não for para manter configs, adiciona à lista
        if (!$this->option('keep-configs')) {
            $tables[] = 'configs';
        }

        return $tables;
    }
}
