<?php

namespace App\Console\Commands;

use App\Models\ApiOccurrence;
use App\Models\ApiToken;
use App\Models\Seller;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixNullApiOccurrencesContextCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-occurrences:fix-null-context
                            {--dry-run : Apenas exibe o que seria alterado, sem gravar}
                            {--limit=0 : Limite total de registros a processar (0 = sem limite)}
                            {--chunk=500 : Tamanho do lote por iteração}
                            {--token-id= : UUID do api_token para preencher api_token_id (opcional)}
                            {--force-sector-from-token : Com --token-id, força sector_id pelo setor do token}
                            {--infer-token-if-unique : Se setor tiver exatamente 1 token ativo, preenche api_token_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige api_occurrences antigas com sector_id/api_token_id nulos (backfill seguro)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $tokenId = $this->option('token-id');
        $forceSectorFromToken = (bool) $this->option('force-sector-from-token');
        $inferTokenIfUnique = (bool) $this->option('infer-token-if-unique');

        $forcedToken = null;
        if ($tokenId) {
            $forcedToken = ApiToken::query()->find($tokenId);
            if (!$forcedToken) {
                $this->error("api_token_id inválido: {$tokenId}");
                return Command::FAILURE;
            }
        }

        $this->info('Iniciando correção de api_occurrences com campos nulos...');
        $this->line('Modo: ' . ($dryRun ? 'DRY-RUN (não grava)' : 'EXECUÇÃO (grava)'));
        $this->line('Chunk: ' . $chunkSize . ' | Limite: ' . ($limit > 0 ? $limit : 'sem limite'));
        if ($forcedToken) {
            $this->line('Token fixo para preencher api_token_id: ' . $forcedToken->id . ' (sector_id=' . $forcedToken->sector_id . ')');
            if ($forceSectorFromToken) {
                $this->line('Opção: forçar sector_id pelo setor do token');
            }
        } elseif ($inferTokenIfUnique) {
            $this->line('Opção: inferir api_token_id quando houver 1 token ativo no setor');
        }

        $stats = [
            'scanned' => 0,
            'updated' => 0,
            'fixed_sector' => 0,
            'fixed_identifier_type' => 0,
            'fixed_token' => 0,
            'forced_sector_from_token' => 0,
            'skipped_ambiguous_seller' => 0,
            'skipped_token_mismatch_sector' => 0,
            'skipped_token_not_unique' => 0,
            'still_null_sector' => 0,
            'still_null_token' => 0,
        ];

        while (true) {
            $updatedThisPass = 0;
            $query = ApiOccurrence::query()
                ->where(function ($q) {
                    $q->whereNull('sector_id')
                        ->orWhereNull('api_token_id')
                        ->orWhereNull('collaborator_identifier_type');
                })
                ->orderBy('created_at')
                ->limit($chunkSize);

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

            foreach ($occurrences as $occurrence) {
                $stats['scanned']++;

                $updates = [];

                // 1) Corrigir sector_id / collaborator_identifier_type quando possível
                if (empty($occurrence->sector_id)) {
                    // (a) Recuperar setor pelo token, se existir
                    if (!empty($occurrence->api_token_id)) {
                        $token = ApiToken::query()->find($occurrence->api_token_id);
                        if ($token?->sector_id) {
                            $updates['sector_id'] = $token->sector_id;
                            $stats['fixed_sector']++;

                            if (empty($occurrence->collaborator_identifier_type) && !empty($token->collaborator_identifier_type)) {
                                $updates['collaborator_identifier_type'] = $token->collaborator_identifier_type;
                                $stats['fixed_identifier_type']++;
                            }
                        }
                    }

                    // (b) Fallback: inferir setor pelo vendedor, apenas se único
                    if (!array_key_exists('sector_id', $updates)) {
                        $identifier = (string) $occurrence->email_funcionario;
                        if ($identifier !== '') {
                            $identifierType = $occurrence->collaborator_identifier_type ?: 'email';
                            $sellerCandidates = $this->findSellerCandidates($identifierType, $identifier);

                            if ($sellerCandidates->count() === 1) {
                                $updates['sector_id'] = $sellerCandidates->first()->sector_id;
                                $stats['fixed_sector']++;

                                if (empty($occurrence->collaborator_identifier_type)) {
                                    $updates['collaborator_identifier_type'] = $identifierType;
                                    $stats['fixed_identifier_type']++;
                                }
                            } elseif ($sellerCandidates->count() > 1) {
                                $stats['skipped_ambiguous_seller']++;
                            }
                        }
                    }

                    // (c) Se forneceu token e pediu para forçar setor, aplicar setor do token
                    if (!array_key_exists('sector_id', $updates) && $forcedToken && $forceSectorFromToken) {
                        $updates['sector_id'] = $forcedToken->sector_id;
                        $stats['fixed_sector']++;
                        $stats['forced_sector_from_token']++;

                        if (empty($occurrence->collaborator_identifier_type)) {
                            $updates['collaborator_identifier_type'] = $forcedToken->collaborator_identifier_type ?: 'email';
                            $stats['fixed_identifier_type']++;
                        }
                    }
                } elseif (empty($occurrence->collaborator_identifier_type)) {
                    // Se já tem setor mas não tem tipo, assumir email como padrão seguro
                    $updates['collaborator_identifier_type'] = 'email';
                    $stats['fixed_identifier_type']++;
                }

                // 2) Corrigir api_token_id, se solicitado e seguro
                if (empty($occurrence->api_token_id)) {
                    $sectorId = $updates['sector_id'] ?? $occurrence->sector_id;

                    if ($forcedToken) {
                        if (!empty($sectorId) && (string) $sectorId !== (string) $forcedToken->sector_id) {
                            $stats['skipped_token_mismatch_sector']++;
                        } else {
                            $updates['api_token_id'] = $forcedToken->id;
                            $stats['fixed_token']++;
                        }
                    } elseif ($inferTokenIfUnique && !empty($sectorId)) {
                        $activeTokens = ApiToken::query()
                            ->where('sector_id', $sectorId)
                            ->where('is_active', true)
                            ->limit(2)
                            ->get(['id']);

                        if ($activeTokens->count() === 1) {
                            $updates['api_token_id'] = $activeTokens->first()->id;
                            $stats['fixed_token']++;
                        } else {
                            $stats['skipped_token_not_unique']++;
                        }
                    }
                }

                if (!empty($updates)) {
                    $stats['updated']++;
                    $updatedThisPass++;

                    if (!$dryRun) {
                        DB::transaction(function () use ($occurrence, $updates) {
                            $occurrence->update($updates);
                        });
                    }
                }
            }

            if ($updatedThisPass === 0) {
                $this->warn('Nenhuma atualização possível no lote atual. Parando para evitar loop infinito.');
                break;
            }
        }

        // Recontar "ainda nulos" (apenas informativo)
        $stats['still_null_sector'] = ApiOccurrence::whereNull('sector_id')->count();
        $stats['still_null_token'] = ApiOccurrence::whereNull('api_token_id')->count();

        $this->newLine();
        $this->info('Resumo:');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Processados (scanned)', $stats['scanned']],
                ['Com updates aplicáveis (updated)', $stats['updated']],
                ['sector_id preenchido', $stats['fixed_sector']],
                ['collaborator_identifier_type preenchido', $stats['fixed_identifier_type']],
                ['api_token_id preenchido', $stats['fixed_token']],
                ['sector_id forçado pelo token', $stats['forced_sector_from_token']],
                ['Pulados por vendedor ambíguo', $stats['skipped_ambiguous_seller']],
                ['Pulados por token != setor', $stats['skipped_token_mismatch_sector']],
                ['Pulados por token não-único no setor', $stats['skipped_token_not_unique']],
                ['Ainda com sector_id NULL', $stats['still_null_sector']],
                ['Ainda com api_token_id NULL', $stats['still_null_token']],
            ]
        );

        $this->info('Concluído.');
        return Command::SUCCESS;
    }

    private function findSellerCandidates(string $identifierType, string $identifier)
    {
        $query = Seller::query()->whereNotNull('sector_id');

        if ($identifierType === 'external_code') {
            return $query->where('external_code', $identifier)
                ->limit(2)
                ->get(['id', 'sector_id']);
        }

        return $query->where('email', $identifier)
            ->limit(2)
            ->get(['id', 'sector_id']);
    }
}

