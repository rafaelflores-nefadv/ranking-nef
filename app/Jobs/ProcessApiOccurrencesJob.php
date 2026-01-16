<?php

namespace App\Jobs;

use App\Models\ApiOccurrence;
use App\Models\Score;
use App\Models\ScoreRule;
use App\Models\Seller;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessApiOccurrencesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Número de ocorrências processadas por lote
     */
    private const BATCH_SIZE = 100;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Cache de score rules por ocorrencia para evitar queries repetidas
        $scoreRulesCache = [];

        // Processar em lotes para evitar sobrecarga de memória
        $offset = 0;
        
        do {
            $occurrences = ApiOccurrence::where('processed', false)
                ->orderBy('created_at')
                ->limit(self::BATCH_SIZE)
                ->offset($offset)
                ->get();

            if ($occurrences->isEmpty()) {
                break;
            }

            foreach ($occurrences as $occurrence) {
                try {
                    DB::beginTransaction();

                    // Buscar ou criar seller por email_funcionario
                    $seller = Seller::firstOrCreate(
                        ['email' => $occurrence->email_funcionario],
                        [
                            'name' => $occurrence->email_funcionario,
                            'points' => 0,
                            'status' => 'active',
                        ]
                    );

                    // Buscar score_rule usando cache
                    $cacheKey = $occurrence->ocorrencia;
                    
                    if (!isset($scoreRulesCache[$cacheKey])) {
                        $scoreRule = ScoreRule::where('ocorrencia', $occurrence->ocorrencia)
                            ->where('is_active', true)
                            ->orderBy('priority', 'desc')
                            ->first();
                        
                        $scoreRulesCache[$cacheKey] = $scoreRule;
                    } else {
                        $scoreRule = $scoreRulesCache[$cacheKey];
                    }

                    if ($scoreRule) {
                        // Criar registro em scores
                        Score::create([
                            'seller_id' => $seller->id,
                            'score_rule_id' => $scoreRule->id,
                            'points' => $scoreRule->points,
                            'created_at' => now(),
                        ]);

                        // Atualizar sellers.points
                        $seller->increment('points', $scoreRule->points);
                    }

                    // Marcar ocorrência como processada
                    $occurrence->update([
                        'processed' => true,
                        'error_message' => null,
                    ]);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();

                    // Salvar error_message em caso de erro
                    $occurrence->update([
                        'error_message' => $e->getMessage(),
                    ]);

                    Log::error('Erro ao processar ocorrência', [
                        'occurrence_id' => $occurrence->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $offset += self::BATCH_SIZE;
        } while ($occurrences->count() === self::BATCH_SIZE);
    }
}
