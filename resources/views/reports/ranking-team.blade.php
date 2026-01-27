@extends('layouts.app')

@section('title', 'Relatório - Ranking por Equipe')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-7xl mx-auto">
        @php
            $user = auth()->user();
            $isAdmin = $user && $user->role === 'admin';
            $currentSectorName = $isAdmin && isset($sectorOptions)
                ? optional($sectorOptions->firstWhere('id', $currentSectorId ?? null))->name
                : ($user?->sector?->name ?? null);
        @endphp
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Ranking por Equipe</h1>
                <p class="text-slate-400">Ranking interno de cada equipe</p>
                <span class="text-xs text-slate-400">
                    Setor: <span class="text-slate-200">{{ $currentSectorName ?? 'Não selecionado' }}</span>
                </span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.ranking-team', array_merge(request()->query(), ['export' => 'csv'])) }}" 
                   class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700">
                    Exportar CSV
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6 mb-6">
            <form method="GET" action="{{ route('reports.ranking-team') }}" class="flex gap-4 items-end">
                @if($isAdmin && isset($sectorOptions) && $sectorOptions->isNotEmpty())
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Setor</label>
                        <select name="sector" id="sector-filter" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Selecione um setor</option>
                            @foreach($sectorOptions as $sector)
                                <option value="{{ $sector->id }}" {{ ($currentSectorId ?? null) === $sector->id ? 'selected' : '' }}>
                                    {{ $sector->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Temporada</label>
                    <select name="season" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Todas</option>
                        @foreach($seasons as $season)
                            <option value="{{ $season->id }}" {{ $seasonId == $season->id ? 'selected' : '' }}>
                                {{ $season->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        Filtrar
                    </button>
                    <a href="{{ route('reports.ranking-team') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg">
                        Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Ranking por Equipe -->
        <div class="space-y-6">
            @forelse($ranking as $team)
                <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 overflow-hidden">
                    <div class="bg-slate-800/50 px-6 py-4 border-b border-slate-700/50">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-white">{{ $team['team_name'] }}</h2>
                            <div class="text-slate-400 text-sm">
                                <span class="font-semibold text-white">{{ $team['sellers_count'] }}</span> vendedores
                                | Total: <span class="font-semibold text-white">{{ number_format($team['total_points'], 0, ',', '.') }}</span> pontos
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-800/30">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Posição</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Nome</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Pontos</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Contribuição</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50">
                                @forelse($team['sellers'] as $seller)
                                <tr class="hover:bg-slate-800/30">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-white font-bold">#{{ $seller['position'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-white font-medium">{{ $seller['seller_name'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-slate-400">{{ $seller['seller_email'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-white font-bold">{{ number_format($seller['points'], 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-slate-800 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, $seller['percentage']) }}%"></div>
                                            </div>
                                            <span class="text-slate-400 text-sm w-16 text-right">{{ number_format($seller['percentage'], 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                        Nenhum vendedor encontrado
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-8 text-center">
                    <p class="text-slate-400">Nenhuma equipe encontrada</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@if($isAdmin)
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const sectorSelect = document.getElementById('sector-filter');
        if (!sectorSelect) return;
        sectorSelect.addEventListener('change', () => {
            const form = sectorSelect.closest('form');
            if (form) form.submit();
        });
    });
    </script>
@endif
@endsection
