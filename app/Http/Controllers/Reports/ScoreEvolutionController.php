<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Services\EvolutionService;
use App\Services\SectorService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ScoreEvolutionController extends Controller
{
    public function __construct(
        private EvolutionService $evolutionService
    ) {}

    /**
     * Exibe o relatório de evolução de pontuação
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
        $sellerId = $request->query('seller') ?: null;
        $startDate = $request->query('start_date') 
            ? Carbon::parse($request->query('start_date')) 
            : Carbon::now()->subDays(30);
        $endDate = $request->query('end_date') 
            ? Carbon::parse($request->query('end_date')) 
            : Carbon::now();

        // Obter dados
        $evolution = $this->evolutionService->getScoreEvolution(
            $sellerId,
            $allowedTeamIds,
            $startDate,
            $endDate,
            $sectorId
        );

        // Dados para filtros
        $sellersQuery = Seller::with('teams')->orderBy('name');
        if ($sectorId) {
            $sellersQuery->where('sector_id', $sectorId);
        }
        if ($allowedTeamIds !== null) {
            $sellersQuery->whereHas('teams', function ($q) use ($allowedTeamIds) {
                $q->whereIn('teams.id', $allowedTeamIds);
            });
        }
        $sellers = $sellersQuery->get();

        // Exportação
        if ($request->query('export') === 'csv') {
            return $this->exportCsv($evolution);
        }

        return view('reports.score-evolution', compact(
            'evolution',
            'sellers',
            'sellerId',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Exporta evolução para CSV
     */
    private function exportCsv($evolution)
    {
        $filename = 'evolucao-pontuacao-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($evolution) {
            $file = fopen('php://output', 'w');

            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Cabeçalhos
            fputcsv($file, [
                'Vendedor',
                'Email',
                'Equipe',
                'Data',
                'Pontos do Dia',
                'Ocorrências',
                'Pontos Acumulados'
            ], ';');

            // Dados
            foreach ($evolution as $sellerEvolution) {
                foreach ($sellerEvolution['evolution'] as $day) {
                    fputcsv($file, [
                        $sellerEvolution['seller_name'],
                        $sellerEvolution['seller_email'],
                        $sellerEvolution['team_name'],
                        $day['date'],
                        number_format($day['points'], 2, ',', '.'),
                        $day['occurrences'],
                        number_format($day['accumulated'], 2, ',', '.')
                    ], ';');
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
