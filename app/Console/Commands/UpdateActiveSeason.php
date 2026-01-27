<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Models\Season;
use Illuminate\Console\Command;

class UpdateActiveSeason extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'season:update-active';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza a temporada ativa baseado nas configurações de recorrência';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $activeSeason = Season::where('is_active', true)->first();

        if (!$activeSeason) {
            $this->error('Nenhuma temporada ativa encontrada.');
            return 1;
        }

        // Buscar configurações de recorrência
        $configs = Config::all()->pluck('value', 'key');
        $recurrenceType = $configs['season_recurrence_type'] ?? 'annual';
        $fixedEndDate = $configs['season_fixed_end_date'] ?? null;
        $durationDays = isset($configs['season_duration_days']) ? (int) $configs['season_duration_days'] : null;

        $this->info("Temporada atual: {$activeSeason->name}");
        $this->info("Período atual: {$activeSeason->starts_at->format('d/m/Y')} - {$activeSeason->ends_at->format('d/m/Y')}");
        $this->info("Tipo de recorrência configurado: {$recurrenceType}");

        // Calcular novas datas
        $dates = Season::calculateDatesByRecurrence(
            $recurrenceType,
            $activeSeason->starts_at,
            $fixedEndDate,
            $durationDays
        );

        $this->info("Novo período: {$dates['starts_at']->format('d/m/Y')} - {$dates['ends_at']->format('d/m/Y')}");

        if (!$this->confirm('Deseja atualizar a temporada com essas datas?', true)) {
            $this->info('Operação cancelada.');
            return 0;
        }

        $activeSeason->update([
            'starts_at' => $dates['starts_at'],
            'ends_at' => $dates['ends_at'],
            'recurrence_type' => $recurrenceType,
            'fixed_end_date' => $fixedEndDate ? \Carbon\Carbon::parse($fixedEndDate) : null,
            'duration_days' => $durationDays,
        ]);

        $this->info('✓ Temporada atualizada com sucesso!');
        return 0;
    }
}
