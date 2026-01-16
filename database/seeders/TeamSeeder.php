<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    protected ?int $quantity = null;

    /**
     * Define a quantidade de equipes a criar
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obter quantidade de equipes (padrão: 6)
        $quantity = $this->quantity ?? (int) env('SEED_TEAMS_QUANTITY', 6);
        
        if ($quantity < 1) {
            if ($this->command) {
                $this->command->warn('Quantidade de equipes deve ser maior que 0. Usando padrão: 6');
            }
            $quantity = 6;
        }

        if ($this->command) {
            $this->command->info("Criando {$quantity} equipes...");
        }

        // Nomes base para equipes
        $teamNames = [
            'Alpha', 'Beta', 'Gamma', 'Delta', 'Omega', 'Sigma',
            'Phoenix', 'Titan', 'Apex', 'Nexus', 'Vortex', 'Quantum',
            'Stellar', 'Nova', 'Eclipse', 'Zenith', 'Aurora', 'Polaris',
            'Orion', 'Atlas', 'Mercury', 'Venus', 'Mars', 'Jupiter',
        ];

        for ($i = 0; $i < $quantity; $i++) {
            $teamName = 'Equipe ' . ($teamNames[$i] ?? "Equipe " . ($i + 1));
            
            Team::updateOrCreate(
                ['name' => $teamName],
                ['name' => $teamName]
            );
        }

        if ($this->command) {
            $this->command->info("✓ {$quantity} equipes criadas com sucesso!");
        }
    }
}
