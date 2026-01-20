@extends('layouts.app')

@section('title', 'Editar Meta')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Editar Meta</h1>
            <p class="text-slate-400">Edite as informações da meta</p>
        </div>

        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
            <form method="POST" action="{{ route('goals.update', $goal) }}">
                @csrf
                @method('PUT')

                <!-- Escopo -->
                <div class="mb-4">
                    <label for="scope" class="block text-sm font-medium text-slate-300 mb-2">Escopo da Meta</label>
                    <select id="scope" name="scope" required
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="global" {{ old('scope', $goal->scope) == 'global' ? 'selected' : '' }}>Global (Toda a temporada)</option>
                        <option value="team" {{ old('scope', $goal->scope) == 'team' ? 'selected' : '' }}>Por Equipe</option>
                        <option value="seller" {{ old('scope', $goal->scope) == 'seller' ? 'selected' : '' }}>Por Vendedor</option>
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
                            <option value="{{ $season->id }}" {{ old('season_id', $goal->season_id) == $season->id ? 'selected' : '' }}>
                                {{ $season->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('season_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Equipe -->
                <div class="mb-4" id="team-field" style="display: {{ old('scope', $goal->scope) == 'team' ? 'block' : 'none' }};">
                    <label for="team_id" class="block text-sm font-medium text-slate-300 mb-2">Equipe <span class="text-red-400">*</span></label>
                    <select id="team_id" name="team_id"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione uma equipe</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ old('team_id', $goal->team_id) == $team->id ? 'selected' : '' }}>
                                {{ $team->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('team_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Vendedor -->
                <div class="mb-4" id="seller-field" style="display: {{ old('scope', $goal->scope) == 'seller' ? 'block' : 'none' }};">
                    <label for="seller_id" class="block text-sm font-medium text-slate-300 mb-2">Vendedor <span class="text-red-400">*</span></label>
                    <select id="seller_id" name="seller_id"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione um vendedor</option>
                        @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" {{ old('seller_id', $goal->seller_id) == $seller->id ? 'selected' : '' }}>
                                {{ $seller->name }} ({{ $seller->team->name ?? 'Sem equipe' }})
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
                    <input type="text" id="name" name="name" value="{{ old('name', $goal->name) }}" required
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descrição -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-slate-300 mb-2">Descrição</label>
                    <textarea id="description" name="description" rows="3"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $goal->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Valor Alvo -->
                <div class="mb-4">
                    <label for="target_value" class="block text-sm font-medium text-slate-300 mb-2">Valor Alvo (pontos) <span class="text-red-400">*</span></label>
                    <input type="number" id="target_value" name="target_value" value="{{ old('target_value', $goal->target_value) }}" required min="0" step="0.01"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('target_value')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Datas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="starts_at" class="block text-sm font-medium text-slate-300 mb-2">Data de Início <span class="text-red-400">*</span></label>
                        <input type="date" id="starts_at" name="starts_at" value="{{ old('starts_at', $goal->starts_at->format('Y-m-d')) }}" required
                            class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('starts_at')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="ends_at" class="block text-sm font-medium text-slate-300 mb-2">Data de Término <span class="text-red-400">*</span></label>
                        <input type="date" id="ends_at" name="ends_at" value="{{ old('ends_at', $goal->ends_at->format('Y-m-d')) }}" required
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
                        Atualizar Meta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scopeSelect = document.getElementById('scope');
    const teamField = document.getElementById('team-field');
    const sellerField = document.getElementById('seller-field');
    const teamIdSelect = document.getElementById('team_id');
    const sellerIdSelect = document.getElementById('seller_id');

    function updateFields() {
        const scope = scopeSelect.value;
        
        if (scope === 'team') {
            teamField.style.display = 'block';
            sellerField.style.display = 'none';
            teamIdSelect.required = true;
            sellerIdSelect.required = false;
        } else if (scope === 'seller') {
            teamField.style.display = 'none';
            sellerField.style.display = 'block';
            teamIdSelect.required = false;
            sellerIdSelect.required = true;
        } else {
            teamField.style.display = 'none';
            sellerField.style.display = 'none';
            teamIdSelect.required = false;
            sellerIdSelect.required = false;
        }
    }

    scopeSelect.addEventListener('change', updateFields);
});
</script>
@endsection
