@extends('layouts.app')

@section('title', 'Equipes')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Equipes</h1>
                <p class="text-slate-400">Gerencie as equipes do sistema</p>
            </div>
            @can('create', App\Models\Team::class)
            <a href="{{ route('teams.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                Nova Equipe
            </a>
            @endcan
        </div>

        <!-- Mensagens -->
        @if(session('success'))
        <div class="mb-4 bg-green-500/10 border border-green-500/20 rounded-lg p-4">
            <p class="text-green-400">{{ session('success') }}</p>
        </div>
        @endif

        <!-- Grid de Equipes -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($teams as $team)
            <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-white">{{ $team->name }}</h3>
                    <div class="flex items-center gap-2">
                        @can('update', $team)
                        <a href="{{ route('teams.edit', $team) }}" class="text-yellow-400 hover:text-yellow-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>
                        @endcan
                        @can('delete', $team)
                        <form method="POST" action="{{ route('teams.destroy', $team) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta equipe?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
                <div class="flex items-center gap-2 text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>{{ $team->sellers_count ?? 0 }} vendedores</span>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <p class="text-slate-400">Nenhuma equipe encontrada</p>
            </div>
            @endforelse
        </div>

        <!-- Paginação -->
        @if($teams->hasPages())
        <div class="mt-6">
            {{ $teams->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
