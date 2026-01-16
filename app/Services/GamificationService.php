<?php

namespace App\Services;

class GamificationService
{
    /**
     * Tabela de níveis e badges
     */
    private const LEVELS = [
        ['min' => 0, 'max' => 999, 'level' => 1, 'badge' => 'Iniciante'],
        ['min' => 1000, 'max' => 4999, 'level' => 2, 'badge' => 'Intermediário'],
        ['min' => 5000, 'max' => 9999, 'level' => 3, 'badge' => 'Avançado'],
        ['min' => 10000, 'max' => 19999, 'level' => 4, 'badge' => 'Pro'],
        ['min' => 20000, 'max' => PHP_INT_MAX, 'level' => 5, 'badge' => 'Elite'],
    ];

    /**
     * Retorna informações de gamificação baseado nos pontos
     *
     * @param float $points
     * @return array{level: int, badge: string, progress: float}
     */
    public function getGamificationInfo(float $points): array
    {
        $levelInfo = $this->getLevelByPoints($points);
        
        $progress = $this->calculateProgress($points, $levelInfo);

        return [
            'level' => $levelInfo['level'],
            'badge' => $levelInfo['badge'],
            'progress' => $progress,
        ];
    }

    /**
     * Obtém informações do nível baseado nos pontos
     *
     * @param float $points
     * @return array{min: int, max: int, level: int, badge: string}
     */
    private function getLevelByPoints(float $points): array
    {
        foreach (self::LEVELS as $level) {
            if ($points >= $level['min'] && $points <= $level['max']) {
                return $level;
            }
        }

        // Fallback para o último nível
        return end(self::LEVELS);
    }

    /**
     * Calcula o progresso dentro do nível atual (0-100)
     *
     * @param float $points
     * @param array $levelInfo
     * @return float
     */
    private function calculateProgress(float $points, array $levelInfo): float
    {
        $range = $levelInfo['max'] - $levelInfo['min'];
        
        if ($range <= 0) {
            return 100.0; // Nível máximo
        }

        $progressInRange = $points - $levelInfo['min'];
        $progress = ($progressInRange / $range) * 100;

        return round(min(100, max(0, $progress)), 2);
    }
}
