<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Services\GamificationService;
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
        $seasonId = $request->query('season_id');
        $teamId = $request->query('team_id');

        $query = Seller::with(['team', 'season'])
            ->where('status', 'active')
            ->orderBy('points', 'desc');

        if ($seasonId) {
            $query->where('season_id', $seasonId);
        }

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

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
