@extends('layouts.app')

@section('title', 'Detalhes da Meta')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">{{ $goal->name }}</h1>
            <p class="text-slate-400">Detalhes da meta de desempenho</p>
        </div>

        <!-- Informações Principais -->
        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Escopo e Status -->
                <div>
                    <h3 class="text-sm font-medium text-slate-400 mb-2">Escopo</h3>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full
                        @if($goal->scope === 'global') bg-purple-600/20 text-purple-400
                        @elseif($goal->scope === 'team') bg-blue-600/20 text-blue-400
                        @else bg-green-600/20 text-green-400
                        @endif">
                        {{ ucfirst($goal->scope) }}
                    </span>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-slate-400 mb-2">Status</h3>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full
                        @if($progress['status'] === 'reached') bg-green-600/20 text-green-400
                        @elseif($progress['status'] === 'in_progress') bg-yellow-600/20 text-yellow-400
                        @else bg-red-600/20 text-red-400
                        @endif">
                        @if($progress['status'] === 'reached') Atingida
                        @elseif($progress['status'] === 'in_progress') Em Andamento
                        @else Não Atingida
                        @endif
                    </span>
                </div>

                <!-- Temporada -->
                <div>
                    <h3 class="text-sm font-medium text-slate-400 mb-2">Temporada</h3>
                    <p class="text-white">{{ $goal->season->name }}</p>
                </div>

                <!-- Equipe ou Vendedor -->
                @if($goal->team)
                <div>
                    <h3 class="text-sm font-medium text-slate-400 mb-2">Equipe</h3>
                    <p class="text-white">{{ $goal->team->name }}</p>
                </div>
                @endif

                @if($goal->seller)
                <div>
                    <h3 class="text-sm font-medium text-slate-400 mb-2">Vendedor</h3>
                    <p class="text-white">{{ $goal->seller->name }}</p>
                </div>
                @endif

                <!-- Período -->
                <div>
                    <h3 class="text-sm font-medium text-slate-400 mb-2">Período</h3>
                    <p class="text-white">{{ $goal->starts_at->format('d/m/Y') }} - {{ $goal->ends_at->format('d/m/Y') }}</p>
                </div>

                <!-- Valor Alvo -->
                <div>
                    <h3 class="text-sm font-medium text-slate-400 mb-2">Valor Alvo</h3>
                    <p class="text-white text-lg font-semibold">{{ number_format($goal->target_value, 0, ',', '.') }} pontos</p>
                </div>
            </div>

            <!-- Descrição -->
            @if($goal->description)
            <div class="mt-6 pt-6 border-t border-slate-700/50">
                <h3 class="text-sm font-medium text-slate-400 mb-2">Descrição</h3>
                <p class="text-slate-300 whitespace-pre-wrap">{{ $goal->description }}</p>
            </div>
            @endif
        </div>

        <!-- Progresso -->
        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6 mb-6">
            <h2 class="text-xl font-semibold text-white mb-4">Progresso</h2>
            
            <div class="mb-4">
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="text-slate-400">Progresso Atual</span>
                    <span class="text-white font-semibold text-lg">{{ number_format($progress['progress'], 1) }}%</span>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-4">
                    <div class="h-4 rounded-full transition-all flex items-center justify-end pr-2
                        @if($progress['progress'] >= 100) bg-green-500
                        @elseif($progress['progress'] >= 50) bg-yellow-500
                        @else bg-blue-500
                        @endif"
                        style="width: {{ min(100, $progress['progress']) }}%">
                        @if($progress['progress'] >= 5)
                        <span class="text-xs text-white font-semibold">{{ number_format($progress['progress'], 0) }}%</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                <div class="bg-slate-800/50 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-slate-400 mb-1">Valor Atual</h3>
                    <p class="text-white text-2xl font-bold">{{ number_format($progress['current_value'], 0, ',', '.') }}</p>
                    <p class="text-slate-500 text-sm mt-1">pontos</p>
                </div>
                <div class="bg-slate-800/50 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-slate-400 mb-1">Valor Restante</h3>
                    <p class="text-white text-2xl font-bold">
                        {{ number_format(max(0, $goal->target_value - $progress['current_value']), 0, ',', '.') }}
                    </p>
                    <p class="text-slate-500 text-sm mt-1">pontos</p>
                </div>
            </div>
        </div>

        <!-- Ações -->
        <div class="flex items-center justify-between">
            <a href="{{ route('goals.index') }}" class="px-4 py-2 text-slate-400 hover:text-white">
                ← Voltar
            </a>
            <div class="flex items-center gap-3">
                @can('update', $goal)
                <a href="{{ route('goals.edit', $goal) }}" class="px-4 py-2 bg-blue-600/20 text-blue-400 rounded-lg hover:bg-blue-600/30 transition-colors">
                    Editar
                </a>
                @endcan
                @can('delete', $goal)
                <form method="POST" action="{{ route('goals.destroy', $goal) }}" onsubmit="return confirm('Tem certeza que deseja excluir esta meta?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600/20 text-red-400 rounded-lg hover:bg-red-600/30 transition-colors">
                        Excluir
                    </button>
                </form>
                @endcan
                @can('create', App\Models\Goal::class)
                <form method="POST" action="{{ route('goals.duplicate', $goal) }}">
                    @csrf
                    <select name="season_id" required class="px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white text-sm mr-2">
                        @foreach(\App\Models\Season::all() as $season)
                            @if($season->id !== $goal->season_id)
                            <option value="{{ $season->id }}">{{ $season->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-purple-600/20 text-purple-400 rounded-lg hover:bg-purple-600/30 transition-colors">
                        Duplicar para Temporada
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection
