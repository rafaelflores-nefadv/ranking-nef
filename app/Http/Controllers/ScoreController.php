<?php

namespace App\Http\Controllers;

use App\Models\Monitor;
use App\Models\Sector;
use App\Models\Score;
use App\Services\SectorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ScoreController extends Controller
{
    /**
     * Retorna vendas recentes para notificação em tempo real.
     */
    public function recent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'since' => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $limit = $validated['limit'] ?? 10;
        $since = isset($validated['since'])
            ? Carbon::parse($validated['since'], 'UTC')->utc()
            : null;
        if ($since && $since->greaterThan(Carbon::now('UTC')->addMinutes(5))) {
            \Log::warning('ScoreController: parâmetro since no futuro, ignorando', [
                'since' => $since->toIso8601String(),
            ]);
            $since = null;
        }
        $sectorId = null;
        $sectorIds = null;
        $allowedTeamIds = null;
        $monitorSlug = $request->query('monitor');
        if ($monitorSlug) {
            $monitor = Monitor::where('slug', $monitorSlug)
                ->where('is_active', true)
                ->first();
            if (!$monitor) {
                abort(404, 'Monitor não encontrado');
            }

            $sectorIds = $monitor->getSectorIds();
            if (empty($sectorIds) && $monitor->sector_id) {
                $sectorIds = [$monitor->sector_id];
            }
            $sectorIds = array_values(array_filter($sectorIds ?: []));

            $explicitTeams = $monitor->getAllowedTeamIds();
            if (!empty($explicitTeams)) {
                $allowedTeamIds = $explicitTeams;
            }
        }
        if (!$sectorIds) {
            $requestedSector = $request->query('sector');
            if ($requestedSector) {
                $sectorId = Sector::where('id', $requestedSector)
                    ->where('is_active', true)
                    ->value('id');
            }
        }
        if (!$sectorIds && $sectorId) {
            $sectorIds = [$sectorId];
        }
        if (!$sectorIds) {
            $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);
            $sectorIds = $sectorId ? [$sectorId] : [];
        }

        $query = Score::with([
            'seller:id,name',
            'scoreRule:id,ocorrencia',
        ])->orderBy('created_at', 'desc');
        if (!empty($sectorIds)) {
            $query->whereIn('sector_id', $sectorIds);
        }

        // Se monitor tem equipes explicitamente configuradas, limitar scores ao escopo permitido
        if (!empty($allowedTeamIds)) {
            $query->whereHas('seller', function ($sellerQuery) use ($allowedTeamIds) {
                $sellerQuery->where(function ($q) use ($allowedTeamIds) {
                    $q->whereHas('teams', function ($teamQuery) use ($allowedTeamIds) {
                        $teamQuery->whereIn('teams.id', $allowedTeamIds);
                    })->orWhereDoesntHave('teams');
                });
            });
        }

        if ($since) {
            $query->where('created_at', '>', $since);
        }

        $scores = $query->limit($limit)->get()->values();

        \Log::info('ScoreController: vendas recentes', [
            'monitor_slug' => $monitorSlug,
            'sector_ids' => $sectorIds,
            'allowed_team_ids' => $allowedTeamIds,
            'since' => $since?->toIso8601String(),
            'limit' => $limit,
            'count' => $scores->count(),
            'first_created_at' => $scores->first()?->created_at?->copy()->utc()->toIso8601String(),
        ]);

        return response()->json([
            'data' => $scores->map(function (Score $score) {
                return [
                    'id' => $score->id,
                    'created_at' => $score->created_at?->copy()->utc()->toIso8601String(),
                    'points' => (float) $score->points,
                    'seller' => [
                        'id' => $score->seller?->id,
                        'name' => $score->seller?->name,
                    ],
                    'occurrence' => [
                        'id' => $score->scoreRule?->id,
                        'type' => $score->scoreRule?->ocorrencia,
                    ],
                ];
            }),
        ]);
    }
}
