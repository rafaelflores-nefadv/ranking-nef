@extends('layouts.app')

@section('title', 'Relatório - Evolução de Pontuação')

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
                <h1 class="text-3xl font-bold text-white mb-2">Evolução de Pontuação</h1>
                <p class="text-slate-400">Timeline de pontos por vendedor</p>
                <span class="text-xs text-slate-400">
                    Setor: <span class="text-slate-200">{{ $currentSectorName ?? 'Não selecionado' }}</span>
                </span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.score-evolution', array_merge(request()->query(), ['export' => 'csv'])) }}" 
                   class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700">
                    Exportar CSV
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6 mb-6">
            <form method="GET" action="{{ route('reports.score-evolution') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                    <label class="block text-sm font-medium text-slate-300 mb-2">Vendedor</label>
                    <select name="seller" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Todos</option>
                        @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" {{ $sellerId == $seller->id ? 'selected' : '' }}>
                                {{ $seller->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Data Início</label>
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" 
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Data Fim</label>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" 
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="md:col-span-4 flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        Filtrar
                    </button>
                    <a href="{{ route('reports.score-evolution') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg">
                        Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Evolução por Vendedor -->
        <div class="space-y-6">
            @forelse($evolution as $sellerEvolution)
                <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 overflow-hidden">
                    <div class="bg-slate-800/50 px-6 py-4 border-b border-slate-700/50">
                        <h2 class="text-xl font-bold text-white">{{ $sellerEvolution['seller_name'] }}</h2>
                        <p class="text-slate-400 text-sm">{{ $sellerEvolution['seller_email'] }} | {{ $sellerEvolution['team_name'] }}</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-800/30">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Pontos do Dia</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Ocorrências</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Acumulado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50">
                                @forelse($sellerEvolution['evolution'] as $day)
                                <tr class="hover:bg-slate-800/30">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-white">{{ \Carbon\Carbon::parse($day['date'])->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-blue-400 font-semibold">{{ number_format($day['points'], 2, ',', '.') }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-slate-400">{{ $day['occurrences'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-white font-bold">{{ number_format($day['accumulated'], 2, ',', '.') }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-400">
                                        Nenhum dado encontrado para este período
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-8 text-center">
                    <p class="text-slate-400">Nenhum dado encontrado para o período selecionado</p>
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
        const sellerSelect = document.querySelector('select[name="seller"]');
        sectorSelect.addEventListener('change', () => {
            if (sellerSelect) sellerSelect.value = '';
            const form = sectorSelect.closest('form');
            if (form) form.submit();
        });
    });
    </script>
@endif
@endsection
