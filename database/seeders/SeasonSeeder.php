<?php

namespace Database\Seeders;

use App\Models\Config;
use App\Models\Season;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Desativar todas as temporadas existentes
        Season::query()->update(['is_active' => false]);

        // Buscar configurações de recorrência
        $configs = Config::all()->pluck('value', 'key');
        $recurrenceType = $configs['season_recurrence_type'] ?? 'annual';
        $fixedEndDate = $configs['season_fixed_end_date'] ?? null;
        $durationDays = isset($configs['season_duration_days']) ? (int) $configs['season_duration_days'] : null;

        // Calcular datas baseado no tipo de recorrência
        $dates = Season::calculateDatesByRecurrence(
            $recurrenceType,
            now()->startOfDay(),
            $fixedEndDate,
            $durationDays
        );

        // Criar temporada ativa
        Season::updateOrCreate(
            [
                'name' => 'Temporada Atual',
            ],
            [
                'starts_at' => $dates['starts_at'],
                'ends_at' => $dates['ends_at'],
                'recurrence_type' => $recurrenceType,
                'fixed_end_date' => $fixedEndDate ? \Carbon\Carbon::parse($fixedEndDate) : null,
                'duration_days' => $durationDays,
                'is_active' => true,
            ]
        );
    }
}
