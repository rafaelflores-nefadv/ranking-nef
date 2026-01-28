@extends('layouts.app')

@section('title', 'Editar Vendedor')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Editar Vendedor</h1>
            <p class="text-slate-400">Atualize as informações do Vendedor</p>
        </div>

        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
            <form method="POST" action="{{ route('sellers.update', $seller) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Avatar -->
                <x-avatar-upload
                    name="profile_photo"
                    :currentPath="$seller->profile_photo_path"
                    :fallbackName="$seller->name"
                    :allowRemove="true"
                    label="Foto de Perfil"
                />

                <!-- Nome -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Nome</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $seller->name) }}" required
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $seller->email) }}" required
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="external_code" class="block text-sm font-medium text-slate-300 mb-2">Código Externo</label>
                    <input type="text" id="external_code" name="external_code" value="{{ old('external_code', $seller->external_code) }}"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('external_code')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                @php
                    $user = auth()->user();
                    $isAdmin = $user && $user->role === 'admin';
                    $initialSectorId = old('sector_id') ?? ($isAdmin ? ($seller->sector_id ?? '') : ($user->sector_id ?? ''));
                    $teamsData = $teams->map(function($team) {
                        $displayLabel = $team->display_label;
                        return [
                            'id' => $team->id,
                            'name' => $team->name,
                            'display_label' => $displayLabel,
                            'sector_id' => $team->sector_id,
                            'sellers_count' => $team->sellers_count ?? 0,
                            'searchText' => strtolower(($displayLabel ?: '') . ' ' . ($team->name ?? ''))
                        ];
                    })->toArray();
                    $selectedTeamIds = old('teams', $currentTeamIds ?? []);
                @endphp
                <div class="mb-4"
                     x-data="teamsSelector(@js($teamsData), @js($selectedTeamIds), @js($initialSectorId), @js($isAdmin))"
                     x-init="init()">
                    @if($isAdmin)
                        <div class="mb-4">
                            <label for="sector_id" class="block text-sm font-medium text-slate-300 mb-2">Setor</label>
                            <select id="sector_id" name="sector_id"
                                x-model="sectorId"
                                @change="handleSectorChange()"
                                required
                                class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione um setor</option>
                                @foreach($sectors ?? [] as $sector)
                                    <option value="{{ $sector->id }}" {{ $sector->id === $initialSectorId ? 'selected' : '' }}>
                                        {{ $sector->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sector_id')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <!-- Equipes -->
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-slate-300">Equipes</label>
                        <span class="text-xs text-slate-400" x-show="selectedCount > 0">
                            <span x-text="selectedCount"></span> selecionada(s)
                        </span>
                    </div>

                    <!-- Barra de busca -->
                    <div class="mb-3">
                        <div class="relative">
                            <input 
                                type="text" 
                                x-model="search"
                                placeholder="Buscar equipe por nome..."
                                :disabled="isAdmin && !sectorId"
                                :class="{ 'opacity-50 cursor-not-allowed': isAdmin && !sectorId }"
                                class="w-full px-4 py-2 pl-10 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <svg class="absolute left-3 top-2.5 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Lista de equipes -->
                    <div class="bg-slate-800 border border-slate-600 rounded-lg p-4 max-h-64 overflow-y-auto">
                        <div x-show="isAdmin && !sectorId" class="text-center py-8">
                            <p class="text-slate-400 text-sm">Selecione um setor para listar as equipes.</p>
                        </div>
                        <!-- Mensagem quando não há resultados -->
                        <div x-show="filteredTeams.length === 0 && (!isAdmin || sectorId)" class="text-center py-8 alpine-no-results" style="display: none;">
                            <p class="text-slate-400 text-sm" x-show="search && search.trim()">
                                Nenhuma equipe encontrada para "<span x-text="search"></span>"
                            </p>
                            <p class="text-slate-400 text-sm" x-show="!search || !search.trim()">
                                Nenhuma equipe disponível
                            </p>
                        </div>

                        <!-- Botões de ação rápida -->
                        <div class="mb-3 flex gap-2" x-show="filteredTeams.length > 0">
                            <button 
                                type="button"
                                @click="toggleAll()"
                                :disabled="isAdmin && !sectorId"
                                :class="{ 'opacity-50 cursor-not-allowed': isAdmin && !sectorId }"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 transition-colors">
                                <span x-text="allFilteredSelected() ? 'Desselecionar Todas' : 'Selecionar Todas'"></span>
                            </button>
                            <button 
                                type="button"
                                @click="search = ''"
                                x-show="search"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 transition-colors">
                                Limpar Busca
                            </button>
                        </div>

                        <!-- Lista de equipes com Alpine -->
                        <div x-show="filteredTeams.length > 0" class="space-y-2 alpine-teams-list" style="display: none;">
                            <template x-for="team in filteredTeams" :key="team.id">
                                <label class="flex items-center space-x-3 p-3 rounded-lg hover:bg-slate-700/50 cursor-pointer transition-colors border border-transparent hover:border-slate-600 team-item"
                                       :class="{ 'bg-slate-700/30 border-blue-500/30': isSelected(team.id) }">
                                    <input 
                                        type="checkbox" 
                                        name="teams[]" 
                                        :value="team.id"
                                        :checked="isSelected(team.id)"
                                        @change="toggleTeam(team.id)"
                                :disabled="isAdmin && !sectorId"
                                        class="w-4 h-4 text-blue-600 bg-slate-700 border-slate-600 rounded focus:ring-blue-500 focus:ring-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-white text-sm font-medium" x-text="team.display_label || team.name || ''"></span>
                                            <span x-show="isSelected(team.id)" class="text-xs px-2 py-0.5 rounded-full bg-blue-600/20 text-blue-400 border border-blue-600/30">
                                                Selecionada
                                            </span>
                                        </div>
                                        <span class="text-slate-400 text-xs block" x-show="team.sellers_count !== undefined" x-text="team.sellers_count + ' vendedor(es)'"></span>
                                    </div>
                                </label>
                            </template>
                        </div>

                        <!-- Fallback: Lista estática caso Alpine não funcione -->
                        <div id="teams-fallback" class="space-y-2">
                            @if($teams->count() > 0)
                                @foreach($teams as $team)
                                    <label class="flex items-center space-x-3 p-3 rounded-lg hover:bg-slate-700/50 cursor-pointer transition-colors border border-transparent hover:border-slate-600 team-item"
                                           data-team-id="{{ $team->id }}"
                                           data-team-name="{{ strtolower(($team->display_label ?? '') . ' ' . ($team->name ?? '')) }}"
                                           data-sector-id="{{ $team->sector_id }}"
                                           @if(in_array($team->id, old('teams', $currentTeamIds ?? []))) style="background-color: rgba(51, 65, 85, 0.3); border-color: rgba(59, 130, 246, 0.3);" @endif>
                                        <input 
                                            type="checkbox" 
                                            name="teams[]" 
                                            value="{{ $team->id }}"
                                            {{ in_array($team->id, old('teams', $currentTeamIds ?? [])) ? 'checked' : '' }}
                                            class="team-checkbox w-4 h-4 text-blue-600 bg-slate-700 border-slate-600 rounded focus:ring-blue-500 focus:ring-2">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-white text-sm font-medium">{{ $team->display_label }}</span>
                                                @if(in_array($team->id, old('teams', $currentTeamIds ?? [])))
                                                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-600/20 text-blue-400 border border-blue-600/30">
                                                        Selecionada
                                                    </span>
                                                @endif
                                            </div>
                                            @if(isset($team->sellers_count))
                                                <span class="text-slate-400 text-xs block">{{ $team->sellers_count }} vendedor(es)</span>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            @else
                                <div class="text-center py-8">
                                    <p class="text-slate-400 text-sm">Nenhuma equipe disponível</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    @error('teams')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    @error('teams.*')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Temporada -->
                <div class="mb-4">
                    <label for="season_id" class="block text-sm font-medium text-slate-300 mb-2">Temporada</label>
                    <select id="season_id" name="season_id"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione uma temporada</option>
                        @foreach($seasons as $season)
                            <option value="{{ $season->id }}" {{ old('season_id', $seller->season_id) == $season->id ? 'selected' : '' }}>
                                {{ $season->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('season_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-slate-300 mb-2">Status</label>
                    <select id="status" name="status" required
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="active" {{ old('status', $seller->status) === 'active' ? 'selected' : '' }}>Ativo</option>
                        <option value="inactive" {{ old('status', $seller->status) === 'inactive' ? 'selected' : '' }}>Inativo</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('sellers.index') }}" class="px-4 py-2 text-slate-400 hover:text-white">
                        Cancelar
                    </a>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                        Atualizar Vendedor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function teamsSelector(teamsData, selectedTeamIds, initialSectorId, isAdmin) {
    return {
        search: '',
        selectedTeams: selectedTeamIds || [],
        teams: teamsData || [],
        sectorId: initialSectorId || '',
        isAdmin: !!isAdmin,
        init() {
            this.normalizeSelection();
        },
        get filteredTeams() {
            let availableTeams = this.teams;
            if (this.isAdmin) {
                if (!this.sectorId) {
                    return [];
                }
                availableTeams = availableTeams.filter(team => team.sector_id === this.sectorId);
            }
            if (!this.search || !this.search.trim()) return availableTeams;
            const query = this.search.toLowerCase().trim();
            return availableTeams.filter(team => {
                return team.searchText && team.searchText.includes(query);
            });
        },
        get selectedCount() {
            return this.selectedTeams.length;
        },
        handleSectorChange() {
            this.normalizeSelection();
        },
        normalizeSelection() {
            if (!this.isAdmin) {
                return;
            }
            if (!this.sectorId) {
                this.selectedTeams = [];
                return;
            }
            const allowedTeamIds = this.teams
                .filter(team => team.sector_id === this.sectorId)
                .map(team => team.id);
            this.selectedTeams = this.selectedTeams.filter(id => allowedTeamIds.includes(id));
        },
        isSelected(teamId) {
            return this.selectedTeams.includes(teamId);
        },
        toggleTeam(teamId) {
            const index = this.selectedTeams.indexOf(teamId);
            if (index > -1) {
                this.selectedTeams.splice(index, 1);
            } else {
                this.selectedTeams.push(teamId);
            }
        },
        toggleAll() {
            const allSelected = this.filteredTeams.length > 0 && this.filteredTeams.every(t => this.isSelected(t.id));
            const newState = !allSelected;
            
            this.filteredTeams.forEach(filteredTeam => {
                const index = this.selectedTeams.indexOf(filteredTeam.id);
                if (newState && index === -1) {
                    this.selectedTeams.push(filteredTeam.id);
                } else if (!newState && index > -1) {
                    this.selectedTeams.splice(index, 1);
                }
                const checkbox = document.querySelector('input[name="teams[]"][value="' + filteredTeam.id + '"]');
                if (checkbox) checkbox.checked = newState;
            });
        },
        allFilteredSelected() {
            return this.filteredTeams.length > 0 && this.filteredTeams.every(t => this.isSelected(t.id));
        }
    };
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[placeholder*="Buscar equipe"]');
    const fallbackContainer = document.getElementById('teams-fallback');
    const alpineList = document.querySelector('.alpine-teams-list');
    const alpineNoResults = document.querySelector('.alpine-no-results');
    const sectorSelect = document.getElementById('sector_id');
    
    if (!searchInput || !fallbackContainer) return;
    
    // Fallback de busca caso Alpine não funcione
    function handleSearch(query) {
        const teamItems = fallbackContainer.querySelectorAll('.team-item');
        let visibleCount = 0;
        const sectorId = sectorSelect ? sectorSelect.value : '';
        
        teamItems.forEach(item => {
            const searchText = item.dataset.teamName || '';
            const matchesSector = sectorSelect ? (sectorId ? item.dataset.sectorId === sectorId : false) : true;
            const matchesQuery = !query || searchText.includes(query);
            const matches = matchesSector && matchesQuery;
            item.style.display = matches ? 'flex' : 'none';
            if (matches) visibleCount++;
        });
        
        // Mostrar/ocultar mensagem de "sem resultados"
        let noResultsMsg = fallbackContainer.querySelector('.no-results-msg');
        if (query && visibleCount === 0) {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.className = 'no-results-msg text-center py-8';
                fallbackContainer.insertBefore(noResultsMsg, fallbackContainer.firstChild);
            }
            noResultsMsg.innerHTML = '<p class="text-slate-400 text-sm">Nenhuma equipe encontrada para "<span>' + query + '</span>"</p>';
            noResultsMsg.style.display = 'block';
        } else if (noResultsMsg) {
            noResultsMsg.style.display = 'none';
        }
    }
    
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        handleSearch(query);
    });

    if (sectorSelect) {
        sectorSelect.addEventListener('change', function() {
            handleSearch(searchInput.value.toLowerCase().trim());
        });
    }

    handleSearch(searchInput.value.toLowerCase().trim());
    
    // Verificar se Alpine está funcionando
    setTimeout(function() {
        // Se Alpine não estiver funcionando ou não estiver renderizando, usar fallback
        if (typeof Alpine === 'undefined' || !Alpine || (alpineList && alpineList.style.display === 'none' && !alpineList.offsetParent)) {
            // Ocultar elementos Alpine
            if (alpineList) alpineList.style.display = 'none';
            if (alpineNoResults) alpineNoResults.style.display = 'none';
            // Mostrar fallback
            fallbackContainer.style.display = 'block';
        } else {
            // Alpine está funcionando, ocultar fallback após um delay para garantir renderização
            setTimeout(function() {
                if (alpineList && alpineList.offsetParent) {
                    fallbackContainer.style.display = 'none';
                }
            }, 200);
        }
    }, 150);
});
</script>
@endpush
@endsection
