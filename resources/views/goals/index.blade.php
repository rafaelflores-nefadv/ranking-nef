@extends('layouts.app')

@section('title', 'Metas')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Metas</h1>
                <p class="text-slate-400">Gerencie as metas de desempenho</p>
            </div>
            @can('create', App\Models\Goal::class)
            <a href="{{ route('goals.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                Nova Meta
            </a>
            @endcan
        </div>

        <!-- Mensagens -->
        @if(session('success'))
        <div class="mb-4 bg-green-500/10 border border-green-500/20 rounded-lg p-4">
            <p class="text-green-400">{{ session('success') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-4 bg-red-500/10 border border-red-500/20 rounded-lg p-4">
            <p class="text-red-400">{{ session('error') }}</p>
        </div>
        @endif

        <!-- Filtros -->
        <div class="mb-6 bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-4">
            <form method="GET" action="{{ route('goals.index') }}" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label for="season_id" class="block text-sm font-medium text-slate-300 mb-2">Temporada</label>
                    <select id="season_id" name="season_id"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todas</option>
                        @foreach($seasons as $season)
                            <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>
                                {{ $season->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label for="scope" class="block text-sm font-medium text-slate-300 mb-2">Escopo</label>
                    <select id="scope" name="scope"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="global" {{ request('scope') == 'global' ? 'selected' : '' }}>Global</option>
                        <option value="team" {{ request('scope') == 'team' ? 'selected' : '' }}>Por Equipe</option>
                        <option value="seller" {{ request('scope') == 'seller' ? 'selected' : '' }}>Por Vendedor</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Filtrar
                    </button>
                    <a href="{{ route('goals.index') }}" class="ml-2 px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600">
                        Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Grid de Metas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($goals as $goal)
            <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6 hover:border-blue-500/50 transition-all">
                <!-- Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-white mb-1">{{ $goal->name }}</h3>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($goal->scope === 'global') bg-purple-600/20 text-purple-400
                                @elseif($goal->scope === 'team') bg-blue-600/20 text-blue-400
                                @else bg-green-600/20 text-green-400
                                @endif">
                                {{ ucfirst($goal->scope) }}
                            </span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($goal->progress_data['status'] === 'reached') bg-green-600/20 text-green-400
                                @elseif($goal->progress_data['status'] === 'in_progress') bg-yellow-600/20 text-yellow-400
                                @else bg-red-600/20 text-red-400
                                @endif">
                                @if($goal->progress_data['status'] === 'reached') Atingida
                                @elseif($goal->progress_data['status'] === 'in_progress') Em Andamento
                                @else Não Atingida
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Informações -->
                <div class="mb-4 space-y-2 text-sm text-slate-400">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ $goal->season->name }}</span>
                    </div>
                    @if($goal->team)
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>{{ $goal->team->name }}</span>
                    </div>
                    @endif
                    @if($goal->seller)
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>{{ $goal->seller->name }}</span>
                    </div>
                    @endif
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Meta: {{ number_format($goal->target_value, 0, ',', '.') }} pontos</span>
                    </div>
                </div>

                <!-- Barra de Progresso -->
                <div class="mb-4">
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="text-slate-400">Progresso</span>
                        <span class="text-white font-semibold">{{ number_format($goal->progress_data['progress'], 1) }}%</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full transition-all
                            @if($goal->progress_data['progress'] >= 100) bg-green-500
                            @elseif($goal->progress_data['progress'] >= 50) bg-yellow-500
                            @else bg-blue-500
                            @endif"
                            style="width: {{ min(100, $goal->progress_data['progress']) }}%">
                        </div>
                    </div>
                    <div class="mt-1 text-xs text-slate-500">
                        {{ number_format($goal->progress_data['current_value'], 0, ',', '.') }} / {{ number_format($goal->target_value, 0, ',', '.') }} pontos
                    </div>
                </div>

                <!-- Datas -->
                <div class="mb-4 text-xs text-slate-500">
                    <div>Período: {{ $goal->starts_at->format('d/m/Y') }} - {{ $goal->ends_at->format('d/m/Y') }}</div>
                </div>

                <!-- Ações -->
                <div class="flex items-center gap-2 pt-4 border-t border-slate-700/50">
                    @can('view', $goal)
                    <a href="{{ route('goals.show', $goal) }}" class="flex-1 px-3 py-2 text-center text-sm bg-slate-800 text-slate-300 rounded-lg hover:bg-slate-700 transition-colors">
                        Ver
                    </a>
                    @endcan
                    @can('update', $goal)
                    <a href="{{ route('goals.edit', $goal) }}" class="flex-1 px-3 py-2 text-center text-sm bg-blue-600/20 text-blue-400 rounded-lg hover:bg-blue-600/30 transition-colors">
                        Editar
                    </a>
                    @endcan
                    @can('delete', $goal)
                    <form method="POST" action="{{ route('goals.destroy', $goal) }}" class="flex-1" onsubmit="return confirm('Tem certeza que deseja excluir esta meta?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-3 py-2 text-sm bg-red-600/20 text-red-400 rounded-lg hover:bg-red-600/30 transition-colors">
                            Excluir
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <p class="text-slate-400 text-lg">Nenhuma meta encontrada</p>
            </div>
            @endforelse
        </div>

        <!-- Paginação -->
        @if($goals->hasPages())
        <div class="mt-6">
            {{ $goals->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
