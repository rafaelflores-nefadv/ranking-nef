@extends('layouts.app')

@section('title', 'Editar Equipe')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Editar Equipe</h1>
            <p class="text-slate-400">Atualize as informações da equipe</p>
        </div>

        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
            <form method="POST" action="{{ route('teams.update', $team) }}">
                @csrf
                @method('PUT')

                @php
                    $user = auth()->user();
                    $isAdmin = $user && $user->role === 'admin';
                @endphp

                @if($isAdmin)
                    <!-- Setor -->
                    <div class="mb-6">
                        <label for="sector_id" class="block text-sm font-medium text-slate-300 mb-2">Setor</label>
                        <select id="sector_id" name="sector_id" disabled
                            class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white opacity-70 cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($sectors ?? [] as $sector)
                                <option value="{{ $sector->id }}" {{ $sector->id === $team->sector_id ? 'selected' : '' }}>
                                    {{ $sector->name }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="sector_id" value="{{ $team->sector_id }}">
                        @error('sector_id')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- Nome -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Nome da Equipe</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $team->name) }}" required
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-slate-400">Identificador técnico usado nas integrações.</p>
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nome de Exibição -->
                <div class="mb-6">
                    <label for="display_name" class="block text-sm font-medium text-slate-300 mb-2">Nome de Exibição</label>
                    <input type="text" id="display_name" name="display_name" value="{{ old('display_name', $team->display_name) }}"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-slate-400">Opcional. Usado para exibir na interface e no monitor.</p>
                    @error('display_name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Vendedores -->
                @php
                    $sellersData = $sellers->map(function($seller) use ($team, $currentSellerIds) {
                        return [
                            'id' => $seller->id,
                            'name' => $seller->name,
                            'email' => $seller->email ?? '',
                            'team_name' => $seller->team && $seller->team_id !== $team->id ? $seller->team->display_label : null,
                            'selected' => in_array($seller->id, old('sellers', $currentSellerIds ?? [])),
                            'searchText' => strtolower(($seller->name ?? '') . ' ' . ($seller->email ?? ''))
                        ];
                    })->toArray();
                    $initialSelectedCount = count(old('sellers', $currentSellerIds ?? []));
                @endphp
                <div class="mb-6" 
                     x-data="sellerSelector({{ $initialSelectedCount }}, @js($sellersData))"
                     x-init="init()">
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-slate-300">Vendedores</label>
                        <span class="text-xs text-slate-400" x-show="selectedCount > 0">
                            <span x-text="selectedCount"></span> selecionado(s)
                        </span>
                    </div>

                    <!-- Barra de busca -->
                    <div class="mb-3">
                        <div class="relative">
                            <input 
                                type="text" 
                                x-model="search"
                                placeholder="Buscar vendedor por nome ou email..."
                                class="w-full px-4 py-2 pl-10 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <svg class="absolute left-3 top-2.5 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Botões de ação rápida -->
                    <div class="mb-3 flex gap-2" x-show="filteredSellers.length > 0">
                        <button 
                            type="button"
                            @click="toggleAll()"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 transition-colors">
                            <span x-text="allFilteredSelected() ? 'Desselecionar Todos' : 'Selecionar Todos'"></span>
                        </button>
                        <button 
                            type="button"
                            @click="search = ''"
                            x-show="search"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 transition-colors">
                            Limpar Busca
                        </button>
                    </div>

                    <!-- Lista de vendedores -->
                    <div class="bg-slate-800 border border-slate-600 rounded-lg p-4 max-h-80 overflow-y-auto">
                        <!-- Lista de vendedores com Alpine (será mostrada se Alpine funcionar) -->
                        <div x-show="filteredSellers.length > 0" class="space-y-2 alpine-sellers-list" style="display: none;">
                            <template x-for="seller in filteredSellers" :key="seller.id">
                                <label class="flex items-center space-x-3 p-3 rounded-lg hover:bg-slate-700/50 cursor-pointer transition-colors border border-transparent hover:border-slate-600"
                                       :class="{ 'bg-slate-700/30 border-blue-500/30': seller.selected }">
                                    <input 
                                        type="checkbox" 
                                        name="sellers[]" 
                                        :value="seller.id"
                                        :checked="seller.selected"
                                        @change="toggleSeller(seller.id)"
                                        class="w-4 h-4 text-blue-600 bg-slate-700 border-slate-600 rounded focus:ring-blue-500 focus:ring-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-white text-sm font-medium" x-text="seller.name || ''"></span>
                                            <span x-show="seller.selected" class="text-xs px-2 py-0.5 rounded-full bg-blue-600/20 text-blue-400 border border-blue-600/30">
                                                Selecionado
                                            </span>
                                        </div>
                                        <span class="text-slate-400 text-xs block truncate" x-text="seller.email || ''" x-show="seller.email"></span>
                                        <span class="text-yellow-400 text-xs block" x-show="seller.team_name" x-text="'Equipe: ' + seller.team_name"></span>
                                    </div>
                                </label>
                            </template>
                        </div>

                        <!-- Mensagem quando não há resultados (Alpine) -->
                        <div x-show="filteredSellers.length === 0" class="text-center py-8 alpine-no-results" style="display: none;">
                            <p class="text-slate-400 text-sm" x-show="search && search.trim()">
                                Nenhum vendedor encontrado para "<span x-text="search"></span>"
                            </p>
                            <p class="text-slate-400 text-sm" x-show="!search || !search.trim()">
                                Nenhum vendedor disponível
                            </p>
                        </div>

                        <!-- Fallback: Lista estática caso Alpine não funcione -->
                        <div id="sellers-fallback" class="space-y-2">
                            @if($sellers->count() > 0)
                                @foreach($sellers as $seller)
                                    <label class="flex items-center space-x-3 p-3 rounded-lg hover:bg-slate-700/50 cursor-pointer transition-colors border border-transparent hover:border-slate-600 seller-item"
                                           data-seller-id="{{ $seller->id }}"
                                           data-seller-name="{{ strtolower($seller->name . ' ' . ($seller->email ?? '')) }}"
                                           @if(in_array($seller->id, old('sellers', $currentSellerIds ?? []))) style="background-color: rgba(51, 65, 85, 0.3); border-color: rgba(59, 130, 246, 0.3);" @endif>
                                        <input 
                                            type="checkbox" 
                                            name="sellers[]" 
                                            value="{{ $seller->id }}"
                                            {{ in_array($seller->id, old('sellers', $currentSellerIds ?? [])) ? 'checked' : '' }}
                                            class="seller-checkbox w-4 h-4 text-blue-600 bg-slate-700 border-slate-600 rounded focus:ring-blue-500 focus:ring-2">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-white text-sm font-medium">{{ $seller->name }}</span>
                                                @if(in_array($seller->id, old('sellers', $currentSellerIds ?? [])))
                                                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-600/20 text-blue-400 border border-blue-600/30">
                                                        Selecionado
                                                    </span>
                                                @endif
                                            </div>
                                            @if($seller->email)
                                                <span class="text-slate-400 text-xs block truncate">{{ $seller->email }}</span>
                                            @endif
                                            @if($seller->team && $seller->team_id !== $team->id)
                                                <span class="text-yellow-400 text-xs block">Equipe: {{ $seller->team->display_label }}</span>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            @else
                                <div class="text-center py-8">
                                    <p class="text-slate-400 text-sm">Nenhum vendedor disponível</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    @error('sellers')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    @error('sellers.*')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('teams.index') }}" class="px-4 py-2 text-slate-400 hover:text-white">
                        Cancelar
                    </a>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                        Atualizar Equipe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function sellerSelector(initialCount, sellersData) {
    return {
        search: '',
        selectedCount: initialCount,
        sellers: sellersData,
        init() {
            // Inicialização se necessário
        },
        get filteredSellers() {
            if (!this.search || !this.search.trim()) return this.sellers;
            const query = this.search.toLowerCase().trim();
            return this.sellers.filter(seller => {
                return seller.searchText && seller.searchText.includes(query);
            });
        },
        toggleAll() {
            const allSelected = this.filteredSellers.length > 0 && this.filteredSellers.every(s => s.selected);
            const newState = !allSelected;
            
            this.filteredSellers.forEach(filteredSeller => {
                const seller = this.sellers.find(s => s.id === filteredSeller.id);
                if (seller) {
                    seller.selected = newState;
                }
                const checkbox = document.querySelector('input[name="sellers[]"][value="' + filteredSeller.id + '"]');
                if (checkbox) checkbox.checked = newState;
            });
            this.updateCount();
        },
        toggleSeller(sellerId) {
            const seller = this.sellers.find(s => s.id === sellerId);
            if (seller) {
                seller.selected = !seller.selected;
                this.updateCount();
            }
        },
        updateCount() {
            this.selectedCount = this.sellers.filter(s => s.selected).length;
        },
        allFilteredSelected() {
            return this.filteredSellers.length > 0 && this.filteredSellers.every(s => s.selected);
        },
        someFilteredSelected() {
            return this.filteredSellers.some(s => s.selected) && !this.allFilteredSelected();
        }
    };
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[placeholder*="Buscar vendedor"]');
    const fallbackContainer = document.getElementById('sellers-fallback');
    const alpineList = document.querySelector('.alpine-sellers-list');
    const alpineNoResults = document.querySelector('.alpine-no-results');
    
    if (!searchInput || !fallbackContainer) return;
    
    // Fallback de busca caso Alpine não funcione
    function handleSearch(query) {
        const sellerItems = fallbackContainer.querySelectorAll('.seller-item');
        let visibleCount = 0;
        
        sellerItems.forEach(item => {
            const searchText = item.dataset.sellerName || '';
            const matches = !query || searchText.includes(query);
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
            noResultsMsg.innerHTML = '<p class="text-slate-400 text-sm">Nenhum vendedor encontrado para "<span>' + query + '</span>"</p>';
            noResultsMsg.style.display = 'block';
        } else if (noResultsMsg) {
            noResultsMsg.style.display = 'none';
        }
    }
    
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        handleSearch(query);
    });
    
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
