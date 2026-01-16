@extends('layouts.app')

@section('title', 'Relatório - Gamificação')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Gamificação</h1>
                <p class="text-slate-400">Relatório de níveis, badges e progresso por vendedor</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.gamification', array_merge(request()->query(), ['export' => 'csv'])) }}" 
                   class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700">
                    Exportar CSV
                </a>
            </div>
        </div>

        <!-- Resumo por Nível -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            @foreach($byLevel as $level)
                <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-4">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white mb-1">{{ $level['level'] }}</div>
                        <div class="text-sm text-slate-400 mb-2">{{ $level['badge'] }}</div>
                        <div class="text-lg font-semibold text-blue-400">{{ $level['count'] }} vendedores</div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Tabela -->
        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Equipe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Temporada</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Pontos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Nível</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Badge</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Progresso</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($sellers->items() as $seller)
                        <tr class="hover:bg-slate-800/30">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-white font-medium">{{ $seller['name'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-slate-400">{{ $seller['email'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-slate-400">{{ $seller['team_name'] ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-slate-400">{{ $seller['season_name'] ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-white font-bold">{{ number_format($seller['points'], 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-blue-400 font-bold text-lg">{{ $seller['level'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-600/20 text-purple-400">
                                    {{ $seller['badge'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-slate-800 rounded-full h-2 min-w-[100px]">
                                        <div class="bg-gradient-to-r from-blue-600 to-purple-600 h-2 rounded-full" style="width: {{ min(100, $seller['progress']) }}%"></div>
                                    </div>
                                    <span class="text-slate-400 text-sm w-12 text-right">{{ number_format($seller['progress'], 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-400">
                                Nenhum vendedor encontrado
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            @if($sellers->hasPages())
            <div class="px-6 py-4 border-t border-slate-700/50">
                {{ $sellers->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
