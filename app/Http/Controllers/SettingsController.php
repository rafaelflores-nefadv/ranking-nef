<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Season;
use App\Models\ScoreRule;
use App\Models\Seller;
use App\Models\Team;
use App\Services\SectorService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);

        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);
        $configs = Config::all()->pluck('value', 'key');
        $notificationEventsConfig = $this->normalizeNotificationEventsConfig(
            $configs['notifications_events_config'] ?? null
        );
        $notificationEventsLabels = [
            'sale_registered' => 'Venda registrada',
            'ranking_position_changed' => 'Mudanca de posicao no ranking',
            'entered_top_3' => 'Entrada no Top 3',
            'goal_reached' => 'Meta atingida',
            'season_started' => 'Inicio de temporada',
            'season_ended' => 'Fim de temporada',
        ];
        $notificationChannelsLabels = [
            'system' => 'Sistema',
            'email' => 'Email',
            'sound' => 'Som',
        ];
        $seasons = Season::all();
        $scoreRules = ScoreRule::where('sector_id', $sectorId)
            ->orderBy('ocorrencia')
            ->get();

        // Carregar permissões do supervisor
        $supervisorPermissions = PermissionService::getSupervisorPermissions();
        $availableModules = PermissionService::getAvailableModules();
        $availableActions = PermissionService::getAvailableActions();

        // Carregar sons personalizados com URLs completas
        $customSoundsPaths = json_decode($configs['notifications_custom_sounds'] ?? '{}', true) ?: [];
        $customSounds = [];
        foreach ($customSoundsPaths as $eventKey => $filePath) {
            $customSounds[$eventKey] = asset('storage/' . $filePath);
        }

        return view('settings', compact(
            'configs',
            'seasons',
            'scoreRules',
            'notificationEventsConfig',
            'notificationEventsLabels',
            'notificationChannelsLabels',
            'customSounds',
            'supervisorPermissions',
            'availableModules',
            'availableActions'
        ));
    }

    public function updateNotifications(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $validated = $request->validate([
            'notifications_system_enabled' => 'nullable|boolean',
            'notifications_email_enabled' => 'nullable|boolean',
            'notifications_sound_enabled' => 'nullable|boolean',
            'notifications_popup_max_count' => 'nullable|integer|min:1|max:10',
            'notifications_popup_auto_close_seconds' => 'nullable|integer|min:1|max:60',
        ]);

        $notificationKeys = [
            'notifications_system_enabled',
            'notifications_email_enabled',
            'notifications_sound_enabled',
        ];

        foreach ($notificationKeys as $key) {
            $value = $request->boolean($key) ? 'true' : 'false';

            Config::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Salvar configurações de popups
        if ($request->has('notifications_popup_max_count')) {
            Config::updateOrCreate(
                ['key' => 'notifications_popup_max_count'],
                ['value' => (string) $request->input('notifications_popup_max_count', 2)]
            );
        }

        if ($request->has('notifications_popup_auto_close_seconds')) {
            Config::updateOrCreate(
                ['key' => 'notifications_popup_auto_close_seconds'],
                ['value' => (string) $request->input('notifications_popup_auto_close_seconds', 7)]
            );
        }

        return redirect()
            ->route('settings')
            ->with('status', 'Notificações atualizadas com sucesso!');
    }

    public function updateNotificationEvents(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $submitted = $request->input('notifications_events', []);
        $eventsConfig = [];

        foreach ($this->defaultNotificationEventsConfig() as $event => $channels) {
            $eventsConfig[$event] = [];
            foreach (array_keys($channels) as $channel) {
                $eventsConfig[$event][$channel] = !empty($submitted[$event][$channel]);
            }
        }

        Config::updateOrCreate(
            ['key' => 'notifications_events_config'],
            ['value' => json_encode($eventsConfig)]
        );

        return redirect()
            ->route('settings')
            ->with('status', 'Eventos de notificacao atualizados com sucesso!');
    }

    public function updateVoiceSettings(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $validated = $request->validate([
            'notifications_voice_enabled' => 'nullable|boolean',
            'notifications_voice_mode' => 'required|in:server,browser,both',
            'notifications_voice_scope' => 'required|in:global,teams,both',
            'notifications_voice_interval_minutes' => 'required|integer|min:1|max:1440',
            'notifications_voice_only_when_changed' => 'nullable|boolean',
            'notifications_voice_name' => 'nullable|string|max:120',
            'notifications_voice_browser_name' => 'nullable|string|max:120',
        ]);

        $configs = [
            'notifications_voice_enabled' => $request->boolean('notifications_voice_enabled') ? 'true' : 'false',
            'notifications_voice_mode' => $validated['notifications_voice_mode'],
            'notifications_voice_scope' => $validated['notifications_voice_scope'],
            'notifications_voice_interval_minutes' => (string) $validated['notifications_voice_interval_minutes'],
            'notifications_voice_only_when_changed' => $request->boolean('notifications_voice_only_when_changed') ? 'true' : 'false',
            'notifications_voice_name' => trim((string) ($validated['notifications_voice_name'] ?? '')),
            'notifications_voice_browser_name' => trim((string) ($validated['notifications_voice_browser_name'] ?? '')),
        ];

        foreach ($configs as $key => $value) {
            Config::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()
            ->route('settings')
            ->with('status', 'Leitura por voz atualizada com sucesso!');
    }

    public function updateSoundSettings(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $submitted = $request->input('notifications_sounds', []);
        $soundsFiles = $request->file('notifications_sounds_file', []);
        $soundsConfig = [];
        $customSounds = json_decode(Config::where('key', 'notifications_custom_sounds')->value('value') ?? '{}', true) ?: [];

        // Processar uploads de arquivos
        foreach ($soundsFiles as $eventKey => $file) {
            if ($file && $file->isValid()) {
                // Validar tipo de arquivo
                $allowedMimes = ['audio/mpeg', 'audio/mp3', 'audio/mpeg3'];
                $allowedExtensions = ['mp3'];
                
                $mimeType = $file->getMimeType();
                $extension = strtolower($file->getClientOriginalExtension());
                
                if (!in_array($mimeType, $allowedMimes) && !in_array($extension, $allowedExtensions)) {
                    continue;
                }

                // Criar diretório se não existir
                $soundDir = storage_path('app/public/sounds');
                if (!is_dir($soundDir)) {
                    mkdir($soundDir, 0755, true);
                }

                // Remover arquivo antigo se existir
                if (isset($customSounds[$eventKey])) {
                    $oldFile = storage_path('app/public/' . $customSounds[$eventKey]);
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                // Salvar novo arquivo
                $filename = $eventKey . '_' . time() . '.' . $extension;
                $file->move($soundDir, $filename);
                $customSounds[$eventKey] = 'sounds/' . $filename;
            }
        }

        // Processar configurações de sons
        foreach ($this->defaultNotificationEventsConfig() as $event => $channels) {
            if (isset($submitted[$event])) {
                $soundsConfig[$event] = $submitted[$event];
                
                // Se não for custom e houver arquivo customizado, remover
                if ($soundsConfig[$event] !== 'custom' && isset($customSounds[$event])) {
                    $oldFile = storage_path('app/public/' . $customSounds[$event]);
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                    unset($customSounds[$event]);
                }
            }
        }

        Config::updateOrCreate(
            ['key' => 'notifications_sounds_config'],
            ['value' => json_encode($soundsConfig)]
        );

        Config::updateOrCreate(
            ['key' => 'notifications_custom_sounds'],
            ['value' => json_encode($customSounds)]
        );

        return redirect()
            ->route('settings')
            ->with('status', 'Configurações de sons atualizadas com sucesso!');
    }

    public function removeCustomSound(Request $request, string $eventKey)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $customSounds = json_decode(Config::where('key', 'notifications_custom_sounds')->value('value') ?? '{}', true) ?: [];

        if (isset($customSounds[$eventKey])) {
            $filePath = storage_path('app/public/' . $customSounds[$eventKey]);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            unset($customSounds[$eventKey]);

            Config::updateOrCreate(
                ['key' => 'notifications_custom_sounds'],
                ['value' => json_encode($customSounds)]
            );

            // Atualizar configuração de som para padrão se estava usando custom
            $soundsConfig = json_decode(Config::where('key', 'notifications_sounds_config')->value('value') ?? '{}', true) ?: [];
            if (isset($soundsConfig[$eventKey]) && $soundsConfig[$eventKey] === 'custom') {
                $soundsConfig[$eventKey] = 'notification';
                Config::updateOrCreate(
                    ['key' => 'notifications_sounds_config'],
                    ['value' => json_encode($soundsConfig)]
                );
            }
        }

        return redirect()
            ->route('settings')
            ->with('status', 'Som personalizado removido com sucesso!');
    }

    public function testVoice(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $voiceMode = Config::where('key', 'notifications_voice_mode')->value('value') ?? 'server';
        $voiceMode = $voiceMode ?: 'server';
        $scope = Config::where('key', 'notifications_voice_scope')->value('value') ?? 'global';
        $precision = (int) (Config::where('key', 'points_precision')->value('value') ?? 2);
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);

        // Busca temporada ativa
        $season = Season::where('is_active', true)->first();
        
        if (!$season) {
            return response()->json([
                'success' => false,
                'error' => 'Nenhuma temporada ativa encontrada.',
            ], 400);
        }

        $texts = [];

        // Busca dados do ranking geral se o escopo incluir global
        if (in_array($scope, ['global', 'both'], true)) {
            $globalTop = $this->getTopSellers($season->id, null, $sectorId);
            if ($globalTop->isNotEmpty()) {
                $texts[] = $this->buildRankingText('Top 3 do ranking geral:', $globalTop->all(), $precision);
            }
        }

        // Busca dados do ranking por equipes se o escopo incluir teams
        if (in_array($scope, ['teams', 'both'], true)) {
            $teams = Team::where('sector_id', $sectorId)->orderBy('name')->get(['id', 'name']);
            foreach ($teams as $team) {
                $teamTop = $this->getTopSellers($season->id, $team->id, $sectorId);
                if ($teamTop->isNotEmpty()) {
                    $texts[] = $this->buildRankingText("Top 3 da equipe {$team->name}:", $teamTop->all(), $precision);
                }
            }
        }

        if (empty($texts)) {
            return response()->json([
                'success' => false,
                'error' => 'Nenhum dado de ranking encontrado para teste.',
            ], 400);
        }

        $testText = implode(' ', $texts);

        // Se for modo servidor ou both, executa no servidor
        if (in_array($voiceMode, ['server', 'both'], true)) {
            $textToSpeechService = app(\App\Services\TextToSpeechService::class);
            $textToSpeechService->speak($testText);
        }

        // Retorna o texto para o browser também (se for browser ou both)
        return response()->json([
            'success' => true,
            'text' => $testText,
            'mode' => $voiceMode,
        ]);
    }

    private function getTopSellers(string $seasonId, ?string $teamId, ?string $sectorId)
    {
        $query = Seller::query()
            ->where('season_id', $seasonId)
            ->where('sector_id', $sectorId)
            ->orderBy('points', 'desc')
            ->limit(3);

        if ($teamId) {
            $query->whereHas('teams', function ($q) use ($teamId) {
                $q->where('teams.id', $teamId);
            });
        }

        return $query->get(['id', 'name', 'points']);
    }

    private function buildRankingText(string $title, array $entries, int $precision): string
    {
        $parts = [$title];

        foreach ($entries as $index => $seller) {
            $position = $index + 1;
            $points = number_format((float) $seller->points, $precision, ',', '.');
            $parts[] = "{$position}o lugar: {$seller->name}, {$points} pontos.";
        }

        return implode(' ', $parts);
    }

    public function storeScoreRule(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);

        $this->authorize('create', ScoreRule::class);

        $validated = $request->validate([
            'ocorrencia' => 'required|string',
            'points' => 'required|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        ScoreRule::create([
            'sector_id' => $sectorId,
            'ocorrencia' => $validated['ocorrencia'],
            'points' => $validated['points'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('settings')
            ->with('status', 'Regra de pontuação criada com sucesso!');
    }

    public function updateScoreRule(Request $request, ScoreRule $scoreRule)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);

        $this->authorize('update', $scoreRule);

        $validated = $request->validate([
            'ocorrencia' => 'required|string',
            'points' => 'required|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        if ($scoreRule->sector_id !== $sectorId) {
            abort(403, 'Acesso negado');
        }

        $scoreRule->update([
            'ocorrencia' => $validated['ocorrencia'],
            'points' => $validated['points'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('settings')
            ->with('status', 'Regra de pontuação atualizada com sucesso!');
    }

    public function destroyScoreRule(Request $request, string $scoreRule)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $scoreRuleModel = ScoreRule::findOrFail($scoreRule);
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);
        if ($scoreRuleModel->sector_id !== $sectorId) {
            abort(403, 'Acesso negado');
        }
        $this->authorize('delete', $scoreRuleModel);

        $scoreRuleModel->delete();

        return redirect()
            ->route('settings')
            ->with('status', 'Regra de pontuação excluída com sucesso!');
    }

    public function updateGeneral(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $validated = $request->validate([
            'auto_process_occurrences' => 'nullable|boolean',
            'points_precision' => 'required|integer|min:0|max:6',
            'ranking_limit' => 'required|integer|min:1|max:1000',
            'sale_term' => 'nullable|string|max:40',
        ]);

        $configs = [
            'auto_process_occurrences' => $request->boolean('auto_process_occurrences') ? 'true' : 'false',
            'points_precision' => (string) $validated['points_precision'],
            'ranking_limit' => (string) $validated['ranking_limit'],
            'sale_term' => trim($validated['sale_term'] ?? '') ?: 'Venda',
        ];

        foreach ($configs as $key => $value) {
            Config::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()
            ->route('settings')
            ->with('status', 'Configurações gerais atualizadas com sucesso!');
    }

    public function updateSeasonOptions(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $validated = $request->validate([
            'season_recurrence_type' => 'required|in:daily,weekly,monthly,bimonthly,quarterly,semiannual,annual,fixed_date,days',
            'season_fixed_end_date' => 'nullable|date|required_if:season_recurrence_type,fixed_date',
            'season_duration_days' => 'nullable|integer|min:1|max:3650|required_if:season_recurrence_type,days',
            'season_auto_renew' => 'nullable|boolean',
        ]);

        $configs = [
            'season_recurrence_type' => $validated['season_recurrence_type'],
            'season_fixed_end_date' => $validated['season_fixed_end_date'] ?? null,
            'season_duration_days' => isset($validated['season_duration_days']) ? (string) $validated['season_duration_days'] : null,
            'season_auto_renew' => $request->boolean('season_auto_renew') ? 'true' : 'false',
        ];

        foreach ($configs as $key => $value) {
            if ($value !== null) {
                Config::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        }

        // Atualizar temporada ativa se existir, baseado nas novas configurações
        $activeSeason = Season::where('is_active', true)->first();
        if ($activeSeason) {
            // Usar a data de início atual da temporada para recalcular
            // O método calculateDatesByRecurrence já ajusta o início para o período correto
            // (ex: início do mês para mensal, início do ano para anual, etc.)
            $dates = Season::calculateDatesByRecurrence(
                $validated['season_recurrence_type'],
                $activeSeason->starts_at,
                $validated['season_fixed_end_date'] ?? null,
                isset($validated['season_duration_days']) ? (int) $validated['season_duration_days'] : null
            );

            $activeSeason->update([
                'starts_at' => $dates['starts_at'],
                'ends_at' => $dates['ends_at'],
                'recurrence_type' => $validated['season_recurrence_type'],
                'fixed_end_date' => $validated['season_fixed_end_date'] ? \Carbon\Carbon::parse($validated['season_fixed_end_date']) : null,
                'duration_days' => isset($validated['season_duration_days']) ? (int) $validated['season_duration_days'] : null,
            ]);
        }

        return redirect()
            ->route('settings')
            ->with('status', 'Opções de temporada atualizadas com sucesso!');
    }

    private function defaultNotificationEventsConfig(): array
    {
        return [
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
    }

    private function normalizeNotificationEventsConfig(?string $json): array
    {
        $defaults = $this->defaultNotificationEventsConfig();
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

    public function indexPermissions(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $permissions = PermissionService::getSupervisorPermissions();
        $modules = PermissionService::getAvailableModules();
        $actions = PermissionService::getAvailableActions();

        return compact('permissions', 'modules', 'actions');
    }

    public function updatePermissions(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'array',
            'permissions.*.*' => 'string|in:view,create,edit,delete,toggle',
        ]);

        try {
            PermissionService::setSupervisorPermissions($validated['permissions']);

            return redirect()
                ->route('settings')
                ->with('status', 'Permissões do Supervisor atualizadas com sucesso!');
        } catch (\Exception $e) {
            return redirect()
                ->route('settings')
                ->withErrors(['error' => 'Erro ao salvar permissões: ' . $e->getMessage()]);
        }
    }

    public function updateTheme(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $validated = $request->validate([
            'monitor_theme' => 'required|string|max:50',
        ]);

        // Verificar se o tema existe
        $themePath = resource_path("views/monitors/themes/{$validated['monitor_theme']}");
        $dashboardPath = $themePath . '/dashboard.blade.php';
        $layoutPath = $themePath . '/layout.blade.php';
        
        if (!is_dir($themePath) || !file_exists($dashboardPath) || !file_exists($layoutPath)) {
            return redirect()
                ->route('settings')
                ->withErrors(['error' => 'Tema selecionado não existe ou está incompleto.']);
        }

        Config::updateOrCreate(
            ['key' => 'monitor_theme'],
            ['value' => $validated['monitor_theme']]
        );

        return redirect()
            ->route('settings')
            ->with('status', 'Tema do monitor atualizado com sucesso!');
    }

    public function previewTheme(Request $request, string $theme)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        // Verificar se o tema existe
        $themePath = resource_path("views/monitors/themes/{$theme}");
        $dashboardPath = $themePath . '/dashboard.blade.php';
        $layoutPath = $themePath . '/layout.blade.php';
        
        if (!is_dir($themePath) || !file_exists($dashboardPath) || !file_exists($layoutPath)) {
            abort(404, 'Tema não encontrado');
        }

        // Buscar um monitor ativo para preview, ou criar um monitor fictício
        $monitor = \App\Models\Monitor::where('is_active', true)
            ->where('sector_id', $sectorId)
            ->first();
        
        if (!$monitor) {
            // Criar monitor fictício para preview
            $monitor = new \App\Models\Monitor();
            $monitor->id = '00000000-0000-0000-0000-000000000000';
            $monitor->name = 'Preview do Tema';
            $monitor->slug = 'preview';
            $monitor->is_active = true;
            $monitor->settings = [];
            $monitor->sector_id = $sectorId;
        }

        $settings = $monitor->getMergedSettings();
        
        // Usar GamificationService para construir dados
        $gamificationService = app(\App\Services\GamificationService::class);
        
        // Obter dados do dashboard (sem filtro de usuário - público)
        $teams = Team::where('sector_id', $sectorId)->orderBy('name')->get(['id', 'name']);
        $activeTeam = null;

        // Buscar temporada ativa para preview
        $activeSeason = Season::where('is_active', true)->first();

        $sellersQuery = Seller::with(['teams', 'season'])->where('sector_id', $sectorId);
        
        // Filtrar por temporada ativa (apenas vendedores da temporada atual)
        if ($activeSeason) {
            $sellersQuery->where('season_id', $activeSeason->id);
        }
        
        $sellers = $sellersQuery
            ->orderBy('points', 'desc')
            ->limit(100)
            ->get();

        $ranking = $sellers->map(function ($seller) use ($gamificationService) {
            $gamification = $gamificationService->getGamificationInfo($seller->points);
            return [
                'id' => $seller->id,
                'name' => $seller->name,
                'email' => $seller->email,
                'points' => $seller->points,
                'level' => $gamification['level'],
                'badge' => $gamification['badge'],
                'progress' => $gamification['progress'],
                'position' => 0,
                'team' => $seller->team?->name,
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

        $configs = Config::all()->pluck('value', 'key');
        
        // Normalizar configuração de eventos de notificação
        $notificationEventsConfig = $this->normalizeNotificationEventsConfig(
            $configs['notifications_events_config'] ?? null
        );

        // Preparar configuração JavaScript do monitor
        $voiceEnabledValue = $settings['voice_enabled'] ?? false;
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
            'teams' => $settings['teams'] ?? [],
            'notifications_enabled' => (bool)($settings['notifications_enabled'] ?? false),
            'sound_enabled' => (bool)($settings['sound_enabled'] ?? false),
            'voice_enabled' => $voiceEnabledValue,
            'font_scale' => $settings['font_scale'] ?? 1.0,
            'monitor_slug' => $monitor->slug,
        ];

        // Forçar o tema selecionado para preview
        $themeName = $theme;
        
        return view("monitors.themes.{$themeName}.dashboard", [
            'teams' => $teams,
            'activeTeam' => $activeTeam,
            'ranking' => $ranking,
            'top3' => $top3,
            'stats' => $stats,
            'percentage' => $percentage,
            'configs' => $configs,
            'notificationEventsConfig' => $notificationEventsConfig,
            'monitor' => $monitor,
            'dashboardConfig' => $dashboardConfig,
        ]);
    }
}
