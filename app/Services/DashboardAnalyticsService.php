<?php

namespace App\Services;

use App\Models\Config;
use App\Models\Score;
use App\Models\Seller;
use App\Models\Season;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    /**
     * Obtém dados agregados para a dashboard analítica
     *
     * @param array $filters ['team_id', 'season_id', 'month', 'year']
     * @param array|null $allowedTeamIds Equipes permitidas para o usuário
     * @return array
     */
    public function getAnalyticsData(array $filters = [], ?array $allowedTeamIds = null): array
    {
        $teamId = $filters['team_id'] ?? null;
        $seasonId = $filters['season_id'] ?? null;
        $month = $filters['month'] ?? null;
        $year = $filters['year'] ?? Carbon::now()->year;

        // Se não houver temporada selecionada, usar temporada ativa
        if (!$seasonId) {
            $activeSeason = Season::where('is_active', true)->first();
            if ($activeSeason) {
                $seasonId = $activeSeason->id;
            }
        }

        // Obter percentual de margem do sistema (padrão 40%)
        $margemPercentual = (float) (Config::where('key', 'margem_contribuicao_percentual')->value('value') ?? 40);
        $margemPercentual = $margemPercentual / 100; // Converter para decimal

        // Construir query base para scores
        $scoresQuery = Score::query()
            ->join('sellers', 'scores.seller_id', '=', 'sellers.id')
            ->select(
                'scores.*',
                'sellers.team_id',
                'sellers.season_id'
            );

        // Aplicar filtros
        if ($teamId) {
            if ($allowedTeamIds === null || in_array($teamId, $allowedTeamIds)) {
                $scoresQuery->where('sellers.team_id', $teamId);
            } else {
                $scoresQuery->whereRaw('1 = 0'); // Sem permissão
            }
        } elseif ($allowedTeamIds !== null) {
            $scoresQuery->whereIn('sellers.team_id', $allowedTeamIds);
        }

        if ($seasonId) {
            $scoresQuery->where('sellers.season_id', $seasonId);
        }

        if ($month) {
            $scoresQuery->whereMonth('scores.created_at', $month);
        }

        if ($year) {
            $scoresQuery->whereYear('scores.created_at', $year);
        }

        // Dados agregados gerais
        $totalReceita = (float) $scoresQuery->sum('scores.points');
        $totalScores = $scoresQuery->count();
        $avgPorScore = $totalScores > 0 ? $totalReceita / $totalScores : 0;

        // Margem de contribuição usando percentual do sistema
        $margemContribuicao = $totalReceita * $margemPercentual;
        $percentualMC = $totalReceita > 0 ? ($margemContribuicao / $totalReceita) * 100 : 0;

        // Evolução por mês (últimos 6 meses ou período da temporada)
        $evolucaoMensal = $this->getEvolucaoMensal($scoresQuery, $year, $seasonId, $teamId, $month, $allowedTeamIds, $margemPercentual);

        // Melhor mês
        $melhorMes = $this->getMelhorMes($evolucaoMensal);

        // Dados por equipe
        $dadosPorEquipe = $this->getDadosPorEquipe($scoresQuery, $teamId, $allowedTeamIds, $margemPercentual);

        // Dados por temporada
        $dadosPorTemporada = $this->getDadosPorTemporada($scoresQuery, $seasonId, $margemPercentual);

        // Top fornecedores (baseado em score_rules/ocorrencias)
        $topFornecedores = $this->getTopFornecedores($scoresQuery, $margemPercentual, $teamId, $seasonId, $month, $year, $allowedTeamIds);

        return [
            'total_receita' => $totalReceita,
            'margem_contribuicao' => $margemContribuicao,
            'percentual_mc' => round($percentualMC, 2),
            'total_scores' => $totalScores,
            'media_por_score' => round($avgPorScore, 2),
            'evolucao_mensal' => $evolucaoMensal,
            'melhor_mes' => $melhorMes,
            'dados_por_equipe' => $dadosPorEquipe,
            'dados_por_temporada' => $dadosPorTemporada,
            'top_fornecedores' => $topFornecedores,
        ];
    }

    /**
     * Obtém evolução mensal dos últimos 6 meses ou período da temporada
     */
    private function getEvolucaoMensal($baseQuery, ?int $year, ?string $seasonId, ?string $teamId, ?int $month, ?array $allowedTeamIds, float $margemPercentual): array
    {
        $meses = [];
        $now = Carbon::now();
        $year = $year ?? $now->year;

        // Se há filtro de mês específico, mostrar apenas esse mês
        if ($month) {
            $data = $now->copy()->month($month)->year($year);
            $mes = $data->month;
            $ano = $data->year;

            $query = Score::query()
                ->join('sellers', 'scores.seller_id', '=', 'sellers.id')
                ->whereYear('scores.created_at', $ano)
                ->whereMonth('scores.created_at', $mes);

            // Aplicar filtros
            if ($teamId) {
                if ($allowedTeamIds === null || in_array($teamId, $allowedTeamIds)) {
                    $query->where('sellers.team_id', $teamId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } elseif ($allowedTeamIds !== null) {
                $query->whereIn('sellers.team_id', $allowedTeamIds);
            }

            if ($seasonId) {
                $query->where('sellers.season_id', $seasonId);
            }

            $total = (float) $query->sum('scores.points');
            $margem = $total * $margemPercentual;
            $percentual = $total > 0 ? ($margem / $total) * 100 : 0;

            return [[
                'mes' => $mes,
                'ano' => $ano,
                'mes_nome' => $data->locale('pt_BR')->translatedFormat('M'),
                'receita' => $total,
                'margem' => $margem,
                'percentual' => round($percentual, 2),
            ]];
        }

        // Caso contrário, mostrar últimos 6 meses
        for ($i = 5; $i >= 0; $i--) {
            $data = $now->copy()->subMonths($i);
            $mes = $data->month;
            $ano = $data->year;

            $query = Score::query()
                ->join('sellers', 'scores.seller_id', '=', 'sellers.id')
                ->whereYear('scores.created_at', $ano)
                ->whereMonth('scores.created_at', $mes);

            // Aplicar filtros
            if ($teamId) {
                if ($allowedTeamIds === null || in_array($teamId, $allowedTeamIds)) {
                    $query->where('sellers.team_id', $teamId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } elseif ($allowedTeamIds !== null) {
                $query->whereIn('sellers.team_id', $allowedTeamIds);
            }

            if ($seasonId) {
                $query->where('sellers.season_id', $seasonId);
            }

            $total = (float) $query->sum('scores.points');
            $margem = $total * $margemPercentual;
            $percentual = $total > 0 ? ($margem / $total) * 100 : 0;

            $meses[] = [
                'mes' => $mes,
                'ano' => $ano,
                'mes_nome' => $data->locale('pt_BR')->translatedFormat('M'),
                'receita' => $total,
                'margem' => $margem,
                'percentual' => round($percentual, 2),
            ];
        }

        return $meses;
    }

    /**
     * Obtém o melhor mês baseado na receita
     */
    private function getMelhorMes(array $evolucaoMensal): ?array
    {
        if (empty($evolucaoMensal)) {
            return null;
        }

        $melhor = collect($evolucaoMensal)->sortByDesc('receita')->first();
        return $melhor;
    }

    /**
     * Obtém dados agregados por equipe
     */
    private function getDadosPorEquipe($baseQuery, ?string $teamId, ?array $allowedTeamIds, float $margemPercentual): Collection
    {
        // Obter filtros da query base
        $seasonFilter = null;
        foreach ($baseQuery->getQuery()->wheres as $where) {
            if (isset($where['column']) && $where['column'] === 'sellers.season_id' && $where['type'] === 'Basic') {
                $seasonFilter = $where['value'];
            }
        }

        $query = Score::query()
            ->join('sellers', 'scores.seller_id', '=', 'sellers.id')
            ->join('teams', 'sellers.team_id', '=', 'teams.id')
            ->select(
                'teams.id',
                'teams.name',
                DB::raw('SUM(scores.points) as total_receita'),
                DB::raw('COUNT(scores.id) as total_scores')
            )
            ->groupBy('teams.id', 'teams.name');

        if ($teamId) {
            $query->where('teams.id', $teamId);
        } elseif ($allowedTeamIds !== null) {
            $query->whereIn('teams.id', $allowedTeamIds);
        }

        if ($seasonFilter) {
            $query->where('sellers.season_id', $seasonFilter);
        }

        return $query->get()->map(function ($item) {
            $receita = (float) $item->total_receita;
            $margem = $receita * 0.40;
            $percentual = $receita > 0 ? ($margem / $receita) * 100 : 0;

            return [
                'id' => $item->id,
                'name' => $item->name,
                'receita' => $receita,
                'margem' => $margem,
                'percentual_mc' => round($percentual, 2),
                'total_scores' => (int) $item->total_scores,
            ];
        })->sortByDesc('receita');
    }

    /**
     * Obtém dados agregados por temporada
     */
    private function getDadosPorTemporada($baseQuery, ?string $seasonId, float $margemPercentual): Collection
    {
        // Obter filtros da query base
        $teamFilter = null;
        $allowedTeamIds = null;
        foreach ($baseQuery->getQuery()->wheres as $where) {
            if (isset($where['column'])) {
                if ($where['column'] === 'sellers.team_id' && $where['type'] === 'Basic') {
                    $teamFilter = $where['value'];
                } elseif ($where['column'] === 'sellers.team_id' && $where['type'] === 'In') {
                    $allowedTeamIds = $where['values'];
                }
            }
        }

        $query = Score::query()
            ->join('sellers', 'scores.seller_id', '=', 'sellers.id')
            ->join('seasons', 'sellers.season_id', '=', 'seasons.id')
            ->select(
                'seasons.id',
                'seasons.name',
                DB::raw('SUM(scores.points) as total_receita'),
                DB::raw('COUNT(scores.id) as total_scores')
            )
            ->groupBy('seasons.id', 'seasons.name');

        if ($seasonId) {
            $query->where('seasons.id', $seasonId);
        }

        if ($teamFilter) {
            $query->where('sellers.team_id', $teamFilter);
        } elseif ($allowedTeamIds !== null) {
            $query->whereIn('sellers.team_id', $allowedTeamIds);
        }

        return $query->get()->map(function ($item) use ($margemPercentual) {
            $receita = (float) $item->total_receita;
            $margem = $receita * $margemPercentual;
            $percentual = $receita > 0 ? ($margem / $receita) * 100 : 0;

            return [
                'id' => $item->id,
                'name' => $item->name,
                'receita' => $receita,
                'margem' => $margem,
                'percentual_mc' => round($percentual, 2),
                'total_scores' => (int) $item->total_scores,
            ];
        })->sortByDesc('receita');
    }

    /**
     * Obtém top fornecedores (baseado em ocorrências/score_rules)
     */
    private function getTopFornecedores($baseQuery, float $margemPercentual, ?string $teamId, ?string $seasonId, ?int $month, ?int $year, ?array $allowedTeamIds): Collection
    {
        $query = Score::query()
            ->join('sellers', 'scores.seller_id', '=', 'sellers.id')
            ->join('score_rules', 'scores.score_rule_id', '=', 'score_rules.id')
            ->select(
                'score_rules.ocorrencia as fornecedor',
                'score_rules.description',
                DB::raw('SUM(scores.points) as total_receita'),
                DB::raw('COUNT(scores.id) as total_ocorrencias')
            )
            ->groupBy('score_rules.ocorrencia', 'score_rules.description')
            ->orderByDesc('total_receita')
            ->limit(10);

        // Aplicar mesmos filtros da query base
        if ($teamId) {
            if ($allowedTeamIds === null || in_array($teamId, $allowedTeamIds)) {
                $query->where('sellers.team_id', $teamId);
            } else {
                $query->whereRaw('1 = 0'); // Sem permissão
            }
        } elseif ($allowedTeamIds !== null) {
            $query->whereIn('sellers.team_id', $allowedTeamIds);
        }

        if ($seasonId) {
            $query->where('sellers.season_id', $seasonId);
        }

        if ($month) {
            $query->whereMonth('scores.created_at', $month);
        }

        if ($year) {
            $query->whereYear('scores.created_at', $year);
        }

        return $query->get()->map(function ($item) use ($margemPercentual) {
            $receita = (float) $item->total_receita;
            $margem = $receita * $margemPercentual;
            $percentual = $receita > 0 ? ($margem / $receita) * 100 : 0;

            return [
                'fornecedor' => $item->fornecedor,
                'descricao' => $item->description,
                'receita' => $receita,
                'margem' => $margem,
                'percentual_mc' => round($percentual, 2),
                'total_ocorrencias' => (int) $item->total_ocorrencias,
            ];
        });
    }

    /**
     * Obtém lista de equipes disponíveis
     */
    public function getAvailableTeams(?array $allowedTeamIds = null): Collection
    {
        $query = Team::orderBy('name');
        
        if ($allowedTeamIds !== null) {
            $query->whereIn('id', $allowedTeamIds);
        }

        return $query->get(['id', 'name']);
    }

    /**
     * Obtém lista de temporadas disponíveis
     */
    public function getAvailableSeasons(): Collection
    {
        return Season::orderBy('starts_at', 'desc')
            ->get(['id', 'name', 'starts_at', 'ends_at', 'is_active']);
    }
}
