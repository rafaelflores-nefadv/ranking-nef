@extends('layouts.app')

@section('title', 'Nova Meta')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Nova Meta</h1>
            <p class="text-slate-400">Crie uma nova meta de desempenho</p>
        </div>

        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
            <form method="POST" action="{{ route('goals.store') }}" id="goalForm">
                @csrf

                @php
                    $user = auth()->user();
                    $isAdmin = $user && $user->role === 'admin';
                @endphp

                @if($isAdmin)
                    <!-- Setor -->
                    <div class="mb-4">
                        <label for="sector_id" class="block text-sm font-medium text-slate-300 mb-2">Setor</label>
                        <select id="sector_id" name="sector_id" required
                            class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione um setor</option>
                            @foreach($sectors ?? [] as $sector)
                                <option value="{{ $sector->id }}" {{ old('sector_id') == $sector->id ? 'selected' : '' }}>
                                    {{ $sector->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('sector_id')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- Escopo -->
                <div class="mb-4">
                    <label for="scope" class="block text-sm font-medium text-slate-300 mb-2">Escopo da Meta</label>
                    <select id="scope" name="scope" required
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione o escopo</option>
                        <option value="global" {{ old('scope') == 'global' ? 'selected' : '' }}>Global (Toda a temporada)</option>
                        <option value="team" {{ old('scope') == 'team' ? 'selected' : '' }}>Por Equipe</option>
                        <option value="seller" {{ old('scope') == 'seller' ? 'selected' : '' }}>Por Vendedor</option>
                    </select>
                    @error('scope')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Temporada -->
                <div class="mb-4">
                    <label for="season_id" class="block text-sm font-medium text-slate-300 mb-2">Temporada <span class="text-red-400">*</span></label>
                    <select id="season_id" name="season_id" required
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione uma temporada</option>
                        @foreach($seasons as $season)
                            <option value="{{ $season->id }}" {{ old('season_id') == $season->id ? 'selected' : '' }}>
                                {{ $season->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('season_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Equipe (mostrar apenas se escopo for team) -->
                <div class="mb-4" id="team-field" style="display: none;">
                    <label for="team_id" class="block text-sm font-medium text-slate-300 mb-2">Equipe <span class="text-red-400">*</span></label>
                    <select id="team_id" name="team_id"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione uma equipe</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" data-sector-id="{{ $team->sector_id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                {{ $team->display_label }}
                            </option>
                        @endforeach
                    </select>
                    @error('team_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    
                    <!-- Opção de criar para todos os vendedores da equipe -->
                    <div class="mt-3" id="create-for-all-sellers" style="display: none;">
                        <label class="flex items-center">
                            <input type="checkbox" name="create_for_all_team_sellers" value="1" class="mr-2">
                            <span class="text-sm text-slate-300">Criar metas individuais para todos os vendedores desta equipe</span>
                        </label>
                    </div>
                </div>

                <!-- Vendedor (mostrar apenas se escopo for seller) -->
                <div class="mb-4" id="seller-field" style="display: none;">
                    <label for="seller_id" class="block text-sm font-medium text-slate-300 mb-2">Vendedor <span class="text-red-400">*</span></label>
                    <select id="seller_id" name="seller_id"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione um vendedor</option>
                        @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" data-sector-id="{{ $seller->sector_id }}" {{ old('seller_id') == $seller->id ? 'selected' : '' }}>
                                {{ $seller->name }} ({{ $seller->team?->display_label ?? 'Sem equipe' }})
                            </option>
                        @endforeach
                    </select>
                    @error('seller_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nome -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Nome da Meta <span class="text-red-400">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Ex: Meta de Vendas Q1 2024">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descrição -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-slate-300 mb-2">Descrição</label>
                    <textarea id="description" name="description" rows="3"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Descrição detalhada da meta (opcional)">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Valor Alvo -->
                <div class="mb-4">
                    <label for="target_value" class="block text-sm font-medium text-slate-300 mb-2">Valor Alvo (pontos) <span class="text-red-400">*</span></label>
                    <input type="number" id="target_value" name="target_value" value="{{ old('target_value') }}" required min="0" step="0.01"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Ex: 10000">
                    @error('target_value')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Datas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="starts_at" class="block text-sm font-medium text-slate-300 mb-2">Data de Início <span class="text-red-400">*</span></label>
                        <input type="date" id="starts_at" name="starts_at" value="{{ old('starts_at') }}" required
                            class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('starts_at')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="ends_at" class="block text-sm font-medium text-slate-300 mb-2">Data de Término <span class="text-red-400">*</span></label>
                        <input type="date" id="ends_at" name="ends_at" value="{{ old('ends_at') }}" required
                            class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('ends_at')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('goals.index') }}" class="px-4 py-2 text-slate-400 hover:text-white">
                        Cancelar
                    </a>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                        Criar Meta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sectorSelect = document.getElementById('sector_id');
    const scopeSelect = document.getElementById('scope');
    const teamField = document.getElementById('team-field');
    const sellerField = document.getElementById('seller-field');
    const createForAllSellers = document.getElementById('create-for-all-sellers');
    const teamIdSelect = document.getElementById('team_id');
    const sellerIdSelect = document.getElementById('seller_id');
    const isAdmin = !!sectorSelect;

    function filterOptions(select, sectorId) {
        if (!select) return;
        Array.from(select.options).forEach((option) => {
            if (!option.value) {
                option.hidden = false;
                return;
            }
            const optionSector = option.dataset.sectorId;
            option.hidden = !sectorId || optionSector !== sectorId;
        });
        if (select.value && select.options[select.selectedIndex]?.hidden) {
            select.value = '';
        }
    }

    function updateFields() {
        const sectorId = sectorSelect ? sectorSelect.value : '';
        const sectorEnabled = !isAdmin || !!sectorId;
        const scope = scopeSelect.value;
        
        // Esconder todos os campos
        teamField.style.display = 'none';
        sellerField.style.display = 'none';
        createForAllSellers.style.display = 'none';
        
        // Limpar valores
        teamIdSelect.required = false;
        sellerIdSelect.required = false;
        teamIdSelect.disabled = !sectorEnabled;
        sellerIdSelect.disabled = !sectorEnabled;

        if (!sectorEnabled) {
            return;
        }
        
        // Mostrar campos conforme o escopo
        if (scope === 'team') {
            teamField.style.display = 'block';
            teamIdSelect.required = true;
            createForAllSellers.style.display = 'block';
        } else if (scope === 'seller') {
            sellerField.style.display = 'block';
            sellerIdSelect.required = true;
        }
    }

    function updateSectorState() {
        const sectorId = sectorSelect ? sectorSelect.value : '';
        const enableScope = !isAdmin || !!sectorId;

        if (isAdmin) {
            filterOptions(teamIdSelect, sectorId);
            filterOptions(sellerIdSelect, sectorId);
        }

        scopeSelect.disabled = !enableScope;
        scopeSelect.classList.toggle('opacity-50', !enableScope);
        scopeSelect.classList.toggle('cursor-not-allowed', !enableScope);
        if (!enableScope) {
            scopeSelect.value = '';
            teamIdSelect.value = '';
            sellerIdSelect.value = '';
        }

        updateFields();
    }

    scopeSelect.addEventListener('change', updateFields);

    if (sectorSelect) {
        sectorSelect.addEventListener('change', updateSectorState);
    }

    updateSectorState(); // Inicializar campos
});
</script>
@endsection
