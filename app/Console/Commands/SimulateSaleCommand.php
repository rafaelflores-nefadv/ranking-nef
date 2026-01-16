<?php

namespace App\Console\Commands;

use App\Models\ApiOccurrence;
use App\Models\Score;
use App\Models\ScoreRule;
use App\Models\Seller;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SimulateSaleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulate:sale 
                            {seller? : Email ou ID do vendedor (opcional se usar --random)}
                            {--random : Seleciona um vendedor aleatório}
                            {--occurrence=venda : Tipo de ocorrência (venda, bonus, estorno, etc.)}
                            {--points= : Pontos customizados (sobrescreve a regra padrão)}
                            {--random-points : Usa pontos aleatórios de 1 a 3}
                            {--credor= : Nome do credor (opcional)}
                            {--equipe= : Nome da equipe (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simula a entrada de pontos para um vendedor, criando o histórico e atualizando os pontos';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sellerIdentifier = $this->argument('seller');
        $isRandom = $this->option('random');
        $useRandomPoints = $this->option('random-points');
        $occurrenceType = $this->option('occurrence');
        $customPoints = $this->option('points');
        $credor = $this->option('credor');
        $equipe = $this->option('equipe');

        try {
            DB::beginTransaction();

            // Se modo aleatório, selecionar vendedor aleatório
            if ($isRandom) {
                $seller = $this->getRandomSeller();
                if (!$seller) {
                    $this->error('Nenhum vendedor encontrado no banco de dados!');
                    return 1;
                }
                $this->info("🎲 Vendedor selecionado aleatoriamente: {$seller->name} ({$seller->email})");
            } else {
                if (!$sellerIdentifier) {
                    $this->error('Você deve fornecer um vendedor ou usar --random');
                    return 1;
                }
                // Buscar ou criar seller
                $seller = $this->findOrCreateSeller($sellerIdentifier);
                
                if (!$seller) {
                    $this->error("Vendedor não encontrado: {$sellerIdentifier}");
                    return 1;
                }
            }

            $this->info("Vendedor: {$seller->name} ({$seller->email})");
            $this->info("Pontos atuais: " . number_format($seller->points, 2, ',', '.'));

            // Se usar pontos aleatórios, gerar de 1 a 3
            if ($useRandomPoints) {
                $randomPoints = rand(1, 3);
                $customPoints = (string) $randomPoints;
                $this->info("🎲 Pontos aleatórios gerados: {$randomPoints}");
            }

            // Buscar ou criar ScoreRule
            $scoreRule = $this->getScoreRule($occurrenceType, $customPoints);

            if (!$scoreRule) {
                $this->error("Regra de pontuação não encontrada para: {$occurrenceType}");
                $this->warn("Dica: Use --points para definir pontos customizados");
                return 1;
            }

            $this->info("Ocorrência: {$occurrenceType}");
            $this->info("Pontos a adicionar: " . number_format($scoreRule->points, 2, ',', '.'));

            // Criar ocorrência (seguindo o fluxo do sistema)
            $apiOccurrence = ApiOccurrence::create([
                'email_funcionario' => $seller->email,
                'ocorrencia' => $occurrenceType,
                'credor' => $credor,
                'equipe' => $equipe,
                'processed' => false,
            ]);

            // Criar registro em scores (histórico)
            $score = Score::create([
                'seller_id' => $seller->id,
                'score_rule_id' => $scoreRule->id,
                'points' => $scoreRule->points,
                'created_at' => now(),
            ]);

            // Atualizar pontos do seller
            $oldPoints = $seller->points;
            $seller->increment('points', $scoreRule->points);
            $seller->refresh();

            // Marcar ocorrência como processada
            $apiOccurrence->update([
                'processed' => true,
                'error_message' => null,
            ]);

            DB::commit();

            // Exibir resultado
            $this->newLine();
            $this->info('✓ Venda registrada com sucesso!');
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['Vendedor', $seller->name],
                    ['Email', $seller->email],
                    ['Ocorrência', $occurrenceType],
                    ['Pontos anteriores', number_format($oldPoints, 2, ',', '.')],
                    ['Pontos adicionados', number_format($scoreRule->points, 2, ',', '.')],
                    ['Pontos atuais', number_format($seller->points, 2, ',', '.')],
                    ['ID do Score', $score->id],
                    ['Data/Hora', now()->format('d/m/Y H:i:s')],
                ]
            );

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Erro ao processar venda: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Busca ou cria o seller pelo email ou ID
     */
    private function findOrCreateSeller(string $identifier): ?Seller
    {
        // Tentar buscar por ID (UUID)
        $seller = Seller::find($identifier);

        // Se não encontrou, tentar por email
        if (!$seller) {
            $seller = Seller::where('email', $identifier)->first();
        }

        // Se ainda não encontrou, criar novo seller
        if (!$seller) {
            if (!$this->confirm("Vendedor não encontrado. Deseja criar um novo vendedor com email '{$identifier}'?", true)) {
                return null;
            }

            $name = $this->ask('Digite o nome do vendedor', $identifier);
            
            $seller = Seller::create([
                'email' => $identifier,
                'name' => $name,
                'points' => 0,
                'status' => 'active',
            ]);

            $this->info("✓ Novo vendedor criado: {$seller->name}");
        }

        return $seller;
    }

    /**
     * Busca ou cria a ScoreRule para a ocorrência
     */
    private function getScoreRule(string $occurrenceType, ?string $customPoints): ?ScoreRule
    {
        // Se pontos customizados foram fornecidos, criar regra temporária
        if ($customPoints !== null) {
            $points = (float) $customPoints;
            
            return ScoreRule::firstOrCreate(
                [
                    'ocorrencia' => $occurrenceType,
                    'is_active' => true,
                ],
                [
                    'points' => $points,
                    'description' => "Regra customizada para {$occurrenceType}",
                    'priority' => 1,
                    'is_active' => true,
            ]);
        }

        // Buscar regra existente
        $scoreRule = ScoreRule::where('ocorrencia', $occurrenceType)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->first();

        // Se não encontrou, perguntar se deseja criar
        if (!$scoreRule) {
            if (!$this->confirm("Regra de pontuação não encontrada para '{$occurrenceType}'. Deseja criar uma nova?", true)) {
                return null;
            }

            $points = (float) $this->ask('Digite a quantidade de pontos', 100);
            $description = $this->ask('Digite a descrição', "Pontos por {$occurrenceType}");

            $scoreRule = ScoreRule::create([
                'ocorrencia' => $occurrenceType,
                'points' => $points,
                'description' => $description,
                'priority' => 1,
                'is_active' => true,
            ]);

            $this->info("✓ Nova regra criada: {$occurrenceType} = {$points} pontos");
        }

        return $scoreRule;
    }

    /**
     * Seleciona um vendedor aleatório do banco de dados
     */
    private function getRandomSeller(): ?Seller
    {
        return Seller::inRandomOrder()->first();
    }
}
