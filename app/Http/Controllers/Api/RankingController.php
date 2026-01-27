<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Models\Seller;
use App\Services\GamificationService;
use App\Services\SectorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function __construct(
        private GamificationService $gamificationService
    ) {}

    /**
     * Retorna o ranking de vendedores
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $allowedTeamIds = $user->getSupervisedTeamIds();
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);
        
        $seasonId = $request->query('season_id');
        $teamId = $request->query('team_id');

        // Se não especificou season_id, usar temporada ativa
        if (!$seasonId) {
            $activeSeason = Season::where('is_active', true)->first();
            if ($activeSeason) {
                $seasonId = $activeSeason->id;
            }
        }

        $query = Seller::with(['teams', 'season'])
            ->where('status', 'active')
            ->orderBy('points', 'desc');
        if ($sectorId) {
            $query->where('sector_id', $sectorId);
        }

        // Filtrar por temporada (ativa por padrão ou especificada)
        if ($seasonId) {
            $query->where('season_id', $seasonId);
        }

        if ($teamId) {
            // Filtro por equipe específica
            // Verificar se a equipe selecionada está nas permitidas
            if ($allowedTeamIds === null || in_array($teamId, $allowedTeamIds)) {
                $query->whereHas('teams', function ($q) use ($teamId) {
                    $q->where('teams.id', $teamId);
                });
            } else {
                // Se não tem permissão, não retornar vendedores
                $query->whereRaw('1 = 0');
            }
        } elseif ($allowedTeamIds !== null) {
            // Geral: mostrar vendedores das equipes permitidas OU vendedores sem equipe
            $query->where(function($q) use ($allowedTeamIds) {
                $q->whereHas('teams', function ($teamQuery) use ($allowedTeamIds) {
                    $teamQuery->whereIn('teams.id', $allowedTeamIds);
                })->orWhereDoesntHave('teams');
            });
        }
        // Se $teamId é null e $allowedTeamIds é null, mostrar todos (sem filtro adicional)

        $sellers = $query->get();

        $ranking = $sellers->map(function ($seller) {
            $gamification = $this->gamificationService->getGamificationInfo($seller->points);

            return [
                'id' => $seller->id,
                'name' => $seller->name,
                'email' => $seller->email,
                'points' => $seller->points,
                'status' => $seller->status,
                'team' => $seller->team ? [
                    'id' => $seller->team->id,
                    'name' => $seller->team->name,
                ] : null,
                'season' => $seller->season ? [
                    'id' => $seller->season->id,
                    'name' => $seller->season->name,
                ] : null,
                'gamification' => $gamification,
            ];
        });

        return response()->json($ranking);
    }
}
