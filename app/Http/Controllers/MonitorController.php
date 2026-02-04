<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Monitor;
use App\Models\Season;
use App\Models\Seller;
use App\Models\Team;
use App\Services\GamificationService;
use App\Services\SectorService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MonitorController extends Controller
{
    public function __construct(
        private GamificationService $gamificationService
    ) {}

    /**
     * Exibe o monitor público
     */
    public function show(string $slug)
    {
        $monitor = Monitor::where('slug', $slug)->firstOrFail();

        if (!$monitor->is_active) {
            abort(404, 'Monitor inativo');
        }

        $settings = $monitor->getMergedSettings();
        
        // Debug: Log das configurações
        \Log::info('Monitor: Settings carregadas', [
            'monitor_id' => $monitor->id,
            'monitor_name' => $monitor->name,
            'settings_raw' => $monitor->settings,
            'settings_merged' => $settings,
            'voice_enabled_raw' => $settings['voice_enabled'] ?? 'não definido',
            'voice_enabled_type' => gettype($settings['voice_enabled'] ?? null),
        ]);
        
        $sectorIds = $monitor->getSectorIds();
        if (empty($sectorIds)) {
            $defaultSectorId = app(SectorService::class)->getDefaultSectorId();
            $sectorIds = $defaultSectorId ? [$defaultSectorId] : [];
        }

        $allowedTeamIds = $monitor->getAllowedTeamIds();
        $explicitTeams = !empty($allowedTeamIds);
        $allowedTeamIdsFilter = $explicitTeams ? $allowedTeamIds : null;

        // Obter dados do dashboard (sem filtro de usuário - público)
        $data = $this->buildDashboardData(null, $allowedTeamIdsFilter, $sectorIds);
        $configs = Config::all()->pluck('value', 'key');
        
        // Buscar temporada ativa
        $activeSeason = \App\Models\Season::where('is_active', true)->first();
        
        // Normalizar configuração de eventos de notificação
        $notificationEventsConfig = $this->normalizeNotificationEventsConfig(
            $configs['notifications_events_config'] ?? null
        );

        // Preparar configuração JavaScript do monitor
        // Garantir que valores booleanos sejam passados corretamente
        $voiceEnabledValue = $settings['voice_enabled'] ?? false;
        // Converter para boolean explícito, tratando todos os casos possíveis
        if (is_string($voiceEnabledValue)) {
            $voiceEnabledValue = in_array(strtolower($voiceEnabledValue), ['true', '1', 'yes', 'on']);
        } elseif (is_int($voiceEnabledValue)) {
            $voiceEnabledValue = $voiceEnabledValue === 1;
        } else {
            $voiceEnabledValue = (bool)$voiceEnabledValue;
        }
        
        $dashboardConfig = [
            'refresh_interval' => $settings['refresh_interval'] ?? 30000,
            'auto_rotate_teams' => (bool)($settings['auto_rotate_teams'] ?? true),
            // equipes são controladas via pivot monitor_team; vazio = todas (dentro dos setores)
            'teams' => $explicitTeams ? $allowedTeamIds : [],
            'notifications_enabled' => (bool)($settings['notifications_enabled'] ?? false),
            'sound_enabled' => (bool)($settings['sound_enabled'] ?? false),
            'voice_enabled' => $voiceEnabledValue, // Já convertido para boolean acima
            'font_scale' => $settings['font_scale'] ?? 1.0,
            'monitor_slug' => $monitor->slug,
            'sector_ids' => $sectorIds,
            'sector_id' => $sectorIds[0] ?? null, // legacy/compat
        ];
        
        // Debug: Log da configuração final
        \Log::info('Monitor: DashboardConfig preparada', [
            'voice_enabled_final' => $dashboardConfig['voice_enabled'],
            'voice_enabled_final_type' => gettype($dashboardConfig['voice_enabled']),
            'dashboard_config' => $dashboardConfig,
        ]);

        // Obter tema configurado (padrão: 'default')
        $themeName = $configs['monitor_theme'] ?? 'default';
        
        // Garantir que o tema existe, caso contrário usar 'default'
        $themePath = resource_path("views/monitors/themes/{$themeName}/dashboard.blade.php");
        if (!file_exists($themePath)) {
            $themeName = 'default';
        }
        
        return view("monitors.themes.{$themeName}.dashboard", array_merge($data, [
            'configs' => $configs,
            'notificationEventsConfig' => $notificationEventsConfig,
            'monitor' => $monitor,
            'dashboardConfig' => $dashboardConfig,
            'activeSeason' => $activeSeason,
        ]));
    }

    /**
     * Retorna texto do ranking para leitura por voz
     * Lê o ranking geral e todas as equipes configuradas no monitor
     */
    public function voiceText(Request $request, string $slug)
    {
        $monitor = Monitor::where('slug', $slug)->firstOrFail();

        if (!$monitor->is_active) {
            abort(404, 'Monitor inativo');
        }

        $settings = $monitor->getMergedSettings();
        
        // Verificar se voz está habilitada no monitor
        if (!($settings['voice_enabled'] ?? false)) {
            return response()->json(['error' => 'Voz não habilitada no monitor'], 403);
        }

        $season = \App\Models\Season::where('is_active', true)->first();
        if (!$season) {
            return response()->json(['error' => 'Nenhuma temporada ativa'], 404);
        }
        $sectorIds = $monitor->getSectorIds();
        if (empty($sectorIds)) {
            $defaultSectorId = app(SectorService::class)->getDefaultSectorId();
            $sectorIds = $defaultSectorId ? [$defaultSectorId] : [];
        }

        $precision = (int)(\App\Models\Config::where('key', 'points_precision')->value('value') ?? 2);
        $requestedScope = $request->query('scope');
        $voiceScope = in_array($requestedScope, ['global', 'teams', 'both', 'team'], true)
            ? $requestedScope
            : (\App\Models\Config::where('key', 'notifications_voice_scope')->value('value') ?? 'global');
        $requestedTeamId = $request->query('team_id') ?? $request->query('team');
        
        $allTexts = [];

        // Ler ranking geral se configurado
        if (in_array($voiceScope, ['global', 'both'], true)) {
            $globalTop = \App\Models\Seller::query()
                ->where('season_id', $season->id)
                ->when(!empty($sectorIds), fn ($q) => $q->whereIn('sector_id', $sectorIds))
                ->orderBy('points', 'desc')
                ->limit(3)
                ->get(['id', 'name', 'points']);

            if ($globalTop->isNotEmpty()) {
                $parts = ['Top 3 do ranking geral:'];
                foreach ($globalTop as $index => $seller) {
                    $position = $index + 1;
                    $points = number_format((float) $seller->points, $precision, ',', '.');
                    $parts[] = "{$position}o lugar: {$seller->name}, {$points} pontos.";
                }
                $allTexts[] = implode(' ', $parts);
            }
        }

        // Ler ranking de cada equipe se configurado
        if (in_array($voiceScope, ['teams', 'both', 'team'], true)) {
            // Obter equipes permitidas no monitor ou todas
            $allowedTeamIds = null;
            $explicitTeamIds = $monitor->getAllowedTeamIds();
            if (!empty($explicitTeamIds)) {
                $allowedTeamIds = $explicitTeamIds;
            }
            $teamsQuery = \App\Models\Team::query()
                ->when(!empty($sectorIds), fn ($q) => $q->whereIn('sector_id', $sectorIds))
                ->orderBy('name');
            if ($allowedTeamIds !== null) {
                $teamsQuery->whereIn('id', $allowedTeamIds);
            }
            if ($voiceScope === 'team') {
                if (!$requestedTeamId) {
                    return response()->json(['error' => 'Equipe não informada'], 422);
                }
                $teamsQuery->where('id', $requestedTeamId);
            }
            $teams = $teamsQuery->get(['id', 'name', 'display_name']);
            if ($voiceScope === 'team' && $teams->isEmpty()) {
                return response()->json(['error' => 'Equipe não encontrada'], 404);
            }

            foreach ($teams as $team) {
                $teamTop = \App\Models\Seller::query()
                    ->where('season_id', $season->id)
                    ->when(!empty($sectorIds), fn ($q) => $q->whereIn('sector_id', $sectorIds))
                    ->whereHas('teams', function($query) use ($team) {
                        $query->where('teams.id', $team->id);
                    })
                    ->orderBy('points', 'desc')
                    ->limit(3)
                    ->get(['id', 'name', 'points']);

                if ($teamTop->isNotEmpty()) {
                    $parts = ["Top 3 da equipe {$team->display_label}:"];
                    foreach ($teamTop as $index => $seller) {
                        $position = $index + 1;
                        $points = number_format((float) $seller->points, $precision, ',', '.');
                        $parts[] = "{$position}o lugar: {$seller->name}, {$points} pontos.";
                    }
                    $allTexts[] = implode(' ', $parts);
                }
            }
        }

        if (empty($allTexts)) {
            return response()->json(['error' => 'Nenhum ranking encontrado'], 404);
        }

        // Juntar todos os textos com uma pausa entre eles
        $content = implode(' ', $allTexts);

        return response()->json([
            'content' => $content,
            'scope' => $voiceScope,
        ]);
    }

    /**
     * Retorna status da leitura por voz (scheduler)
     */
    public function voiceStatus(string $slug)
    {
        $monitor = Monitor::where('slug', $slug)->firstOrFail();

        if (!$monitor->is_active) {
            abort(404, 'Monitor inativo');
        }

        $sectorIds = $monitor->getSectorIds();
        if (empty($sectorIds)) {
            $defaultSectorId = app(SectorService::class)->getDefaultSectorId();
            $sectorIds = $defaultSectorId ? [$defaultSectorId] : [];
        }
        $enabled = (Config::where('key', 'notifications_voice_enabled')->value('value') ?? 'false') === 'true';
        $mode = Config::where('key', 'notifications_voice_mode')->value('value') ?? 'server';
        $intervalMinutes = (int) (Config::where('key', 'notifications_voice_interval_minutes')->value('value') ?? 15);

        $now = Carbon::now('UTC');

        $hasLastRun = false;
        $minNextRunAt = null;
        $maxLastRunAt = null;

        foreach ($sectorIds as $sectorId) {
            $lastRunKey = "notifications_voice_last_run_at_{$sectorId}";
            $lastRunValue = Config::where('key', $lastRunKey)->value('value');
            $lastRunAt = $lastRunValue ? Carbon::parse($lastRunValue, 'UTC')->utc() : null;
            $nextRunAt = $lastRunAt ? $lastRunAt->copy()->addMinutes($intervalMinutes) : $now->copy();

            if ($lastRunAt) {
                $hasLastRun = true;
                if (!$maxLastRunAt || $lastRunAt->greaterThan($maxLastRunAt)) {
                    $maxLastRunAt = $lastRunAt->copy();
                }
            }
            if (!$minNextRunAt || $nextRunAt->lessThan($minNextRunAt)) {
                $minNextRunAt = $nextRunAt->copy();
            }
        }

        $nextRunAt = $minNextRunAt ?? $now->copy();
        $remainingSeconds = max(0, $now->diffInSeconds($nextRunAt, false));
        $overdueSeconds = max(0, $nextRunAt->diffInSeconds($now, false));

        return response()->json([
            'enabled' => $enabled,
            'mode' => $mode,
            'interval_minutes' => $intervalMinutes,
            'sector_ids' => $sectorIds,
            'has_last_run' => (bool) $hasLastRun,
            'last_run_at' => $maxLastRunAt?->copy()->utc()->toIso8601String(),
            'next_run_at' => $nextRunAt->copy()->utc()->toIso8601String(),
            'remaining_seconds' => $remainingSeconds,
            'overdue_seconds' => $overdueSeconds,
        ]);
    }

    /**
     * Retorna dados JSON para atualização do monitor
     */
    public function data(Request $request, string $slug)
    {
        $monitor = Monitor::where('slug', $slug)->firstOrFail();

        if (!$monitor->is_active) {
            abort(404, 'Monitor inativo');
        }

        $settings = $monitor->getMergedSettings();
        $teamId = $request->query('team');
        $sectorIds = $monitor->getSectorIds();
        if (empty($sectorIds)) {
            $defaultSectorId = app(SectorService::class)->getDefaultSectorId();
            $sectorIds = $defaultSectorId ? [$defaultSectorId] : [];
        }

        // Se o monitor tem equipes configuradas, usar apenas essas
        $allowedTeamIds = null;
        $explicitTeamIds = $monitor->getAllowedTeamIds();
        if (!empty($explicitTeamIds)) {
            $allowedTeamIds = $explicitTeamIds;
            // Se uma equipe foi selecionada mas não está nas permitidas, usar null
            if ($teamId && !in_array($teamId, $allowedTeamIds)) {
                $teamId = null;
            }
        }

        $data = $this->buildDashboardData($teamId, $allowedTeamIds, $sectorIds);

        return response()->json([
            'activeTeamName' => $data['activeTeam']?->display_label,
            'rankingHtml' => view('dashboard.partials.ranking', [
                'ranking' => $data['ranking'],
                'activeTeam' => $data['activeTeam'],
            ])->render(),
            'podiumHtml' => view('dashboard.partials.podium', [
                'top3' => $data['top3'],
            ])->render(),
            'stats' => [
                'totalParticipants' => $data['stats']['totalParticipants'],
                'activeParticipants' => $data['stats']['activeParticipants'],
                'percentage' => $data['percentage'],
            ],
        ]);
    }

    /**
     * Constrói dados do dashboard (reutilizado do DashboardController)
     */
    private function buildDashboardData(?string $teamId, ?array $allowedTeamIds = null, array $sectorIds = []): array
    {
        // Filtrar equipes
        $teamsQuery = Team::orderBy('name');
        if (!empty($sectorIds)) {
            $teamsQuery->whereIn('sector_id', $sectorIds);
        }
        if ($allowedTeamIds !== null) {
            $teamsQuery->whereIn('id', $allowedTeamIds);
        }
        $teams = $teamsQuery->get(['id', 'name', 'display_name']);

        // Se a equipe solicitada não existe no escopo do monitor, desconsiderar
        if ($teamId && !$teams->contains('id', $teamId)) {
            $teamId = null;
        }

        $activeTeam = $teamId ? $teams->firstWhere('id', $teamId) : null;

        // Buscar temporada ativa
        $activeSeason = Season::where('is_active', true)->first();

        $sellersQuery = Seller::with(['teams', 'season']);
        if (!empty($sectorIds)) {
            $sellersQuery->whereIn('sector_id', $sectorIds);
        }
        
        // Filtrar por temporada ativa (apenas vendedores da temporada atual)
        if ($activeSeason) {
            $sellersQuery->where('season_id', $activeSeason->id);
        }
        
        // Filtrar por equipe selecionada ou equipes permitidas
        if ($teamId) {
            // Filtro por equipe específica
            if ($allowedTeamIds === null || in_array($teamId, $allowedTeamIds)) {
                $sellersQuery->whereHas('teams', function($query) use ($teamId) {
                    $query->where('teams.id', $teamId);
                });
            } else {
                $sellersQuery->whereRaw('1 = 0');
            }
        } elseif ($allowedTeamIds !== null) {
            // Geral: mostrar vendedores das equipes permitidas OU vendedores sem equipe
            $sellersQuery->where(function($query) use ($allowedTeamIds) {
                $query->whereHas('teams', function($q) use ($allowedTeamIds) {
                    $q->whereIn('teams.id', $allowedTeamIds);
                })->orWhereDoesntHave('teams');
            });
        }
        // Se $teamId é null e $allowedTeamIds é null, mostrar todos (sem filtro adicional)

        $sellers = $sellersQuery
            ->orderBy('points', 'desc')
            ->limit(100)
            ->get();

        $ranking = $sellers->map(function ($seller) {
            $gamification = $this->gamificationService->getGamificationInfo($seller->points);
            return [
                'id' => $seller->id,
                'name' => $seller->name,
                'email' => $seller->email,
                'profile_photo_path' => $seller->profile_photo_path,
                'points' => $seller->points,
                'status' => $seller->status,
                'level' => $gamification['level'],
                'badge' => $gamification['badge'],
                'progress' => $gamification['progress'],
                'position' => 0,
                'team' => $seller->teams->first()?->display_label,
                'season' => $seller->season?->name,
            ];
        })->values();

        $ranking = $ranking->map(function ($entry, $index) {
            $entry['position'] = $index + 1;
            return $entry;
        });

        $top3 = $ranking->take(3)->values();

        $stats = [
            'totalPoints' => $sellers->sum('points'),
            'totalParticipants' => $sellers->count(),
            'activeParticipants' => $sellers->where('status', 'active')->count(),
            'averagePoints' => $sellers->avg('points') ?? 0,
        ];

        $percentage = number_format((($stats['totalPoints'] ?? 0) / 500000) * 100, 2);

        return compact('teams', 'activeTeam', 'ranking', 'top3', 'stats', 'percentage');
    }

    /**
     * Normaliza configuração de eventos de notificação
     */
    private function normalizeNotificationEventsConfig(?string $json): array
    {
        $defaults = [
            'sale_registered' => [
                'system' => true,
                'email' => false,
                'sound' => true,
            ],
            'ranking_position_changed' => [
                'system' => true,
                'email' => false,
                'sound' => false,
            ],
            'entered_top_3' => [
                'system' => true,
                'email' => true,
                'sound' => true,
            ],
            'goal_reached' => [
                'system' => true,
                'email' => true,
                'sound' => true,
            ],
            'season_started' => [
                'system' => true,
                'email' => true,
                'sound' => false,
            ],
            'season_ended' => [
                'system' => true,
                'email' => true,
                'sound' => false,
            ],
        ];
        
        $decoded = $json ? json_decode($json, true) : null;

        if (!is_array($decoded)) {
            return $defaults;
        }

        $normalized = [];

        foreach ($defaults as $event => $channels) {
            $normalized[$event] = [];
            foreach ($channels as $channel => $defaultValue) {
                $normalized[$event][$channel] = isset($decoded[$event][$channel])
                    ? (bool) $decoded[$event][$channel]
                    : $defaultValue;
            }
        }

        return $normalized;
    }
}
