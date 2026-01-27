@extends('layouts.app')

@section('title', 'Nova Integração API')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Nova Integração API</h1>
            <p class="text-slate-400">Cadastre uma nova integração para sistemas externos</p>
        </div>

        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
            <form method="POST" action="{{ route('settings.api.store') }}">
                @csrf

                @php
                    $user = auth()->user();
                    $isAdmin = $user && $user->role === 'admin';
                @endphp

                @if($isAdmin)
                    <!-- Setor -->
                    <div class="mb-4">
                        <label for="sector_id" class="block text-sm font-medium text-slate-300 mb-2">Setor *</label>
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
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Nome da Integração *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                        placeholder="Ex: ERP X, CRM Y"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descrição -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-slate-300 mb-2">Descrição</label>
                    <textarea id="description" name="description" rows="3"
                        placeholder="Descrição opcional da integração"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sistema/Fornecedor -->
                <div class="mb-4">
                    <label for="system" class="block text-sm font-medium text-slate-300 mb-2">Sistema/Fornecedor</label>
                    <input type="text" id="system" name="system" value="{{ old('system') }}"
                        placeholder="Ex: ERP X, Sistema Y"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('system')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                            class="h-5 w-5 accent-blue-600">
                        <span class="text-slate-300">Ativo</span>
                    </label>
                    <p class="mt-1 text-xs text-slate-400">Integrações inativas não podem usar tokens</p>
                </div>

                <!-- Botões -->
                <div class="flex items-center gap-3">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
                        Criar Integração
                    </button>
                    <a href="{{ route('settings.api.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-semibold">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
