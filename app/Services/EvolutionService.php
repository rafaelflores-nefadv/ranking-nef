<?php

namespace App\Services;

use App\Models\Seller;
use App\Models\Score;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EvolutionService
{
    public function __construct(
        private ReportService $reportService
    ) {}

    /**
     * Retorna evolução de pontuação por vendedor agregada por dia
     *
     * @param string|null $sellerId ID do vendedor (null = todos)
     * @param array|null $allowedTeamIds IDs de equipes permitidas
     * @param Carbon|null $startDate Data de início
     * @param Carbon|null $endDate Data de fim
     * @param string|null $sectorId Setor atual
     * @return Collection
     */
    public function getScoreEvolution(
        ?string $sellerId = null,
        ?array $allowedTeamIds = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?string $sectorId = null
    ): Collection {
        $startDate = $startDate ?? Carbon::now()->subDays(30);
        $endDate = $endDate ?? Carbon::now();

        $query = Score::query()
            ->select(
                'seller_id',
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(points) as total_points'),
                DB::raw('COUNT(*) as occurrences_count')
            )
            ->with('seller.teams')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->groupBy('seller_id', DB::raw('DATE(created_at)'));
        if ($sectorId) {
            $query->where('sector_id', $sectorId);
        }

        // Filtro por vendedor
        if ($sellerId) {
            $query->where('seller_id', $sellerId);
        }

        $scores = $query->get();

        // Filtrar por equipes permitidas
        if ($allowedTeamIds !== null) {
            $scores = $scores->filter(function ($score) use ($allowedTeamIds) {
                return $score->seller
                    && $score->seller->teams->pluck('id')->intersect($allowedTeamIds)->isNotEmpty();
            });
        }

        // Agrupar por vendedor e data
        $grouped = $scores->groupBy('seller_id')->map(function ($sellerScores, $sellerId) {
            $seller = $sellerScores->first()->seller;
            $dailyData = $sellerScores->map(function ($score) {
                return [
                    'date' => Carbon::parse($score->date)->format('Y-m-d'),
                    'points' => (float) $score->total_points,
                    'occurrences' => (int) $score->occurrences_count,
                ];
            })->sortBy('date')->values();

            // Calcular acumulado
            $accumulated = 0;
            $dailyData = $dailyData->map(function ($day) use (&$accumulated) {
                $accumulated += $day['points'];
                $day['accumulated'] = (float) $accumulated;
                return $day;
            });

            return [
                'seller_id' => $sellerId,
                'seller_name' => $seller?->name ?? 'N/A',
                'seller_email' => $seller?->email ?? 'N/A',
                'team_name' => $seller?->team?->display_label ?? 'N/A',
                'evolution' => $dailyData,
            ];
        });

        return $grouped->values();
    }
}
