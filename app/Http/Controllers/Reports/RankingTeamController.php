<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Services\RankingService;
use App\Services\SectorService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RankingTeamController extends Controller
{
    public function __construct(
        private RankingService $rankingService
    ) {}

    /**
     * Exibe o relatório de ranking por equipe
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin', 'supervisor', 'user'])) {
            abort(403, 'Acesso negado');
        }

        $allowedTeamIds = $user->getSupervisedTeamIds();
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);

        // Filtros
        $seasonId = $request->query('season') ?: null;

        // Obter dados
        $ranking = $this->rankingService->getTeamRanking(
            $allowedTeamIds,
            $seasonId,
            $sectorId
        );

        // Dados para filtros
        $seasons = Season::orderBy('name')->get();

        // Exportação
        if ($request->query('export') === 'csv') {
            return $this->exportCsv($ranking);
        }

        return view('reports.ranking-team', compact(
            'ranking',
            'seasons',
            'seasonId'
        ));
    }

    /**
     * Exporta ranking para CSV
     */
    private function exportCsv($ranking)
    {
        $filename = 'ranking-equipes-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($ranking) {
            $file = fopen('php://output', 'w');

            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Cabeçalhos
            fputcsv($file, [
                'Equipe',
                'Posição',
                'Vendedor',
                'Email',
                'Pontos',
                'Contribuição %'
            ], ';');

            // Dados
            foreach ($ranking as $team) {
                foreach ($team['sellers'] as $seller) {
                    fputcsv($file, [
                        $team['team_name'],
                        $seller['position'],
                        $seller['seller_name'],
                        $seller['seller_email'],
                        number_format($seller['points'], 2, ',', '.'),
                        number_format($seller['percentage'], 2, ',', '.') . '%'
                    ], ';');
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
