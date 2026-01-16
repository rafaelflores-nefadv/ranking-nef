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
                <button type="button" data-tab-button="usuarios" class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors text-slate-300 hover:text-white hover:bg-slate-800/60">
                    Usuários
                </button>
                @if(($configs['notifications_sound_enabled'] ?? 'true') === 'true')
                <button type="button" data-tab-button="sons" class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors text-slate-300 hover:text-white hover:bg-slate-800/60">
                    Sons
                </button>
                @endif
            </div>

            <!-- Configurações Gerais -->
            <div data-tab="gerais" class="settings-tab bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
                <form action="{{ route('settings.general.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <h2 class="text-xl font-bold text-white">Configurações Gerais</h2>
                        <p class="text-slate-400 text-sm">Ajustes principais do sistema.</p>
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
                    <div class="mt-6 pt-6 border-t border-slate-800/60 flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                            Salvar
                        </button>
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
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-white">Opções da temporada</h3>
                            <p class="text-slate-400 text-sm">Defina duração e renovação automática.</p>
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
                        <div class="mt-6 pt-6 border-t border-slate-800/60 flex justify-end">
                            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Regras de Pontuação -->
            <div data-tab="regras" class="settings-tab hidden bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-white">Regras de Pontuação</h2>
                </div>
                
                <!-- Formulário para adicionar nova regra -->
                <form action="{{ route('settings.score-rules.store') }}" method="POST" class="mb-6 p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                    @csrf
                    <div class="flex items-center gap-4 flex-wrap">
                        <div class="flex-1 min-w-[120px]">
                            <label class="block text-slate-300 text-xs mb-1">Ocorrência</label>
                            <input
                                type="text"
                                name="ocorrencia"
                                required
                                placeholder="Ex: venda"
                                class="w-full bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                            />
                        </div>
                        <div class="w-24">
                            <label class="block text-slate-300 text-xs mb-1">Pontos</label>
                            <input
                                type="number"
                                name="points"
                                step="0.01"
                                required
                                placeholder="0.00"
                                class="w-full bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                            />
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-slate-300 text-xs mb-1">Descrição</label>
                            <input
                                type="text"
                                name="description"
                                placeholder="Descrição da regra"
                                class="w-full bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                            />
                        </div>
                        <div class="w-20">
                            <label class="block text-slate-300 text-xs mb-1">Prioridade</label>
                            <input
                                type="number"
                                name="priority"
                                value="0"
                                class="w-full bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                            />
                        </div>
                        <div class="flex items-end gap-3">
                            <label class="flex items-center gap-2 text-slate-300 text-sm">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    checked
                                    class="h-4 w-4 accent-blue-600"
                                />
                                <span class="text-xs">Ativo</span>
                            </label>
                            <button
                                type="submit"
                                class="px-4 py-2 rounded-md bg-green-600 hover:bg-green-700 text-white text-sm font-semibold whitespace-nowrap"
                            >
                                Adicionar Regra
                            </button>
                        </div>
                    </div>
                </form>

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
                                    <div class="flex items-center gap-2 justify-end">
                                        <button
                                            form="score-rule-{{ $rule->id }}"
                                            type="submit"
                                            class="px-3 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold"
                                        >
                                            Salvar
                                        </button>
                                        <button
                                            type="button"
                                            class="px-3 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-xs font-semibold delete-rule-btn"
                                            data-rule-id="{{ $rule->id }}"
                                            data-rule-name="{{ $rule->ocorrencia }}"
                                        >
                                            Excluir
                                        </button>
                                    </div>
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
                        <div class="mb-4">
                            <h2 class="text-xl font-bold text-white">Canais de notificação</h2>
                            <p class="text-slate-400 text-sm">Ative ou desative canais gerais.</p>
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
                        <div class="mt-6 pt-6 border-t border-slate-800/60 flex justify-end">
                            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                Salvar
                            </button>
                        </div>
                    </form>

                    <form action="{{ route('settings.notifications.events.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-white">Eventos por canal</h3>
                            <p class="text-slate-400 text-sm">Defina quais eventos disparam em cada canal.</p>
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
                        <div class="mt-6 pt-6 border-t border-slate-800/60 flex justify-end">
                            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                Salvar
                            </button>
                        </div>
                    </form>

                    <form action="{{ route('settings.notifications.voice.update') }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-white">Leitura por Voz do Ranking</h3>
                            <p class="text-slate-400 text-sm">Leitura periódica dos Top 3 do ranking.</p>
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
                                <option value="">Automática (padrão do navegador)</option>
                            </select>
                            <p class="text-slate-400 text-xs mt-2">Disponível apenas quando a página está aberta.</p>
                        </label>
                        <label class="flex items-center gap-3 p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                            <input
                                type="checkbox"
                                name="notifications_voice_only_when_changed"
                                value="1"
                                class="h-4 w-4 accent-blue-600"
                                {{ ($configs['notifications_voice_only_when_changed'] ?? 'false') === 'true' ? 'checked' : '' }}
                            />
                            <span class="text-slate-200">Somente se houver mudança no ranking</span>
                        </label>
                        <div class="mt-6 pt-6 border-t border-slate-800/60 flex justify-end gap-2">
                            <button type="button" id="test-voice-btn" class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold">
                                Testar
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Usuários -->
            <div data-tab="usuarios" class="settings-tab hidden bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-white">Usuários do Sistema</h2>
                    <p class="text-slate-400 text-sm">Gerencie os usuários que podem acessar o sistema (Administradores e Supervisores).</p>
                </div>
                <div class="mb-4">
                    <a href="{{ route('users.index') }}" class="inline-block px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                        Gerenciar Usuários
                    </a>
                </div>
                <div class="bg-slate-800/50 rounded-lg p-4 border border-slate-700/60">
                    <p class="text-slate-300 text-sm">
                        <strong class="text-white">Administradores:</strong> Acesso completo ao sistema, incluindo todas as configurações.<br>
                        <strong class="text-white">Supervisores:</strong> Podem gerenciar colaboradores e equipes, mas não têm acesso às configurações do sistema.
                    </p>
                </div>
            </div>

            <!-- Sons -->
            @if(($configs['notifications_sound_enabled'] ?? 'true') === 'true')
            <div data-tab="sons" class="settings-tab hidden bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
                <form action="{{ route('settings.notifications.sounds.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <h2 class="text-xl font-bold text-white">Personalização de Sons</h2>
                        <p class="text-slate-400 text-sm">Configure o som para cada tipo de notificação.</p>
                    </div>
                    <div class="space-y-4">
                        @php
                            $soundConfig = json_decode($configs['notifications_sounds_config'] ?? '{}', true) ?: [];
                            $customSoundsPaths = json_decode($configs['notifications_custom_sounds'] ?? '{}', true) ?: [];
                            $defaultSounds = [
                                'sale_registered' => 'notification',
                                'ranking_position_changed' => 'notification',
                                'entered_top_3' => 'success',
                                'goal_reached' => 'success',
                                'season_started' => 'notification',
                                'season_ended' => 'notification',
                            ];
                            $availableSounds = [
                                'notification' => 'Notificação (padrão)',
                                'success' => 'Sucesso',
                                'error' => 'Erro',
                                'warning' => 'Aviso',
                                'info' => 'Informação',
                                'custom' => 'Arquivo personalizado',
                            ];
                        @endphp
                        @foreach($notificationEventsLabels as $eventKey => $eventLabel)
                            <div class="p-4 rounded-lg bg-slate-800/50 border border-slate-700/60">
                                <p class="text-white font-medium mb-3">{{ $eventLabel }}</p>
                                <div class="space-y-3">
                                    <select
                                        name="notifications_sounds[{{ $eventKey }}]"
                                        class="w-full bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm sound-select"
                                        data-event="{{ $eventKey }}"
                                    >
                                        @foreach($availableSounds as $soundKey => $soundLabel)
                                            @php
                                                $selectedSound = $soundConfig[$eventKey] ?? $defaultSounds[$eventKey] ?? 'notification';
                                                $hasCustom = isset($customSoundsPaths[$eventKey]);
                                                if ($hasCustom && $selectedSound !== 'custom') {
                                                    $selectedSound = 'custom';
                                                }
                                            @endphp
                                            <option value="{{ $soundKey }}" {{ $selectedSound === $soundKey ? 'selected' : '' }}>
                                                {{ $soundLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    
                                    <div class="sound-upload-container" data-event="{{ $eventKey }}" style="{{ ($selectedSound === 'custom' || isset($customSoundsPaths[$eventKey])) ? '' : 'display: none;' }}">
                                        <label class="block text-sm text-slate-300 mb-1">Arquivo de som (MP3)</label>
                                        <div class="flex items-center gap-2">
                                            <input
                                                type="file"
                                                name="notifications_sounds_file[{{ $eventKey }}]"
                                                accept="audio/mpeg,audio/mp3,.mp3"
                                                class="block w-full text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 file:cursor-pointer bg-slate-900/60 border border-slate-700 rounded-md"
                                            />
                                            @if(isset($customSoundsPaths[$eventKey]))
                                                <span class="text-xs text-green-400">✓ Arquivo carregado</span>
                                                <button
                                                    type="button"
                                                    class="px-2 py-1 text-xs rounded-md bg-red-600 hover:bg-red-700 text-white remove-sound-btn"
                                                    data-event="{{ $eventKey }}"
                                                >
                                                    Remover
                                                </button>
                                            @endif
                                        </div>
                                        @if(isset($customSoundsPaths[$eventKey]))
                                            <p class="text-xs text-slate-400 mt-1">Arquivo atual: {{ basename($customSoundsPaths[$eventKey]) }}</p>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            class="px-3 py-1 text-xs rounded-md bg-slate-700 hover:bg-slate-600 text-white test-sound-btn"
                                            data-sound="{{ $selectedSound }}"
                                            data-event="{{ $eventKey }}"
                                            data-custom-sound="{{ $customSounds[$eventKey] ?? '' }}"
                                        >
                                            Testar Som
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6 pt-6 border-t border-slate-800/60 flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
            @endif
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

            // Reinicializar botões de som quando a guia Sons for ativada
            if (tabName === 'sons') {
                setTimeout(updateSoundButtons, 100);
            }
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
                voiceSelect.innerHTML = '<option value="">Automática (padrão do navegador)</option>';

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
                        showCustomAlert('Erro', data.error || 'Erro ao testar voz. Verifique se há dados no sistema.', 'error');
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
                    showCustomAlert('Erro', 'Erro ao testar voz. Verifique o console para mais detalhes.', 'error');
                } finally {
                    btn.disabled = false;
                    btn.textContent = originalText;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            });
        }

        // Funcionalidade de sons
        const updateSoundButtons = () => {
            const soundSelects = document.querySelectorAll('.sound-select');
            const testSoundBtns = document.querySelectorAll('.test-sound-btn');
            const removeSoundBtns = document.querySelectorAll('.remove-sound-btn');

            // Mostrar/ocultar campo de upload baseado na seleção
            soundSelects.forEach((select) => {
                const eventKey = select.dataset.event;
                const uploadContainer = document.querySelector(`.sound-upload-container[data-event="${eventKey}"]`);
                
                const toggleUpload = () => {
                    if (select.value === 'custom') {
                        if (uploadContainer) uploadContainer.style.display = 'block';
                    } else {
                        if (uploadContainer) uploadContainer.style.display = 'none';
                    }
                };

                toggleUpload();
                select.addEventListener('change', (e) => {
                    toggleUpload();
                    const testBtn = document.querySelector(`.test-sound-btn[data-event="${eventKey}"]`);
                    if (testBtn) {
                        testBtn.dataset.sound = e.target.value;
                    }
                });
            });

            // Tocar som quando o botão de teste for clicado
            testSoundBtns.forEach((btn) => {
                btn.addEventListener('click', () => {
                    const soundType = btn.dataset.sound;
                    const customSound = btn.dataset.customSound;
                    
                    if (soundType === 'custom' && customSound) {
                        playCustomSound(customSound);
                    } else {
                        playSound(soundType);
                    }
                });
            });

            // Remover som personalizado
            removeSoundBtns.forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const eventKey = btn.dataset.event;
                    const confirmed = await showCustomConfirm(
                        'Tem certeza que deseja remover o som personalizado?',
                        'Remover som personalizado'
                    );
                    
                    if (!confirmed) {
                        return;
                    }

                    try {
                        const response = await fetch(`/settings/notifications/sounds/${eventKey}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                            }
                        });

                        if (response.ok) {
                            location.reload();
                        } else {
                            showCustomAlert('Erro', 'Erro ao remover som personalizado.', 'error');
                        }
                    } catch (error) {
                        console.error('Erro ao remover som:', error);
                        showCustomAlert('Erro', 'Erro ao remover som personalizado.', 'error');
                    }
                });
            });
        };

        // Inicializar quando a guia Sons for carregada
        updateSoundButtons();

        // Função para tocar som padrão
        function playSound(type) {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            // Diferentes frequências para diferentes tipos de som
            const frequencies = {
                'notification': 800,
                'success': 1000,
                'error': 400,
                'warning': 600,
                'info': 700,
            };

            oscillator.frequency.value = frequencies[type] || 800;
            oscillator.type = 'sine';
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        }

        // Função para tocar som personalizado (MP3)
        function playCustomSound(filePath) {
            const audio = new Audio(`/storage/${filePath}`);
            audio.play().catch(error => {
                console.error('Erro ao tocar som personalizado:', error);
                showCustomAlert('Erro', 'Erro ao reproduzir o arquivo de som. Verifique se o arquivo está acessível.', 'error');
            });
        }

        // Atualizar visibilidade da guia Sons quando o som for habilitado/desabilitado
        const soundCheckbox = document.querySelector('input[name="notifications_sound_enabled"]');
        if (soundCheckbox) {
            soundCheckbox.addEventListener('change', (e) => {
                const isEnabled = e.target.checked;
                const sonsTab = document.querySelector('[data-tab="sons"]');
                const sonsButton = document.querySelector('[data-tab-button="sons"]');
                
                if (isEnabled) {
                    if (!sonsButton) {
                        // Criar botão da guia Sons
                        const notificacoesButton = document.querySelector('[data-tab-button="notificacoes"]');
                        if (notificacoesButton) {
                            const newButton = document.createElement('button');
                            newButton.type = 'button';
                            newButton.setAttribute('data-tab-button', 'sons');
                            newButton.className = 'px-4 py-2 rounded-lg text-sm font-semibold transition-colors text-slate-300 hover:text-white hover:bg-slate-800/60';
                            newButton.textContent = 'Sons';
                            newButton.addEventListener('click', () => setActive('sons'));
                            notificacoesButton.parentNode.insertBefore(newButton, notificacoesButton.nextSibling);
                        }
                    }
                    if (sonsTab) {
                        sonsTab.classList.remove('hidden');
                    }
                } else {
                    if (sonsButton) {
                        sonsButton.remove();
                    }
                    if (sonsTab) {
                        sonsTab.classList.add('hidden');
                    }
                    // Voltar para a guia de notificações se estiver na guia de sons
                    const activeTab = document.querySelector('.settings-tab:not(.hidden)');
                    if (!activeTab || activeTab.getAttribute('data-tab') === 'sons') {
                        setActive('notificacoes');
                    }
                }
            });
        }

        // Funcionalidade de exclusão de regras
        const deleteRuleBtns = document.querySelectorAll('.delete-rule-btn');
        deleteRuleBtns.forEach((btn) => {
            btn.addEventListener('click', async () => {
                const ruleId = btn.dataset.ruleId;
                const ruleName = btn.dataset.ruleName;
                
                const confirmed = await showCustomConfirm(
                    `Tem certeza que deseja excluir a regra "${ruleName}"?\n\nEsta ação não pode ser desfeita.`,
                    'Excluir regra'
                );
                
                if (!confirmed) {
                    return;
                }

                try {
                    const response = await fetch(`/settings/score-rules/${ruleId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });

                    if (response.ok || response.redirected) {
                        location.reload();
                    } else {
                        const errorData = await response.json().catch(() => ({}));
                        console.error('Erro ao excluir regra:', response.status, errorData);
                        showCustomAlert('Erro', `Erro ao excluir regra (${response.status}). ${errorData.message || 'Tente novamente.'}`, 'error');
                    }
                } catch (error) {
                    console.error('Erro ao excluir regra:', error);
                    showCustomAlert('Erro', 'Erro ao excluir regra. Verifique o console para mais detalhes.', 'error');
                }
            });
        });
    })();
</script>
@endsection
