<?php

namespace Database\Seeders;

use App\Models\Seller;
use App\Models\Team;
use App\Models\Season;
use App\Models\Sector;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SellerSeeder extends Seeder
{
    protected ?int $quantity = null;

    /**
     * Define a quantidade de vendedores a criar
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
        // Obter quantidade de vendedores (padrão: 30)
        $quantity = $this->quantity ?? (int) env('SEED_SELLERS_QUANTITY', 30);
        
        if ($quantity < 1) {
            if ($this->command) {
                $this->command->warn('Quantidade de vendedores deve ser maior que 0. Usando padrão: 30');
            }
            $quantity = 30;
        }

        $defaultSectorId = Sector::where('slug', 'geral')->value('id');
        // Buscar todas as equipes criadas
        $teams = Team::where('sector_id', $defaultSectorId)->get();
        
        if ($teams->isEmpty()) {
            if ($this->command) {
                $this->command->warn('Nenhuma equipe encontrada. Execute o TeamSeeder primeiro.');
            }
            return;
        }

        // Buscar temporada ativa (opcional)
        $activeSeason = Season::where('is_active', true)->first();

        // Lista de nomes de vendedores (pool de nomes)
        $firstNames = [
            'João', 'Maria', 'Pedro', 'Ana', 'Carlos', 'Juliana', 'Ricardo', 'Fernanda',
            'Bruno', 'Patricia', 'Lucas', 'Camila', 'Gabriel', 'Isabela', 'Rafael', 'Larissa',
            'Thiago', 'Mariana', 'Felipe', 'Beatriz', 'André', 'Renata', 'Gustavo', 'Vanessa',
            'Diego', 'Tatiana', 'Rodrigo', 'Amanda', 'Leandro', 'Priscila', 'Roberto', 'Cristina',
            'Marcos', 'Adriana', 'Paulo', 'Sandra', 'Fernando', 'Monica', 'Eduardo', 'Lucia',
            'Rafaela', 'Daniel', 'Carla', 'Marcelo', 'Patricia', 'Fabio', 'Juliana', 'Renato',
            'Simone', 'Alexandre', 'Debora', 'Vinicius', 'Leticia', 'Henrique', 'Priscila',
        ];

        $lastNames = [
            'Silva', 'Santos', 'Oliveira', 'Costa', 'Pereira', 'Ferreira', 'Almeida', 'Lima',
            'Souza', 'Rocha', 'Martins', 'Rodrigues', 'Barbosa', 'Nunes', 'Carvalho', 'Gomes',
            'Ribeiro', 'Dias', 'Araújo', 'Monteiro', 'Cunha', 'Freitas', 'Lopes', 'Moreira',
            'Ramos', 'Correia', 'Teixeira', 'Mendes', 'Azevedo', 'Cardoso', 'Castro', 'Melo',
            'Reis', 'Machado', 'Pinto', 'Cavalcanti', 'Barros', 'Campos', 'Nascimento', 'Moraes',
        ];

        if ($this->command) {
            $this->command->info("Criando {$quantity} vendedores...");
        }

        $created = 0;
        for ($i = 0; $i < $quantity; $i++) {
            // Gerar nome aleatório
            $firstName = fake()->randomElement($firstNames);
            $lastName = fake()->randomElement($lastNames);
            $name = "{$firstName} {$lastName}";
            // Distribuir vendedores entre as equipes de forma balanceada
            $team = $teams->get($i % $teams->count());
            
            // Gerar email único baseado no nome
            $nameSlug = Str::slug($name);
            $email = strtolower($nameSlug) . '@rankingnef.com';
            
            // Garantir que o email seja único
            $counter = 1;
            while (Seller::where('email', $email)->exists()) {
                $email = strtolower($nameSlug) . $counter . '@rankingnef.com';
                $counter++;
            }

            // Pontuação inicial sempre zero; será atribuída em outro comando
            $points = 0;
            
            // Status aleatório (80% ativos, 20% inativos)
            $status = fake()->randomElement(['active', 'active', 'active', 'active', 'inactive']);

            $seller = Seller::updateOrCreate(
                ['email' => $email],
                [
                    'sector_id' => $defaultSectorId,
                    'season_id' => $activeSeason?->id,
                    'name' => $name,
                    'email' => $email,
                    'points' => $points,
                    'status' => $status,
                ]
            );
            $seller->teams()->sync([$team->id]);
            
            $created++;
        }

        if ($this->command) {
            $this->command->info("✓ {$created} vendedores criados com sucesso!");
            $this->command->info('Total de vendedores no banco: ' . Seller::count());
            
            // Mostrar distribuição por equipe
            foreach ($teams as $team) {
                $sellersCount = Seller::where('team_id', $team->id)->count();
                $this->command->info("  - {$team->name}: {$sellersCount} vendedores");
            }
        }
    }
}
