<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Season;
use App\Models\ScoreRule;
use App\Models\Seller;
use App\Models\Team;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

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
        $scoreRules = ScoreRule::orderBy('priority')->orderBy('ocorrencia')->get();

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
            'customSounds'
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
            $globalTop = $this->getTopSellers($season->id, null);
            if ($globalTop->isNotEmpty()) {
                $texts[] = $this->buildRankingText('Top 3 do ranking geral:', $globalTop->all(), $precision);
            }
        }

        // Busca dados do ranking por equipes se o escopo incluir teams
        if (in_array($scope, ['teams', 'both'], true)) {
            $teams = Team::orderBy('name')->get(['id', 'name']);
            foreach ($teams as $team) {
                $teamTop = $this->getTopSellers($season->id, $team->id);
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

    private function getTopSellers(string $seasonId, ?string $teamId)
    {
        $query = Seller::query()
            ->where('season_id', $seasonId)
            ->orderBy('points', 'desc')
            ->limit(3);

        if ($teamId) {
            $query->where('team_id', $teamId);
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

        $this->authorize('create', ScoreRule::class);

        $validated = $request->validate([
            'ocorrencia' => 'required|string',
            'points' => 'required|numeric',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        ScoreRule::create([
            'ocorrencia' => $validated['ocorrencia'],
            'points' => $validated['points'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'] ?? 0,
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

        $this->authorize('update', $scoreRule);

        $validated = $request->validate([
            'ocorrencia' => 'required|string',
            'points' => 'required|numeric',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $scoreRule->update([
            'ocorrencia' => $validated['ocorrencia'],
            'points' => $validated['points'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'] ?? null,
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
            'season_duration_days' => 'required|integer|min:1|max:3650',
            'season_auto_renew' => 'nullable|boolean',
        ]);

        $configs = [
            'season_duration_days' => (string) $validated['season_duration_days'],
            'season_auto_renew' => $request->boolean('season_auto_renew') ? 'true' : 'false',
        ];

        foreach ($configs as $key => $value) {
            Config::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
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
}
