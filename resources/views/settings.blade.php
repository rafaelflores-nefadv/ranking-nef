@extends('layouts.app')

@section('title', 'Configurações')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Configurações</h1>
            <p class="text-slate-400">Gerencie as configurações do sistema</p>
        </div>
        @if(session('status'))
        <div class="mb-6 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm px-4 py-3 rounded-lg">
            {{ session('status') }}
        </div>
        @endif

        <div class="space-y-6">
            <div class="flex flex-wrap gap-2 bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-2">
                <button type="button" data-tab-button="gerais" class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors bg-blue-600 text-white">
                    Gerais
                </button>
                <button type="button" data-tab-button="temporadas" class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors text-slate-300 hover:text-white hover:bg-slate-800/60">
                    Temporadas
                </button>
                <button type="button" data-tab-button="regras" class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors text-slate-300 hover:text-white hover:bg-slate-800/60">
                    Regras
                </button>
                <button type="button" data-tab-button="notificacoes" class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors text-slate-300 hover:text-white hover:bg-slate-800/60">
                    Notificações
                </button>
            </div>

            <!-- Configurações Gerais -->
            <div data-tab="gerais" class="settings-tab bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
                <form action="{{ route('settings.general.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-bold text-white">Configurações Gerais</h2>
                            <p class="text-slate-400 text-sm">Ajustes principais do sistema.</p>
                        </div>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                            Salvar
                        </button>
                    </div>
                    <div class="space-y-4">
                        <label class="flex items-center justify-between gap-4 p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                            <div>
                                <p class="text-white font-medium">Processamento Automático</p>
                                <p class="text-slate-400 text-sm">Processa ocorrências automaticamente.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-slate-300 text-sm">
                                    {{ ($configs['auto_process_occurrences'] ?? 'false') === 'true' ? 'Ativado' : 'Desativado' }}
                                </span>
                                <input
                                    type="checkbox"
                                    name="auto_process_occurrences"
                                    value="1"
                                    class="h-5 w-5 accent-blue-600"
                                    {{ ($configs['auto_process_occurrences'] ?? 'false') === 'true' ? 'checked' : '' }}
                                />
                            </div>
                        </label>
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                                <p class="text-white font-medium mb-2">Precisão de Pontos</p>
                                <input
                                    type="number"
                                    name="points_precision"
                                    min="0"
                                    max="6"
                                    value="{{ $configs['points_precision'] ?? '2' }}"
                                    class="w-full bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                                />
                            </label>
                            <label class="block p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                                <p class="text-white font-medium mb-2">Limite do Ranking</p>
                                <input
                                    type="number"
                                    name="ranking_limit"
                                    min="1"
                                    max="1000"
                                    value="{{ $configs['ranking_limit'] ?? '100' }}"
                                    class="w-full bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                                />
                            </label>
                        </div>
                        <label class="block p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                            <p class="text-white font-medium mb-2">Termo para venda</p>
                            <input
                                type="text"
                                name="sale_term"
                                maxlength="40"
                                value="{{ $configs['sale_term'] ?? 'Venda' }}"
                                placeholder="Ex: Venda"
                                class="w-full bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                            />
                            <p class="text-slate-400 text-xs mt-2">Usado nos botões e notificações.</p>
                        </label>
                    </div>
                </form>
            </div>

            <!-- Temporadas -->
            <div data-tab="temporadas" class="settings-tab hidden bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
                <h2 class="text-xl font-bold text-white mb-4">Temporadas</h2>
                <div class="space-y-2">
                    @forelse($seasons as $season)
                    <div class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg">
                        <div>
                            <p class="text-white font-medium">{{ $season->name }}</p>
                            <p class="text-slate-400 text-sm">
                                {{ $season->starts_at->format('d/m/Y') }} - {{ $season->ends_at->format('d/m/Y') }}
                            </p>
                        </div>
                        @if($season->is_active)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-600/20 text-green-400">
                            Ativa
                        </span>
                        @endif
                    </div>
                    @empty
                    <p class="text-slate-400">Nenhuma temporada encontrada</p>
                    @endforelse
                </div>
                <div class="mt-6 pt-6 border-t border-slate-800/60">
                    <form action="{{ route('settings.seasons.options.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-white">Opções da temporada</h3>
                                <p class="text-slate-400 text-sm">Defina duração e renovação automática.</p>
                            </div>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                Salvar
                            </button>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                                <p class="text-white font-medium mb-2">Duração padrão (dias)</p>
                                <input
                                    type="number"
                                    name="season_duration_days"
                                    min="1"
                                    max="3650"
                                    value="{{ $configs['season_duration_days'] ?? '365' }}"
                                    class="w-full bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                                />
                            </label>
                            <label class="flex items-center justify-between gap-4 p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                                <div>
                                    <p class="text-white font-medium">Renovação automática</p>
                                    <p class="text-slate-400 text-sm">Cria nova temporada ao finalizar.</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-slate-300 text-sm">
                                        {{ ($configs['season_auto_renew'] ?? 'true') === 'true' ? 'Ativado' : 'Desativado' }}
                                    </span>
                                    <input
                                        type="checkbox"
                                        name="season_auto_renew"
                                        value="1"
                                        class="h-5 w-5 accent-blue-600"
                                        {{ ($configs['season_auto_renew'] ?? 'true') === 'true' ? 'checked' : '' }}
                                    />
                                </div>
                            </label>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Regras de Pontuação -->
            <div data-tab="regras" class="settings-tab hidden bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
                <h2 class="text-xl font-bold text-white mb-4">Regras de Pontuação</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-800/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Ocorrência</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Pontos</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Descrição</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Prioridade</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Ativo</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-400 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            @forelse($scoreRules as $rule)
                            <tr class="hover:bg-slate-800/30">
                                <form id="score-rule-{{ $rule->id }}" action="{{ route('settings.score-rules.update', $rule) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                </form>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input
                                        form="score-rule-{{ $rule->id }}"
                                        type="text"
                                        name="ocorrencia"
                                        value="{{ $rule->ocorrencia }}"
                                        class="w-40 bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input
                                        form="score-rule-{{ $rule->id }}"
                                        type="number"
                                        name="points"
                                        step="0.01"
                                        value="{{ $rule->points }}"
                                        class="w-24 bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                                    />
                                </td>
                                <td class="px-6 py-4">
                                    <input
                                        form="score-rule-{{ $rule->id }}"
                                        type="text"
                                        name="description"
                                        value="{{ $rule->description }}"
                                        placeholder="Descrição"
                                        class="w-full min-w-[220px] bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input
                                        form="score-rule-{{ $rule->id }}"
                                        type="number"
                                        name="priority"
                                        value="{{ $rule->priority }}"
                                        class="w-20 bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <label class="flex items-center gap-2 text-slate-300 text-sm">
                                        <input
                                            form="score-rule-{{ $rule->id }}"
                                            type="checkbox"
                                            name="is_active"
                                            value="1"
                                            class="h-4 w-4 accent-blue-600"
                                            {{ $rule->is_active ? 'checked' : '' }}
                                        />
                                        {{ $rule->is_active ? 'Sim' : 'Não' }}
                                    </label>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <button
                                        form="score-rule-{{ $rule->id }}"
                                        type="submit"
                                        class="px-3 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold"
                                    >
                                        Salvar
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                    Nenhuma regra encontrada
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notificações -->
            <div data-tab="notificacoes" class="settings-tab hidden bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
                <div class="space-y-6">
                    <form action="{{ route('settings.notifications.update') }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-xl font-bold text-white">Canais de notificação</h2>
                                <p class="text-slate-400 text-sm">Ative ou desative canais gerais.</p>
                            </div>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                Salvar
                            </button>
                        </div>
                        <label class="flex items-center justify-between gap-4 p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                            <div>
                                <p class="text-white font-medium">Notificações do sistema</p>
                                <p class="text-slate-400 text-sm">Exibe alertas no painel.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-slate-300 text-sm">
                                    {{ ($configs['notifications_system_enabled'] ?? 'true') === 'true' ? 'Ativado' : 'Desativado' }}
                                </span>
                                <input
                                    type="checkbox"
                                    name="notifications_system_enabled"
                                    value="1"
                                    class="h-5 w-5 accent-blue-600"
                                    {{ ($configs['notifications_system_enabled'] ?? 'true') === 'true' ? 'checked' : '' }}
                                />
                            </div>
                        </label>

                        <label class="flex items-center justify-between gap-4 p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                            <div>
                                <p class="text-white font-medium">Notificações por e-mail</p>
                                <p class="text-slate-400 text-sm">Envia alertas para os responsáveis.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-slate-300 text-sm">
                                    {{ ($configs['notifications_email_enabled'] ?? 'true') === 'true' ? 'Ativado' : 'Desativado' }}
                                </span>
                                <input
                                    type="checkbox"
                                    name="notifications_email_enabled"
                                    value="1"
                                    class="h-5 w-5 accent-blue-600"
                                    {{ ($configs['notifications_email_enabled'] ?? 'true') === 'true' ? 'checked' : '' }}
                                />
                            </div>
                        </label>

                        <label class="flex items-center justify-between gap-4 p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                            <div>
                                <p class="text-white font-medium">Som</p>
                                <p class="text-slate-400 text-sm">Reproduz sons em eventos importantes.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-slate-300 text-sm">
                                    {{ ($configs['notifications_sound_enabled'] ?? 'true') === 'true' ? 'Ativado' : 'Desativado' }}
                                </span>
                                <input
                                    type="checkbox"
                                    name="notifications_sound_enabled"
                                    value="1"
                                    class="h-5 w-5 accent-blue-600"
                                    {{ ($configs['notifications_sound_enabled'] ?? 'true') === 'true' ? 'checked' : '' }}
                                />
                            </div>
                        </label>
                    </form>

                    <form action="{{ route('settings.notifications.events.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-white">Eventos por canal</h3>
                                <p class="text-slate-400 text-sm">Defina quais eventos disparam em cada canal.</p>
                            </div>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                Salvar
                            </button>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-slate-700/60">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-800/60">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-slate-300 font-semibold">Evento</th>
                                        @foreach($notificationChannelsLabels as $channelKey => $channelLabel)
                                            <th class="px-4 py-3 text-center text-slate-300 font-semibold">{{ $channelLabel }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-700/50">
                                    @foreach($notificationEventsLabels as $eventKey => $eventLabel)
                                        <tr class="hover:bg-slate-800/40">
                                            <td class="px-4 py-3 text-slate-200">{{ $eventLabel }}</td>
                                            @foreach($notificationChannelsLabels as $channelKey => $channelLabel)
                                                <td class="px-4 py-3 text-center">
                                                    <input
                                                        type="checkbox"
                                                        name="notifications_events[{{ $eventKey }}][{{ $channelKey }}]"
                                                        value="1"
                                                        class="h-4 w-4 accent-blue-600"
                                                        {{ ($notificationEventsConfig[$eventKey][$channelKey] ?? false) ? 'checked' : '' }}
                                                    />
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <form action="{{ route('settings.notifications.voice.update') }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-white">Leitura por Voz do Ranking</h3>
                                <p class="text-slate-400 text-sm">Leitura periodica dos Top 3 do ranking.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" id="test-voice-btn" class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold">
                                    Testar
                                </button>
                                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                    Salvar
                                </button>
                            </div>
                        </div>
                        <label class="flex items-center justify-between gap-4 p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                            <div>
                                <p class="text-white font-medium">Ativar leitura por voz</p>
                                <p class="text-slate-400 text-sm">Dispara a leitura automaticamente via scheduler.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-slate-300 text-sm">
                                    {{ ($configs['notifications_voice_enabled'] ?? 'false') === 'true' ? 'Ativado' : 'Desativado' }}
                                </span>
                                <input
                                    type="checkbox"
                                    name="notifications_voice_enabled"
                                    value="1"
                                    class="h-5 w-5 accent-blue-600"
                                    {{ ($configs['notifications_voice_enabled'] ?? 'false') === 'true' ? 'checked' : '' }}
                                />
                            </div>
                        </label>
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                                <p class="text-white font-medium mb-2">Modo de voz</p>
                                <select
                                    name="notifications_voice_mode"
                                    class="w-full bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                                >
                                    @php $voiceMode = $configs['notifications_voice_mode'] ?? 'server'; @endphp
                                    <option value="server" {{ $voiceMode === 'server' ? 'selected' : '' }}>Servidor</option>
                                    <option value="browser" {{ $voiceMode === 'browser' ? 'selected' : '' }}>Navegador</option>
                                    <option value="both" {{ $voiceMode === 'both' ? 'selected' : '' }}>Servidor + Navegador</option>
                                </select>
                                <p class="text-slate-400 text-xs mt-2">No navegador usa SpeechSynthesis (Chrome/Edge).</p>
                            </label>
                            <label class="block p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                                <p class="text-white font-medium mb-2">Escopo</p>
                                <select
                                    name="notifications_voice_scope"
                                    class="w-full bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                                >
                                    @php $voiceScope = $configs['notifications_voice_scope'] ?? 'global'; @endphp
                                    <option value="global" {{ $voiceScope === 'global' ? 'selected' : '' }}>Geral</option>
                                    <option value="teams" {{ $voiceScope === 'teams' ? 'selected' : '' }}>Equipes</option>
                                    <option value="both" {{ $voiceScope === 'both' ? 'selected' : '' }}>Ambos</option>
                                </select>
                            </label>
                            <label class="block p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                                <p class="text-white font-medium mb-2">Intervalo (minutos)</p>
                                <input
                                    type="number"
                                    name="notifications_voice_interval_minutes"
                                    min="1"
                                    max="1440"
                                    value="{{ $configs['notifications_voice_interval_minutes'] ?? '15' }}"
                                    class="w-full bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                                />
                            </label>
                        </div>
                        <label class="block p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                            <p class="text-white font-medium mb-2">Voz utilizada</p>
                            <input
                                type="text"
                                name="notifications_voice_name"
                                maxlength="120"
                                value="{{ $configs['notifications_voice_name'] ?? '' }}"
                                placeholder="Ex: Microsoft Maria Desktop, Joana, pt-BR"
                                class="w-full bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                            />
                            <p class="text-slate-400 text-xs mt-2">Opcional. Depende das vozes instaladas no servidor.</p>
                        </label>
                        <label class="block p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                            <p class="text-white font-medium mb-2">Voz do navegador</p>
                            <select
                                id="voice-browser-select"
                                name="notifications_voice_browser_name"
                                class="w-full bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                            >
                                <option value="">Automatica (padrao do navegador)</option>
                            </select>
                            <p class="text-slate-400 text-xs mt-2">Disponivel apenas quando a pagina esta aberta.</p>
                        </label>
                        <label class="flex items-center gap-3 p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                            <input
                                type="checkbox"
                                name="notifications_voice_only_when_changed"
                                value="1"
                                class="h-4 w-4 accent-blue-600"
                                {{ ($configs['notifications_voice_only_when_changed'] ?? 'false') === 'true' ? 'checked' : '' }}
                            />
                            <span class="text-slate-200">Somente se houver mudanca no ranking</span>
                        </label>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        const buttons = Array.from(document.querySelectorAll('[data-tab-button]'));
        const tabs = Array.from(document.querySelectorAll('.settings-tab'));

        if (!buttons.length || !tabs.length) {
            return;
        }

        const setActive = (tabName) => {
            tabs.forEach((tab) => {
                const isActive = tab.getAttribute('data-tab') === tabName;
                tab.classList.toggle('hidden', !isActive);
            });

            buttons.forEach((button) => {
                const isActive = button.getAttribute('data-tab-button') === tabName;
                button.classList.toggle('bg-blue-600', isActive);
                button.classList.toggle('text-white', isActive);
                button.classList.toggle('text-slate-300', !isActive);
                button.classList.toggle('hover:text-white', !isActive);
                button.classList.toggle('hover:bg-slate-800/60', !isActive);
            });
        };

        buttons.forEach((button) => {
            button.addEventListener('click', () => setActive(button.getAttribute('data-tab-button')));
        });

        setActive('gerais');

        const voiceSelect = document.getElementById('voice-browser-select');
        if (voiceSelect && 'speechSynthesis' in window) {
            const savedVoice = @json($configs['notifications_voice_browser_name'] ?? '');

            const loadVoices = () => {
                const voices = window.speechSynthesis.getVoices();
                if (!voices.length) return;

                const currentValue = voiceSelect.value;
                voiceSelect.innerHTML = '<option value="">Automatica (padrao do navegador)</option>';

                voices.forEach((voice) => {
                    const option = document.createElement('option');
                    option.value = voice.name;
                    option.textContent = `${voice.name} (${voice.lang})`;
                    voiceSelect.appendChild(option);
                });

                const valueToSet = currentValue || savedVoice;
                if (valueToSet) {
                    voiceSelect.value = valueToSet;
                }
            };

            loadVoices();
            window.speechSynthesis.addEventListener('voiceschanged', loadVoices);
        }

        // Botão de teste de voz
        const testVoiceBtn = document.getElementById('test-voice-btn');
        if (testVoiceBtn) {
            testVoiceBtn.addEventListener('click', async () => {
                const btn = testVoiceBtn;
                const originalText = btn.textContent;
                
                btn.disabled = true;
                btn.textContent = 'Testando...';
                btn.classList.add('opacity-50', 'cursor-not-allowed');

                try {
                    const response = await fetch('{{ route("settings.notifications.voice.test") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        alert(data.error || 'Erro ao testar voz. Verifique se há dados no sistema.');
                        return;
                    }

                    // Se o modo for browser ou both, fala no navegador
                    if (['browser', 'both'].includes(data.mode) && 'speechSynthesis' in window) {
                        const voices = window.speechSynthesis.getVoices();
                        const voiceSelect = document.getElementById('voice-browser-select');
                        const selectedVoiceName = voiceSelect?.value || '';
                        
                        const utterance = new SpeechSynthesisUtterance(data.text);
                        
                        if (selectedVoiceName) {
                            const voice = voices.find(v => v.name === selectedVoiceName);
                            if (voice) {
                                utterance.voice = voice;
                            }
                        }

                        window.speechSynthesis.speak(utterance);
                    }
                } catch (error) {
                    console.error('Erro ao testar voz:', error);
                    alert('Erro ao testar voz. Verifique o console para mais detalhes.');
                } finally {
                    btn.disabled = false;
                    btn.textContent = originalText;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            });
        }
    })();
</script>
@endsection
