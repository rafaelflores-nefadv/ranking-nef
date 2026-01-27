<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Models\Season;
use Illuminate\Console\Command;

class CheckAndRenewSeasons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seasons:check-and-renew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica se a temporada ativa terminou e cria uma nova se a renovação automática estiver ativada';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $activeSeason = Season::where('is_active', true)->first();

        if (!$activeSeason) {
            $this->info('Nenhuma temporada ativa encontrada.');
            return 0;
        }

        $now = now()->startOfDay();
        $endDate = $activeSeason->ends_at;

        // Verificar se a temporada já terminou
        if ($now <= $endDate) {
            $this->info("Temporada ativa ainda não terminou. Termina em: {$endDate->format('d/m/Y')}");
            return 0;
        }

        $this->info("Temporada ativa terminou em: {$endDate->format('d/m/Y')}");

        // Verificar se renovação automática está ativada
        $autoRenew = Config::where('key', 'season_auto_renew')->value('value');
        $isAutoRenewEnabled = filter_var($autoRenew, FILTER_VALIDATE_BOOLEAN);

        if (!$isAutoRenewEnabled) {
            $this->warn('Renovação automática está desativada. Temporada não será renovada.');
            return 0;
        }

        // Buscar configurações de recorrência
        $configs = Config::all()->pluck('value', 'key');
        $recurrenceType = $configs['season_recurrence_type'] ?? 'annual';
        $fixedEndDate = $configs['season_fixed_end_date'] ?? null;
        $durationDays = isset($configs['season_duration_days']) ? (int) $configs['season_duration_days'] : null;

        $this->info("Tipo de recorrência: {$recurrenceType}");

        // Calcular datas da nova temporada
        // A nova temporada começa no dia seguinte ao término da anterior
        $newStartDate = $endDate->copy()->addDay()->startOfDay();
        
        $dates = Season::calculateDatesByRecurrence(
            $recurrenceType,
            $newStartDate,
            $fixedEndDate,
            $durationDays
        );

        // Desativar temporada antiga
        $activeSeason->update(['is_active' => false]);
        $this->info("✓ Temporada antiga desativada: {$activeSeason->name}");

        // Criar nova temporada
        $newSeason = Season::create([
            'name' => "Temporada {$dates['starts_at']->format('d/m/Y')} - {$dates['ends_at']->format('d/m/Y')}",
            'starts_at' => $dates['starts_at'],
            'ends_at' => $dates['ends_at'],
            'recurrence_type' => $recurrenceType,
            'fixed_end_date' => $fixedEndDate ? \Carbon\Carbon::parse($fixedEndDate) : null,
            'duration_days' => $durationDays,
            'is_active' => true,
        ]);

        $this->info("✓ Nova temporada criada: {$newSeason->name}");
        $this->info("  Período: {$dates['starts_at']->format('d/m/Y')} - {$dates['ends_at']->format('d/m/Y')}");

        // Zerar pontos e atualizar season_id de todos os vendedores ativos para a nova temporada
        // Os pontos anteriores ficam preservados no histórico (tabela scores)
        $sellersUpdated = \App\Models\Seller::where('status', 'active')
            ->update([
                'points' => 0,
                'season_id' => $newSeason->id,
            ]);

        $this->info("✓ Pontos zerados e temporada atualizada para {$sellersUpdated} vendedor(es) ativo(s)");
        $this->info("  Nota: Os pontos anteriores foram preservados no histórico (tabela scores)");

        return 0;
    }
}
