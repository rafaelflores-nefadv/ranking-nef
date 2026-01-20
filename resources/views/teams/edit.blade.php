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

                <!-- Nome -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Nome da Equipe</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $team->name) }}" required
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Vendedores -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Vendedores</label>
                    <div class="bg-slate-800 border border-slate-600 rounded-lg p-4 max-h-64 overflow-y-auto">
                        @if($sellers->count() > 0)
                            <div class="space-y-2">
                                @foreach($sellers as $seller)
                                    <label class="flex items-center space-x-3 p-2 rounded-lg hover:bg-slate-700/50 cursor-pointer transition-colors">
                                        <input type="checkbox" 
                                               name="sellers[]" 
                                               value="{{ $seller->id }}"
                                               {{ in_array($seller->id, old('sellers', $currentSellerIds ?? [])) ? 'checked' : '' }}
                                               class="w-4 h-4 text-blue-600 bg-slate-700 border-slate-600 rounded focus:ring-blue-500 focus:ring-2">
                                        <div class="flex-1">
                                            <span class="text-white text-sm font-medium">{{ $seller->name }}</span>
                                            @if($seller->email)
                                                <span class="text-slate-400 text-xs block">{{ $seller->email }}</span>
                                            @endif
                                            @if($seller->team && $seller->team_id !== $team->id)
                                                <span class="text-yellow-400 text-xs block">Equipe: {{ $seller->team->name }}</span>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-slate-400 text-sm">Nenhum vendedor disponível</p>
                        @endif
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
@endsection
