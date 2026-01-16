<?php

namespace Database\Seeders;

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

        // Criar temporada ativa para o ano atual
        $currentYear = now()->year;
        $startsAt = now()->startOfYear();
        $endsAt = now()->endOfYear();

        Season::updateOrCreate(
            [
                'name' => 'Temporada Atual',
            ],
            [
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'is_active' => true,
            ]
        );
    }
}
