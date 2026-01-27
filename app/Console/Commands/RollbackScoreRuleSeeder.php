<?php

namespace App\Console\Commands;

use App\Models\ScoreRule;
use Illuminate\Console\Command;

class RollbackScoreRuleSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'score-rules:rollback-seeder 
                            {--force : Força a remoção sem confirmação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove todas as regras de pontuação criadas pela ScoreRuleSeeder';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Ocorrências completas que foram criadas pela seeder (formato: código - descrição)
        $seederOcorrencias = [
            '20.1 - SEM PROPOSTA/SEM PROMESSA',
            '20.2 - NÃO QUER PAGAR',
            '20.3 - DESCONHECE DÍVIDA',
            '30.1 - C/ PROPOSTA',
            '30.2 - C/PROMESSA',
            '30.3 - SOLICITOU RETORNO',
            '30.4 - AGÊNCIA',
            '30.5 - CPC WHATS',
            '30.6 - BOLETO ENVIADO',
            '30.7 - ALEGOU PAGAMENTO',
            '30.8 -  BOLETO PAGO',
            '10 - NÃO ATENDE',
            '10.2 - NÃO ATENDE - WHATS ',
            '98 - QUEBRA DE ACORDO',
        ];

        // Buscar regras que correspondem aos códigos da seeder
        $rulesToDelete = ScoreRule::whereIn('ocorrencia', $seederOcorrencias)->get();

        if ($rulesToDelete->isEmpty()) {
            $this->info('Nenhuma regra da seeder encontrada para remover.');
            return 0;
        }

        // Exibir regras que serão removidas
        $this->info('Regras que serão removidas:');
        $this->table(
            ['Ocorrência', 'Pontos', 'Ativo'],
            $rulesToDelete->map(function ($rule) {
                return [
                    $rule->ocorrencia,
                    number_format($rule->points, 2, ',', '.'),
                    $rule->is_active ? 'Sim' : 'Não',
                ];
            })->toArray()
        );

        // Confirmar remoção
        if (!$this->option('force')) {
            if (!$this->confirm('Tem certeza que deseja remover essas regras?', false)) {
                $this->info('Operação cancelada.');
                return 0;
            }
        }

        // Remover regras
        $count = $rulesToDelete->count();
        $deletedOcorrencias = $rulesToDelete->pluck('ocorrencia')->toArray();

        ScoreRule::whereIn('ocorrencia', $seederOcorrencias)->delete();

        $this->info("✓ {$count} regra(s) removida(s) com sucesso!");
        $this->line('Ocorrências removidas: ' . implode(', ', $deletedOcorrencias));

        return 0;
    }
}
