<?php

namespace Database\Seeders;

use App\Models\ScoreRule;
use Illuminate\Database\Seeder;

class ScoreRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            [
                'ocorrencia' => 'venda',
                'points' => 100,
                'description' => 'Pontos por venda',
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'ocorrencia' => 'bonus',
                'points' => 50,
                'description' => 'Pontos de bônus',
                'priority' => 2,
                'is_active' => true,
            ],
            [
                'ocorrencia' => 'estorno',
                'points' => -100,
                'description' => 'Estorno de venda',
                'priority' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($rules as $ruleData) {
            ScoreRule::updateOrCreate(
                ['ocorrencia' => $ruleData['ocorrencia']],
                $ruleData
            );
        }
    }
}
