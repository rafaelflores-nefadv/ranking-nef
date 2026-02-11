@extends('layouts.app')

@section('title', 'Editar Monitor')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Editar Monitor</h1>
            <p class="text-slate-400">Configure o monitor de exibição pública</p>
        </div>

        <!-- Mensagens -->
        @if(session('error') || (isset($errors) && $errors->any()))
        <div class="mb-4 bg-red-500/10 border border-red-500/20 rounded-lg p-4">
            @if(session('error'))
                <p class="text-red-400">{{ session('error') }}</p>
            @endif
            @if(isset($errors))
                @foreach($errors->all() as $error)
                    <p class="text-red-400">{{ $error }}</p>
                @endforeach
            @endif
        </div>
        @endif

        <!-- Formulário -->
        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
            <form method="POST" action="{{ route('admin.monitors.update', $monitor) }}">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Nome -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Nome do Monitor *</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $monitor->name) }}" required
                               class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ex: Monitor TV Sala Principal">
                    </div>

                    <!-- Slug -->
                    <div>
                        <label for="slug" class="block text-sm font-medium text-slate-300 mb-2">Slug (URL)</label>
                        <input type="text" id="slug" name="slug" value="{{ old('slug', $monitor->slug) }}"
                               class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ex: tv-sala-principal">
                        <p class="mt-1 text-xs text-slate-400">Altere com cuidado - pode quebrar links existentes</p>
                    </div>

                    <!-- Descrição -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-slate-300 mb-2">Descrição</label>
                        <textarea id="description" name="description" rows="3"
                                  class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Descrição opcional do monitor">{{ old('description', $monitor->description) }}</textarea>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $monitor->is_active) ? 'checked' : '' }}
                                   class="w-4 h-4 bg-slate-800 border-slate-700 rounded text-blue-600 focus:ring-blue-500">
                            <span class="text-sm font-medium text-slate-300">Monitor ativo</span>
                        </label>
                    </div>

                    <!-- Setores -->
                    <div>
                        <label for="sectors" class="block text-sm font-medium text-slate-300 mb-2">Setores para exibição *</label>
                        <select id="sectors" name="sectors[]" multiple required
                                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach($sectors as $sector)
                                <option value="{{ $sector->id }}" {{ in_array($sector->id, old('sectors', $selectedSectorIds ?? [])) ? 'selected' : '' }}>
                                    {{ $sector->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-400">Ao alterar setores, as equipes selecionadas serão limpas automaticamente.</p>
                    </div>

                    <div class="border-t border-slate-700/50 pt-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Configurações</h3>

                        <!-- Intervalo de atualização -->
                        <div class="mb-4">
                            <label for="refresh_interval" class="block text-sm font-medium text-slate-300 mb-2">Intervalo de Atualização (ms) *</label>
                            <input type="number" id="refresh_interval" name="refresh_interval" value="{{ old('refresh_interval', $settings['refresh_interval'] ?? 30000) }}" min="5000" step="1000" required
                                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Rotação automática de equipes -->
                        <div class="mb-4">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="auto_rotate_teams" value="1" {{ old('auto_rotate_teams', $settings['auto_rotate_teams'] ?? true) ? 'checked' : '' }}
                                       class="w-4 h-4 bg-slate-800 border-slate-700 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-medium text-slate-300">Rotação automática de equipes</span>
                            </label>
                        </div>

                        <!-- Equipes permitidas -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Equipes para Exibir</label>
                            <div id="teams-container" class="space-y-2 max-h-56 overflow-y-auto bg-slate-800/50 rounded-lg p-3 border border-slate-700/50">
                                @if($teams->isEmpty())
                                    <p class="text-xs text-slate-400">Selecione ao menos um setor para carregar as equipes.</p>
                                @else
                                    @foreach($teams as $team)
                                        <label class="flex items-center gap-2">
                                            <input type="checkbox" name="teams[]" value="{{ $team->id }}" {{ in_array($team->id, $selectedTeamIds ?? []) ? 'checked' : '' }}
                                                   class="w-4 h-4 bg-slate-800 border-slate-700 rounded text-blue-600 focus:ring-blue-500 team-checkbox">
                                            <span class="text-sm text-slate-300">{{ $team->display_label }}</span>
                                        </label>
                                    @endforeach
                                @endif
                            </div>
                            <p id="teams-help" class="mt-1 text-xs text-slate-400">Se nenhuma equipe for selecionada, serão consideradas todas as equipes dos setores escolhidos.</p>
                        </div>

                        <!-- Notificações -->
                        <div class="mb-4">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="notifications_enabled" value="1" {{ old('notifications_enabled', $settings['notifications_enabled'] ?? false) ? 'checked' : '' }}
                                       class="w-4 h-4 bg-slate-800 border-slate-700 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-medium text-slate-300">Notificações habilitadas</span>
                            </label>
                        </div>

                        <!-- Som -->
                        <div class="mb-4">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="sound_enabled" value="1" {{ old('sound_enabled', $settings['sound_enabled'] ?? false) ? 'checked' : '' }}
                                       class="w-4 h-4 bg-slate-800 border-slate-700 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-medium text-slate-300">Som habilitado</span>
                            </label>
                            <p class="mt-1 text-xs text-slate-400 ml-6">Reproduzir sons nas notificações</p>
                        </div>

                        <!-- Voz -->
                        <div class="mb-4">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="voice_enabled" value="1" {{ old('voice_enabled', $settings['voice_enabled'] ?? false) ? 'checked' : '' }}
                                       class="w-4 h-4 bg-slate-800 border-slate-700 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-medium text-slate-300">Leitura por voz habilitada</span>
                            </label>
                            <p class="mt-1 text-xs text-slate-400 ml-6">Reproduzir leitura do ranking por voz no navegador</p>
                        </div>

                        <!-- Escala de fonte -->
                        <div>
                            <label for="font_scale" class="block text-sm font-medium text-slate-300 mb-2">Escala de Fonte (TV)</label>
                            <input type="number" id="font_scale" name="font_scale" value="{{ old('font_scale', $settings['font_scale'] ?? 1.0) }}" min="0.5" max="3.0" step="0.1"
                                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-end gap-4 mt-6 pt-6 border-t border-slate-700/50">
                    <a href="{{ route('admin.monitors.index') }}" class="px-4 py-2 rounded-lg font-medium text-white transition-all duration-200" style='background: linear-gradient(90deg, #1e40af, #2563eb, rgb(243, 138, 39), rgba(243, 119, 53, 0.95));'>
                        Cancelar
                    </a>
                    <button type="submit" class="px-4 py-2 rounded-lg font-medium text-white transition-all duration-200" style='background: linear-gradient(90deg, #1e40af, #2563eb, rgb(243, 138, 39), rgba(243, 119, 53, 0.95));'>
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Carregar equipes dinamicamente conforme setores selecionados
    (function initMonitorSectorsTeams() {
        const sectorsSelect = document.getElementById('sectors');
        const teamsContainer = document.getElementById('teams-container');
        const teamsHelp = document.getElementById('teams-help');
        const teamsEndpoint = @json(route('admin.monitors.teams-for-sectors'));

        if (!sectorsSelect || !teamsContainer) return;

        const sectorNameMap = new Map(
            (@json($sectors->map(function ($s) {
                return ['id' => $s->id, 'name' => $s->name];
            })->values()->all()))
                .map((s) => [s.id, s.name])
        );

        let selectedTeamIds = new Set(@json($selectedTeamIds ?? []));

        const getSelectedSectorIds = () => {
            return Array.from(sectorsSelect.selectedOptions).map((opt) => opt.value).filter(Boolean);
        };

        const clearTeamsSelection = () => {
            selectedTeamIds = new Set();
            const existing = teamsContainer.querySelectorAll('input[name="teams[]"]');
            existing.forEach((el) => el.checked = false);
        };

        const renderTeams = (teams) => {
            teamsContainer.innerHTML = '';
            if (!teams.length) {
                const p = document.createElement('p');
                p.className = 'text-xs text-slate-400';
                p.textContent = 'Nenhuma equipe encontrada para os setores selecionados.';
                teamsContainer.appendChild(p);
                return;
            }

            teams.forEach((team) => {
                const label = document.createElement('label');
                label.className = 'flex items-center gap-2';
                const input = document.createElement('input');
                input.type = 'checkbox';
                input.name = 'teams[]';
                input.value = team.id;
                input.className = 'w-4 h-4 bg-slate-800 border-slate-700 rounded text-blue-600 focus:ring-blue-500 team-checkbox';
                input.checked = selectedTeamIds.has(team.id);
                input.addEventListener('change', () => {
                    if (input.checked) selectedTeamIds.add(team.id);
                    else selectedTeamIds.delete(team.id);
                });

                const span = document.createElement('span');
                span.className = 'text-sm text-slate-300';
                const sectorName = sectorNameMap.get(team.sector_id);
                const teamLabel = team.display_label || team.name;
                span.textContent = sectorName ? `${teamLabel} (${sectorName})` : teamLabel;

                label.appendChild(input);
                label.appendChild(span);
                teamsContainer.appendChild(label);
            });
        };

        const loadTeams = async ({ clearSelection = false } = {}) => {
            const sectorIds = getSelectedSectorIds();
            if (!sectorIds.length) {
                teamsContainer.innerHTML = '<p class="text-xs text-slate-400">Selecione ao menos um setor para carregar as equipes.</p>';
                if (teamsHelp) teamsHelp.textContent = 'Selecione setores para definir o escopo. Se nenhuma equipe for selecionada, serão consideradas todas as equipes dos setores.';
                clearTeamsSelection();
                return;
            }
            if (clearSelection) {
                clearTeamsSelection();
            }
            const url = new URL(teamsEndpoint, window.location.origin);
            sectorIds.forEach((id) => url.searchParams.append('sectors[]', id));

            const response = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            if (!response.ok) {
                teamsContainer.innerHTML = '<p class="text-xs text-red-400">Erro ao carregar equipes. Tente novamente.</p>';
                return;
            }
            const result = await response.json();
            const teams = result?.data || [];
            renderTeams(teams);
        };

        sectorsSelect.addEventListener('change', () => {
            // Regra: ao alterar setores, limpar automaticamente as equipes selecionadas
            loadTeams({ clearSelection: true });
        });

        // Inicial: garantir consistência com setores selecionados
        loadTeams({ clearSelection: false });
    })();
</script>
@endpush
@endsection
