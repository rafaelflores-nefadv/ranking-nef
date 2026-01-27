<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Services\DashboardAnalyticsService;
use App\Services\SectorService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardAnalyticsService $analyticsService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $allowedTeamIds = $user->getSupervisedTeamIds();
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);

        // Obter filtros da requisição
        $filters = [
            'team_id' => $request->query('team'),
            'season_id' => $request->query('season'),
            'month' => $request->query('month') ? (int) $request->query('month') : null,
            'year' => $request->query('year') ? (int) $request->query('year') : Carbon::now()->year,
        ];

        // Obter dados analíticos
        $analyticsData = $this->analyticsService->getAnalyticsData($filters, $allowedTeamIds, $sectorId);

        // Obter listas para filtros
        $teams = $this->analyticsService->getAvailableTeams($allowedTeamIds, $sectorId);
        $seasons = $this->analyticsService->getAvailableSeasons();

        // Configurações do sistema
        $configs = Config::all()->pluck('value', 'key');

        // Preparar dados para o frontend (JavaScript)
        $dashboardData = [
            'evolucao_mensal' => $analyticsData['evolucao_mensal'],
            'top_fornecedores' => $analyticsData['top_fornecedores'],
        ];

        return view('dashboard.analytics', array_merge($analyticsData, [
            'teams' => $teams,
            'seasons' => $seasons,
            'filters' => $filters,
            'configs' => $configs,
            'dashboard_data' => $dashboardData,
        ]));
    }

}
