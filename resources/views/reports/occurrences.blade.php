@extends('layouts.app')

@section('title', 'Relatório - Ocorrências')

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
                <h1 class="text-3xl font-bold text-white mb-2">Ocorrências</h1>
                <p class="text-slate-400">Relatório de ocorrências recebidas via webhook</p>
                <span class="text-xs text-slate-400">
                    Setor: <span class="text-slate-200">{{ $currentSectorName ?? 'Não selecionado' }}</span>
                </span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.occurrences', array_merge(request()->query(), ['export' => 'csv'])) }}" 
                   class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700">
                    Exportar CSV
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6 mb-6">
            <form method="GET" action="{{ route('reports.occurrences') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                    <label class="block text-sm font-medium text-slate-300 mb-2">Status</label>
                    <select name="status" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Todos</option>
                        <option value="pendente" {{ $status === 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="processada" {{ $status === 'processada' ? 'selected' : '' }}>Processada</option>
                        <option value="erro" {{ $status === 'erro' ? 'selected' : '' }}>Erro</option>
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
                <div class="md:col-span-4 flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        Filtrar
                    </button>
                    <a href="{{ route('reports.occurrences') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg">
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Data/Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Email Funcionário</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Ocorrência</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Credor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Equipe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Pontos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Erro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($occurrences->items() as $item)
                        <tr class="hover:bg-slate-800/30">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-slate-400 text-sm">{{ $item['created_at']->format('d/m/Y H:i:s') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-white font-medium">{{ $item['email_funcionario'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-blue-400 font-semibold">{{ $item['ocorrencia'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-slate-400">{{ $item['credor'] ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-slate-400">{{ $item['equipe'] ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item['status'] === 'processada')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-600/20 text-green-400">
                                        Processada
                                    </span>
                                @elseif($item['status'] === 'erro')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-600/20 text-red-400">
                                        Erro
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-600/20 text-yellow-400">
                                        Pendente
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-white font-bold">{{ $item['points'] > 0 ? number_format($item['points'], 2, ',', '.') : '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if(!empty($item['error_message']))
                                    <span class="text-red-400 text-sm" title="{{ $item['error_message'] }}">
                                        {{ \Illuminate\Support\Str::limit($item['error_message'], 30) }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-400">
                                Nenhuma ocorrência encontrada
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            @if($occurrences->hasPages())
            <div class="px-6 py-4 border-t border-slate-700/50">
                {{ $occurrences->links() }}
            </div>
            @endif
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
