<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Services\GamificationService;
use App\Services\SectorService;
use Illuminate\Http\Request;

class GamificationController extends Controller
{
    public function __construct(
        private GamificationService $gamificationService
    ) {}

    /**
     * Exibe o relatório de gamificação
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin', 'supervisor', 'user'])) {
            abort(403, 'Acesso negado');
        }

        $allowedTeamIds = $user->getSupervisedTeamIds();
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);

        // Query base
        $query = Seller::with(['teams', 'season']);
        if ($sectorId) {
            $query->where('sector_id', $sectorId);
        }

        // Filtrar por equipes permitidas
        if ($allowedTeamIds !== null) {
            $query->whereHas('teams', function ($q) use ($allowedTeamIds) {
                $q->whereIn('teams.id', $allowedTeamIds);
            });
        }

        // Ordenar por pontos
        $sellers = $query->orderBy('points', 'desc')->paginate(50);

        // Enriquecer com dados de gamificação
        $sellers->getCollection()->transform(function ($seller) {
            $gamification = $this->gamificationService->getGamificationInfo($seller->points);

            return [
                'id' => $seller->id,
                'name' => $seller->name,
                'email' => $seller->email,
                'team_name' => $seller->team?->name,
                'season_name' => $seller->season?->name,
                'points' => (float) $seller->points,
                'level' => $gamification['level'],
                'badge' => $gamification['badge'],
                'progress' => $gamification['progress'],
            ];
        });

        // Agrupar por nível
        $byLevel = $sellers->getCollection()->groupBy('level')->map(function ($group, $level) {
            return [
                'level' => (int) $level,
                'badge' => $group->first()['badge'],
                'count' => $group->count(),
                'sellers' => $group->values(),
            ];
        })->sortByDesc('level')->values();

        // Exportação
        if ($request->query('export') === 'csv') {
            return $this->exportCsv($sellers);
        }

        return view('reports.gamification', compact(
            'sellers',
            'byLevel'
        ));
    }

    /**
     * Exporta gamificação para CSV
     */
    private function exportCsv($sellers)
    {
        $filename = 'gamificacao-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($sellers) {
            $file = fopen('php://output', 'w');

            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Cabeçalhos
            fputcsv($file, [
                'Nome',
                'Email',
                'Equipe',
                'Temporada',
                'Pontos',
                'Nível',
                'Badge',
                'Progresso %'
            ], ';');

            // Dados
            foreach ($sellers->items() as $seller) {
                fputcsv($file, [
                    $seller['name'],
                    $seller['email'],
                    $seller['team_name'] ?? '-',
                    $seller['season_name'] ?? '-',
                    number_format($seller['points'], 2, ',', '.'),
                    $seller['level'],
                    $seller['badge'],
                    number_format($seller['progress'], 2, ',', '.')
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
