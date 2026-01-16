<?php

namespace App\Services;

use App\Models\Season;
use App\Models\Seller;
use App\Models\Team;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RankingService
{
    public function __construct(
        private ReportService $reportService
    ) {}

    /**
     * Gera ranking geral de vendedores
     *
     * @param array|null $allowedTeamIds IDs de equipes permitidas (null = todas)
     * @param string|null $seasonId ID da temporada
     * @param Carbon|null $startDate Data de início
     * @param Carbon|null $endDate Data de fim
     * @param string|null $teamId ID da equipe (opcional)
     * @param int|null $limit Limite de posições
     * @return Collection
     */
    public function getGeneralRanking(
        ?array $allowedTeamIds = null,
        ?string $seasonId = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?string $teamId = null,
        ?int $limit = null
    ): Collection {
        $query = Seller::with(['team', 'season']);

        // Filtro de equipes permitidas
        $this->reportService->filterTeamsByUser($query, $allowedTeamIds);

        // Filtro por equipe específica
        if ($teamId) {
            // Verificar se a equipe está nas permitidas
            if ($allowedTeamIds === null || in_array($teamId, $allowedTeamIds)) {
                $query->where('team_id', $teamId);
            }
        }

        // Filtro por temporada
        if ($seasonId) {
            $query->where('season_id', $seasonId);
        }

        // Filtro por período (através dos scores)
        if ($startDate || $endDate) {
            $query->whereHas('scores', function ($q) use ($startDate, $endDate) {
                if ($startDate) {
                    $q->where('created_at', '>=', $startDate->startOfDay());
                }
                if ($endDate) {
                    $q->where('created_at', '<=', $endDate->endOfDay());
                }
            });
        }

        $sellers = $query->orderBy('points', 'desc')->get();

        // Calcular evolução em relação ao período anterior
        $previousPositions = [];
        if ($startDate && $endDate) {
            $previousEndDate = $startDate->copy()->subDay();
            $previousStartDate = $previousEndDate->copy()->subDays($startDate->diffInDays($endDate));

            $previousRanking = $this->getGeneralRanking(
                $allowedTeamIds,
                $seasonId,
                $previousStartDate,
                $previousEndDate,
                $teamId,
                null
            );

            $previousPositions = $previousRanking->pluck('position', 'seller_id')->toArray();
        }

        $finalPositions = $previousPositions;
        $sellers = $sellers->map(function ($seller, $index) use ($finalPositions, $startDate, $endDate) {
            $position = $index + 1;
            $previousPosition = $finalPositions[$seller->id] ?? null;
            
            $evolution = null;
            if ($startDate && $endDate && $previousPosition !== null) {
                $evolution = $previousPosition - $position; // Positivo = subiu, Negativo = desceu
            }

            return [
                'seller_id' => $seller->id,
                'seller_name' => $seller->name,
                'seller_email' => $seller->email,
                'team_name' => $seller->team?->name,
                'season_name' => $seller->season?->name,
                'points' => (float) $seller->points,
                'position' => $position,
                'evolution' => $evolution,
            ];
        });

        // Aplicar limite
        if ($limit) {
            $sellers = $sellers->take($limit);
        }

        return $sellers->values();
    }

    /**
     * Gera ranking por equipe
     *
     * @param array|null $allowedTeamIds IDs de equipes permitidas (null = todas)
     * @param string|null $seasonId ID da temporada
     * @return Collection
     */
    public function getTeamRanking(
        ?array $allowedTeamIds = null,
        ?string $seasonId = null
    ): Collection {
        $query = Team::with(['sellers' => function ($q) use ($seasonId) {
            if ($seasonId) {
                $q->where('season_id', $seasonId);
            }
            $q->orderBy('points', 'desc');
        }]);

        // Filtro de equipes permitidas
        if ($allowedTeamIds !== null) {
            $query->whereIn('id', $allowedTeamIds);
        }

        $teams = $query->get();

        return $teams->map(function ($team) {
            $sellers = $team->sellers;
            $teamTotalPoints = $sellers->sum('points');

            $sellersData = $sellers->map(function ($seller, $index) use ($teamTotalPoints) {
                $percentage = $teamTotalPoints > 0 
                    ? round(($seller->points / $teamTotalPoints) * 100, 2)
                    : 0;

                return [
                    'seller_id' => $seller->id,
                    'seller_name' => $seller->name,
                    'seller_email' => $seller->email,
                    'points' => (float) $seller->points,
                    'position' => $index + 1,
                    'percentage' => $percentage,
                ];
            });

            return [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'total_points' => (float) $teamTotalPoints,
                'sellers_count' => $sellers->count(),
                'sellers' => $sellersData,
            ];
        });
    }
}
