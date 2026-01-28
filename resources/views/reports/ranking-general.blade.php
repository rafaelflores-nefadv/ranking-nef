@extends('layouts.app')

@section('title', 'Relatório - Ranking Geral')

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
                <h1 class="text-3xl font-bold text-white mb-2">Ranking Geral</h1>
                <p class="text-slate-400">Ranking de vendedores por pontos</p>
                <span class="text-xs text-slate-400">
                    Setor: <span class="text-slate-200">{{ $currentSectorName ?? 'Não selecionado' }}</span>
                </span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.ranking-general', array_merge(request()->query(), ['export' => 'csv'])) }}" 
                   class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700">
                    Exportar CSV
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6 mb-6">
            <form method="GET" action="{{ route('reports.ranking-general') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @if($isAdmin && isset($sectorOptions) && $sectorOptions->isNotEmpty())
                    <div>
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
                <div>
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
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Equipe</label>
                    <select name="team" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Todas</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ $teamId == $team->id ? 'selected' : '' }}>
                                {{ $team->display_label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Data Início</label>
                    <input type="date" name="start_date" value="{{ $startDate?->format('Y-m-d') }}" 
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Data Fim</label>
                    <input type="date" name="end_date" value="{{ $endDate?->format('Y-m-d') }}" 
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="md:col-span-5 flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        Filtrar
                    </button>
                    <a href="{{ route('reports.ranking-general') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg">
                        Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabela -->
        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Posição</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Equipe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Temporada</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Pontos</th>
                            @if($ranking->isNotEmpty() && $ranking->first()['evolution'] !== null)
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Evolução</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($ranking as $item)
                        <tr class="hover:bg-slate-800/30">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-white font-bold">#{{ $item['position'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-white font-medium">{{ $item['seller_name'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-slate-400">{{ $item['seller_email'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-slate-400">{{ $item['team_name'] ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-slate-400">{{ $item['season_name'] ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-white font-bold">{{ number_format($item['points'], 0, ',', '.') }}</span>
                            </td>
                            @if($item['evolution'] !== null)
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($item['evolution'] > 0)
                                        <span class="text-green-400 font-semibold">+{{ $item['evolution'] }}</span>
                                    @elseif($item['evolution'] < 0)
                                        <span class="text-red-400 font-semibold">{{ $item['evolution'] }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $ranking->isNotEmpty() && $ranking->first()['evolution'] !== null ? '7' : '6' }}" class="px-6 py-8 text-center text-slate-400">
                                Nenhum resultado encontrado
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@if($isAdmin)
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const sectorSelect = document.getElementById('sector-filter');
        if (!sectorSelect) return;
        const teamSelect = document.querySelector('select[name="team"]');
        sectorSelect.addEventListener('change', () => {
            if (teamSelect) teamSelect.value = '';
            const form = sectorSelect.closest('form');
            if (form) form.submit();
        });
    });
    </script>
@endif
@endsection
