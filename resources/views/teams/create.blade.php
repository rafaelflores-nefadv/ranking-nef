@extends('layouts.app')

@section('title', 'Nova Equipe')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Nova Equipe</h1>
            <p class="text-slate-400">Cadastre uma nova equipe no sistema</p>
        </div>

        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
            <form method="POST" action="{{ route('teams.store') }}">
                @csrf

                @php
                    $user = auth()->user();
                    $isAdmin = $user && $user->role === 'admin';
                @endphp

                @if($isAdmin)
                    <!-- Setor -->
                    <div class="mb-6">
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

                <!-- Nome -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Nome da Equipe</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-slate-400">Identificador técnico usado nas integrações.</p>
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nome de Exibição -->
                <div class="mb-6">
                    <label for="display_name" class="block text-sm font-medium text-slate-300 mb-2">Nome de Exibição</label>
                    <input type="text" id="display_name" name="display_name" value="{{ old('display_name') }}"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-slate-400">Opcional. Usado para exibir na interface e no monitor.</p>
                    @error('display_name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('teams.index') }}" class="px-4 py-2 text-slate-400 hover:text-white">
                        Cancelar
                    </a>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                        Criar Equipe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
