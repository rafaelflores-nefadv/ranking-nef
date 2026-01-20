<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\Seller;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class GoalService
{
    /**
     * Calcula o progresso de uma meta baseado nos pontos reais
     *
     * @param Goal $goal
     * @return array{current_value: float, progress: float, is_reached: bool, status: string}
     */
    public function calculateProgress(Goal $goal): array
    {
        $currentValue = $this->getCurrentValue($goal);
        $progress = $goal->getProgress($currentValue);
        $isReached = $goal->isReached($currentValue);
        
        $status = $this->getStatus($goal, $isReached);
        
        return [
            'current_value' => $currentValue,
            'progress' => $progress,
            'is_reached' => $isReached,
            'status' => $status,
        ];
    }

    /**
     * Obtém o valor atual baseado no escopo da meta
     *
     * @param Goal $goal
     * @return float
     */
    private function getCurrentValue(Goal $goal): float
    {
        return match ($goal->scope) {
            'global' => $this->getGlobalValue($goal),
            'team' => $this->getTeamValue($goal),
            'seller' => $this->getSellerValue($goal),
            default => 0.0,
        };
    }

    /**
     * Calcula o valor total global (soma de todos os pontos da temporada)
     */
    private function getGlobalValue(Goal $goal): float
    {
        return Seller::where('season_id', $goal->season_id)
            ->sum('points') ?? 0.0;
    }

    /**
     * Calcula o valor total da equipe (soma dos pontos dos vendedores da equipe)
     */
    private function getTeamValue(Goal $goal): float
    {
        if (!$goal->team_id) {
            return 0.0;
        }

        return Seller::where('season_id', $goal->season_id)
            ->where('team_id', $goal->team_id)
            ->sum('points') ?? 0.0;
    }

    /**
     * Obtém o valor atual do vendedor
     */
    private function getSellerValue(Goal $goal): float
    {
        if (!$goal->seller_id) {
            return 0.0;
        }

        $seller = Seller::find($goal->seller_id);
        return $seller?->points ?? 0.0;
    }

    /**
     * Retorna o status da meta
     */
    private function getStatus(Goal $goal, bool $isReached): string
    {
        if (!$goal->isActive()) {
            return $isReached ? 'reached' : 'not_reached';
        }

        return $isReached ? 'reached' : 'in_progress';
    }

    /**
     * Calcula o progresso para múltiplas metas
     *
     * @param \Illuminate\Database\Eloquent\Collection $goals
     * @return \Illuminate\Support\Collection
     */
    public function calculateProgressForMany($goals)
    {
        return $goals->map(function (Goal $goal) {
            $progress = $this->calculateProgress($goal);
            return [
                'goal' => $goal,
                'current_value' => $progress['current_value'],
                'progress' => $progress['progress'],
                'is_reached' => $progress['is_reached'],
                'status' => $progress['status'],
            ];
        });
    }

    /**
     * Duplica uma meta para uma nova temporada
     *
     * @param Goal $goal
     * @param string $seasonId
     * @return Goal
     */
    public function duplicateForSeason(Goal $goal, string $seasonId): Goal
    {
        return Goal::create([
            'scope' => $goal->scope,
            'season_id' => $seasonId,
            'team_id' => $goal->team_id,
            'seller_id' => $goal->seller_id,
            'name' => $goal->name . ' (Cópia)',
            'description' => $goal->description,
            'target_value' => $goal->target_value,
            'starts_at' => $goal->starts_at,
            'ends_at' => $goal->ends_at,
        ]);
    }
}
