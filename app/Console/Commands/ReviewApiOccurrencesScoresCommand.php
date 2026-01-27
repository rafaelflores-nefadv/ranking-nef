<?php

namespace App\Console\Commands;

use App\Models\ApiOccurrence;
use App\Models\Score;
use App\Models\ScoreRule;
use App\Models\Seller;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewApiOccurrencesScoresCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-occurrences:review-scores
                            {--dry-run : Apenas exibe o que seria alterado, sem gravar}
                            {--limit=0 : Limite total de registros a processar (0 = sem limite)}
                            {--chunk=200 : Tamanho do lote por iteração}
                            {--progress-every=200 : Exibir progresso a cada N registros}
                            {--only-errors : Processa apenas ocorrências com error_message}
                            {--include-processed : Inclui ocorrências já marcadas como processadas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revisa ocorrências e aplica pontos faltantes sem duplicidade';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $onlyErrors = (bool) $this->option('only-errors');
        $includeProcessed = (bool) $this->option('include-processed');
        $progressEvery = max(1, (int) $this->option('progress-every'));

        $this->info('Iniciando revisão de api_occurrences...');
        $this->line('Modo: ' . ($dryRun ? 'DRY-RUN (não grava)' : 'EXECUÇÃO (grava)'));
        $this->line('Chunk: ' . $chunkSize . ' | Limite: ' . ($limit > 0 ? $limit : 'sem limite'));
        if ($onlyErrors) {
            $this->line('Filtro: apenas ocorrências com error_message');
        } elseif ($includeProcessed) {
            $this->line('Filtro: incluindo ocorrências já processadas');
        } else {
            $this->line('Filtro: ocorrências não processadas ou com erro');
        }

        $stats = [
            'scanned' => 0,
            'applied' => 0,
            'skipped_already_scored' => 0,
            'linked_existing_score' => 0,
            'fixed_context' => 0,
            'failed_missing_seller' => 0,
            'failed_missing_rule' => 0,
            'failed_team_mismatch' => 0,
        ];
        $batchIndex = 0;

        while (true) {
            $query = ApiOccurrence::query();

            if ($onlyErrors) {
                $query->whereNotNull('error_message');
            } elseif (!$includeProcessed) {
                $query->where(function ($q) {
                    $q->where('processed', false)
                        ->orWhereNotNull('error_message');
                });
            }

            $query->orderBy('created_at')->limit($chunkSize);

            if ($limit > 0) {
                $remaining = $limit - $stats['scanned'];
                if ($remaining <= 0) {
                    break;
                }
                $query->limit(min($chunkSize, $remaining));
            }

            $occurrences = $query->get();
            if ($occurrences->isEmpty()) {
                break;
            }
            $batchIndex++;
            $this->line('Lote #' . $batchIndex . ' - registros no lote: ' . $occurrences->count());

            $updatedThisPass = 0;

            foreach ($occurrences as $occurrence) {
                $stats['scanned']++;
                if ($stats['scanned'] % $progressEvery === 0) {
                    $this->line(
                        'Progresso: scanned=' . $stats['scanned']
                        . ' | applied=' . $stats['applied']
                        . ' | linked=' . $stats['linked_existing_score']
                        . ' | skipped=' . $stats['skipped_already_scored']
                    );
                }

                try {
                    DB::beginTransaction();

                    $fixedContext = $this->hydrateMissingContext($occurrence);
                    if ($fixedContext) {
                        $stats['fixed_context']++;
                    }

                    // Evitar duplicidade: se já existe score para esta ocorrência, apenas marcar como processada
                    if (Score::where('api_occurrence_id', $occurrence->id)->exists()) {
                        $stats['skipped_already_scored']++;
                        if (!$dryRun) {
                            $occurrence->update([
                                'processed' => true,
                                'error_message' => null,
                            ]);
                        }
                        DB::commit();
                        $updatedThisPass++;
                        continue;
                    }

                    $seller = $occurrence->seller();
                    if (!$seller) {
                        $stats['failed_missing_seller']++;
                        $this->markFailure($occurrence, 'Vendedor não encontrado no setor.', $dryRun);
                        DB::commit();
                        $updatedThisPass++;
                        continue;
                    }

                    $team = null;
                    if (!empty($occurrence->equipe)) {
                        $team = Team::where('sector_id', $occurrence->sector_id)
                            ->where('name', $occurrence->equipe)
                            ->first();

                        if (!$team) {
                            $stats['failed_team_mismatch']++;
                            $this->markFailure($occurrence, 'Equipe fora do setor.', $dryRun);
                            DB::commit();
                            $updatedThisPass++;
                            continue;
                        }

                        $belongsToTeam = $seller->teams()->where('teams.id', $team->id)->exists();
                        if (!$belongsToTeam) {
                            $stats['failed_team_mismatch']++;
                            $this->markFailure($occurrence, 'Equipe fora do setor.', $dryRun);
                            DB::commit();
                            $updatedThisPass++;
                            continue;
                        }
                    }

                    $scoreRule = ScoreRule::where('sector_id', $occurrence->sector_id)
                        ->where('ocorrencia', $occurrence->ocorrencia)
                        ->where('is_active', true)
                        ->first();

                    if (!$scoreRule) {
                        $stats['failed_missing_rule']++;
                        $this->markFailure($occurrence, 'Regra inexistente no setor.', $dryRun);
                        DB::commit();
                        $updatedThisPass++;
                        continue;
                    }

                    // Tentar vincular score existente (sem api_occurrence_id) para evitar duplicidade
                    $linked = $this->linkExistingScore($occurrence, $seller->id, $scoreRule->id, $dryRun);
                    if ($linked) {
                        $stats['linked_existing_score']++;
                        if (!$dryRun) {
                            $occurrence->update([
                                'processed' => true,
                                'error_message' => null,
                            ]);
                        }
                        DB::commit();
                        $updatedThisPass++;
                        continue;
                    }

                    if (!$dryRun) {
                        Score::create([
                            'api_occurrence_id' => $occurrence->id,
                            'sector_id' => $occurrence->sector_id,
                            'seller_id' => $seller->id,
                            'score_rule_id' => $scoreRule->id,
                            'points' => $scoreRule->points,
                            'created_at' => now(),
                        ]);

                        $seller->increment('points', $scoreRule->points);

                        $occurrence->update([
                            'processed' => true,
                            'error_message' => null,
                        ]);
                    }

                    $stats['applied']++;
                    DB::commit();
                    $updatedThisPass++;
                } catch (\Exception $e) {
                    try {
                        DB::rollBack();
                    } catch (\Exception $rollbackException) {
                        // Conexão pode ter sido perdida; tenta reconectar para continuar
                        DB::disconnect();
                        DB::reconnect();
                    }
                    Log::error('Erro ao revisar ocorrência', [
                        'occurrence_id' => $occurrence->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($updatedThisPass === 0) {
                $this->warn('Nenhuma atualização possível no lote atual. Parando para evitar loop infinito.');
                break;
            }
        }

        $this->newLine();
        $this->info('Resumo:');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Processados (scanned)', $stats['scanned']],
                ['Pontos aplicados', $stats['applied']],
                ['Pulados (já tinham score)', $stats['skipped_already_scored']],
                ['Vínculo de score existente', $stats['linked_existing_score']],
                ['Contexto corrigido', $stats['fixed_context']],
                ['Falha: vendedor inexistente', $stats['failed_missing_seller']],
                ['Falha: regra inexistente', $stats['failed_missing_rule']],
                ['Falha: equipe fora do setor', $stats['failed_team_mismatch']],
            ]
        );

        $this->info('Concluído.');
        return Command::SUCCESS;
    }

    private function hydrateMissingContext(ApiOccurrence $occurrence): bool
    {
        if (!empty($occurrence->sector_id)) {
            return false;
        }

        if (!empty($occurrence->api_token_id)) {
            $token = $occurrence->apiToken()->first();
            if ($token?->sector_id) {
                $occurrence->update([
                    'sector_id' => $token->sector_id,
                    'collaborator_identifier_type' => $occurrence->collaborator_identifier_type ?: $token->collaborator_identifier_type,
                ]);
                return true;
            }
        }

        $identifier = (string) $occurrence->email_funcionario;
        if ($identifier === '') {
            return false;
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
            return true;
        }

        return false;
    }

    private function markFailure(ApiOccurrence $occurrence, string $message, bool $dryRun): void
    {
        if ($dryRun) {
            return;
        }

        $occurrence->update([
            'error_message' => $message,
            'processed' => true,
        ]);
    }

    private function linkExistingScore(ApiOccurrence $occurrence, string $sellerId, string $scoreRuleId, bool $dryRun): bool
    {
        if (!$occurrence->created_at) {
            return false;
        }

        $start = $occurrence->created_at->copy()->subMinutes(2);
        $end = $occurrence->created_at->copy()->addMinutes(2);

        $matches = Score::query()
            ->whereNull('api_occurrence_id')
            ->where('seller_id', $sellerId)
            ->where('score_rule_id', $scoreRuleId)
            ->whereBetween('created_at', [$start, $end])
            ->limit(2)
            ->get(['id']);

        if ($matches->count() !== 1) {
            return false;
        }

        if (!$dryRun) {
            Score::where('id', $matches->first()->id)
                ->update(['api_occurrence_id' => $occurrence->id]);
        }

        return true;
    }
}

