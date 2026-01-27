<?php

namespace App\Jobs;

use App\Models\ApiOccurrence;
use App\Models\Score;
use App\Models\ScoreRule;
use App\Models\Seller;
use App\Models\Team;
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

        // Processar em lotes para evitar sobrecarga de memória.
        // Evita offset pagination (pula/regenera registros quando o conjunto muda).
        while (true) {
            $occurrences = ApiOccurrence::where('processed', false)
                ->orderBy('created_at')
                ->limit(self::BATCH_SIZE)
                ->get();

            if ($occurrences->isEmpty()) {
                return;
            }

            foreach ($occurrences as $occurrence) {
                try {
                    DB::beginTransaction();

                    // Tentar "consertar" ocorrências antigas sem contexto de setor/token
                    $this->hydrateMissingContext($occurrence);

                    $seller = $occurrence->seller();
                    if (!$seller) {
                        throw new \RuntimeException('Vendedor não encontrado no setor.');
                    }

                    $team = null;
                    if (!empty($occurrence->equipe)) {
                        $team = Team::where('sector_id', $occurrence->sector_id)
                            ->where('name', $occurrence->equipe)
                            ->first();

                        if (!$team) {
                            throw new \RuntimeException('Equipe fora do setor.');
                        }

                        $belongsToTeam = $seller->teams()->where('teams.id', $team->id)->exists();
                        if (!$belongsToTeam) {
                            throw new \RuntimeException('Equipe fora do setor.');
                        }
                    }

                    // Buscar score_rule usando cache
                    $cacheKey = $occurrence->sector_id . ':' . $occurrence->ocorrencia;
                    
                    if (!isset($scoreRulesCache[$cacheKey])) {
                        $scoreRule = ScoreRule::where('sector_id', $occurrence->sector_id)
                            ->where('ocorrencia', $occurrence->ocorrencia)
                            ->where('is_active', true)
                            ->first();
                        
                        $scoreRulesCache[$cacheKey] = $scoreRule;
                    } else {
                        $scoreRule = $scoreRulesCache[$cacheKey];
                    }

                    if (!$scoreRule) {
                        throw new \RuntimeException('Regra inexistente no setor.');
                    }

                    // Criar registro em scores
                    Score::create([
                        'sector_id' => $occurrence->sector_id,
                        'seller_id' => $seller->id,
                        'score_rule_id' => $scoreRule->id,
                        'points' => $scoreRule->points,
                        'created_at' => now(),
                    ]);

                    // Atualizar sellers.points
                    $seller->increment('points', $scoreRule->points);

                    // Marcar ocorrência como processada
                    $occurrence->update([
                        'processed' => true,
                        'error_message' => null,
                    ]);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();

                    // Erros "determinísticos" (dados inválidos/ausentes) não se resolvem sozinhos;
                    // marcar como processada evita loop infinito no cron/queue.
                    $markAsProcessed = $e instanceof \RuntimeException;

                    $occurrence->update([
                        'error_message' => $e->getMessage(),
                        'processed' => $markAsProcessed ? true : $occurrence->processed,
                    ]);

                    Log::error('Erro ao processar ocorrência', [
                        'occurrence_id' => $occurrence->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    private function hydrateMissingContext(ApiOccurrence $occurrence): void
    {
        // Se já tem setor, não precisa fazer nada
        if (!empty($occurrence->sector_id)) {
            return;
        }

        // 1) Melhor hipótese: se veio com api_token_id, dá pra recuperar setor direto do token
        if (!empty($occurrence->api_token_id)) {
            $token = $occurrence->apiToken()->first();
            if ($token?->sector_id) {
                $occurrence->update([
                    'sector_id' => $token->sector_id,
                    'collaborator_identifier_type' => $occurrence->collaborator_identifier_type ?: $token->collaborator_identifier_type,
                ]);
                return;
            }
        }

        // 2) Fallback: inferir setor pelo vendedor (apenas se for único)
        $identifier = (string) $occurrence->email_funcionario;
        if ($identifier === '') {
            return;
        }

        if ($occurrence->collaborator_identifier_type === 'external_code') {
            $candidates = Seller::whereNotNull('sector_id')
                ->where('external_code', $identifier)
                ->limit(2)
                ->get(['id', 'sector_id']);
        } else {
            $candidates = Seller::whereNotNull('sector_id')
                ->where('email', $identifier)
                ->limit(2)
                ->get(['id', 'sector_id']);
        }

        if ($candidates->count() === 1) {
            $occurrence->update([
                'sector_id' => $candidates->first()->sector_id,
                'collaborator_identifier_type' => $occurrence->collaborator_identifier_type ?: 'email',
            ]);
        }
    }
}
