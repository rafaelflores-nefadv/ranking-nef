@extends('layouts.app')

@section('title', 'Detalhes do Colaborador')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">{{ $seller->name }}</h1>
            <p class="text-slate-400">Detalhes do colaborador</p>
        </div>

        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
            <!-- Foto de Perfil -->
            <div class="mb-6 flex items-center justify-center">
                <img src="{{ $seller->avatar ? asset('storage/' . $seller->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($seller->name) . '&background=6366f1&color=fff&size=200' }}" 
                     alt="{{ $seller->name }}" 
                     class="w-32 h-32 rounded-full object-cover border-4 border-slate-600 shadow-lg">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Nome</label>
                    <p class="text-white">{{ $seller->name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Email</label>
                    <p class="text-white">{{ $seller->email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Código Externo</label>
                    <p class="text-white">{{ $seller->external_code ?? '—' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Pontos</label>
                    <p class="text-white text-2xl font-bold">{{ number_format($seller->points, 0, ',', '.') }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Equipe</label>
                    <p class="text-white">{{ $seller->team?->name ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Temporada</label>
                    <p class="text-white">{{ $seller->season?->name ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Status</label>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $seller->status === 'active' ? 'bg-green-600/20 text-green-400' : 'bg-red-600/20 text-red-400' }}">
                        {{ $seller->status === 'active' ? 'Ativo' : 'Inativo' }}
                    </span>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-4">
                <a href="{{ route('sellers.index') }}" class="px-4 py-2 text-slate-400 hover:text-white">
                    Voltar
                </a>
                @can('update', $seller)
                <a href="{{ route('sellers.edit', $seller) }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                    Editar
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection
