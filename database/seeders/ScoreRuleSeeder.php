<?php

namespace Database\Seeders;

use App\Models\ScoreRule;
use App\Models\Sector;
use Illuminate\Database\Seeder;

class ScoreRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSectorId = Sector::where('slug', 'geral')->value('id');

        // Dados fornecidos no formato: código - descrição|pontos
        $rawData = [
            '20.1 - SEM PROPOSTA/SEM PROMESSA|1',
            '20.2 - NÃO QUER PAGAR|1',
            '20.3 - DESCONHECE DÍVIDA|1',
            '30.1 - C/ PROPOSTA|2',
            '30.2 - C/PROMESSA|2',
            '30.3 - SOLICITOU RETORNO|1',
            '30.4 - AGÊNCIA|1',
            '30.5 - CPC WHATS|1',
            '30.6 - BOLETO ENVIADO|3',
            '30.7 - ALEGOU PAGAMENTO|1',
            '30.8 -  BOLETO PAGO|4',
            '10 - NÃO ATENDE|1',
            '10.2 - NÃO ATENDE - WHATS |1',
            '98 - QUEBRA DE ACORDO|1',
        ];

        $rules = [];

        foreach ($rawData as $line) {
            // Remover espaços extras
            $line = trim($line);
            
            // Dividir por "|" para separar ocorrencia completa e pontos
            $parts = explode('|', $line, 2);
            
            if (count($parts) !== 2) {
                continue;
            }
            
            // Tudo antes do "|" é a ocorrencia completa (código - descrição)
            $ocorrencia = trim($parts[0]);
            
            // O que vem depois do "|" são os pontos
            $points = (float) trim($parts[1]);
            
            $rules[] = [
                'ocorrencia' => $ocorrencia,
                'points' => $points,
                'is_active' => true,
            ];
        }

        foreach ($rules as $ruleData) {
            ScoreRule::updateOrCreate(
                ['ocorrencia' => $ruleData['ocorrencia'], 'sector_id' => $defaultSectorId],
                array_merge($ruleData, ['sector_id' => $defaultSectorId])
            );
        }
    }
}
