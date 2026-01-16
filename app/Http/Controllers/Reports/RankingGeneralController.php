<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Models\Team;
use App\Services\RankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RankingGeneralController extends Controller
{
    public function __construct(
        private RankingService $rankingService
    ) {}

    /**
     * Exibe o relatório de ranking geral
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin', 'supervisor', 'user'])) {
            abort(403, 'Acesso negado');
        }

        $allowedTeamIds = $user->getSupervisedTeamIds();

        // Filtros
        $seasonId = $request->query('season');
        $teamId = $request->query('team');
        $startDate = $request->query('start_date') 
            ? Carbon::parse($request->query('start_date')) 
            : null;
        $endDate = $request->query('end_date') 
            ? Carbon::parse($request->query('end_date')) 
            : null;
        $limit = $request->query('limit') ? (int) $request->query('limit') : 100;

        // Validação de equipe permitida
        if ($teamId && $allowedTeamIds !== null && !in_array($teamId, $allowedTeamIds)) {
            $teamId = null;
        }

        // Obter dados
        $ranking = $this->rankingService->getGeneralRanking(
            $allowedTeamIds,
            $seasonId,
            $startDate,
            $endDate,
            $teamId,
            $limit
        );

        // Dados para filtros
        $seasons = Season::orderBy('name')->get();
        $teamsQuery = Team::orderBy('name');
        if ($allowedTeamIds !== null) {
            $teamsQuery->whereIn('id', $allowedTeamIds);
        }
        $teams = $teamsQuery->get();

        // Exportação
        if ($request->query('export') === 'csv') {
            return $this->exportCsv($ranking);
        }

        if ($request->query('export') === 'xlsx') {
            return $this->exportXlsx($ranking);
        }

        return view('reports.ranking-general', compact(
            'ranking',
            'seasons',
            'teams',
            'seasonId',
            'teamId',
            'startDate',
            'endDate',
            'limit'
        ));
    }

    /**
     * Exporta ranking para CSV
     */
    private function exportCsv($ranking)
    {
        $filename = 'ranking-geral-' . now()->format('Y-m-d-His') . '.csv';

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
                'Posição',
                'Nome',
                'Email',
                'Equipe',
                'Temporada',
                'Pontos',
                'Evolução'
            ], ';');

            // Dados
            foreach ($ranking as $item) {
                $evolution = $item['evolution'] !== null
                    ? ($item['evolution'] > 0 ? "+{$item['evolution']}" : (string) $item['evolution'])
                    : '-';
                
                fputcsv($file, [
                    $item['position'],
                    $item['seller_name'],
                    $item['seller_email'],
                    $item['team_name'] ?? '-',
                    $item['season_name'] ?? '-',
                    number_format($item['points'], 2, ',', '.'),
                    $evolution
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporta ranking para XLSX
     */
    private function exportXlsx($ranking)
    {
        // Por enquanto, retornar CSV
        // Para XLSX real, seria necessário instalar uma biblioteca como PhpSpreadsheet
        return $this->exportCsv($ranking);
    }
}
