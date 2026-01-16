@extends('layouts.app')

@section('title', 'Notificações')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Notificações</h1>
            <p class="text-slate-400">Últimas ocorrências registradas.</p>
        </div>

        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-4">
            <form method="GET" action="{{ route('notifications.index') }}" class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between mb-4">
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="text-sm text-slate-400" for="start_date">Início</label>
                        <input
                            id="start_date"
                            name="start_date"
                            type="date"
                            value="{{ request('start_date') }}"
                            class="w-full sm:w-48 bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                        />
                    </div>
                    <div>
                        <label class="text-sm text-slate-400" for="end_date">Fim</label>
                        <input
                            id="end_date"
                            name="end_date"
                            type="date"
                            value="{{ request('end_date') }}"
                            class="w-full sm:w-48 bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="h-10 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                            Filtrar
                        </button>
                        <a href="{{ route('notifications.index') }}" class="h-10 px-4 rounded-lg bg-slate-800/70 hover:bg-slate-700 text-white text-sm font-semibold inline-flex items-center">
                            Limpar
                        </a>
                    </div>
                </div>
                <div class="flex flex-col gap-2 md:items-end">
                    <div class="flex items-center gap-3">
                        <label class="text-sm text-slate-400" for="notifications-search">Buscar</label>
                        <input
                            id="notifications-search"
                            type="text"
                            placeholder="Digite para filtrar..."
                            class="w-full md:w-64 bg-slate-900/60 border border-slate-700 text-white rounded-md px-3 py-2 text-sm"
                        />
                    </div>
                    <div class="text-xs text-slate-500 md:text-right">
                        Exibindo <span id="notifications-count">{{ $notifications->count() }}</span> de {{ $notifications->total() }} notificações
                    </div>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-slate-400 border-b border-slate-800/60">
                        <tr>
                            <th class="text-left font-medium py-3">Tipo</th>
                            <th class="text-left font-medium py-3">Ocorrência</th>
                            <th class="text-left font-medium py-3">Colaborador</th>
                            <th class="text-left font-medium py-3">Pontos</th>
                            <th class="text-right font-medium py-3">Data</th>
                        </tr>
                    </thead>
                    <tbody id="notifications-table" class="divide-y divide-slate-800/60">
                        @forelse($notifications as $notification)
                            @php
                                $occurrence = $notification->scoreRule?->description ?? $notification->scoreRule?->ocorrencia ?? 'Ocorrência registrada';
                                $sellerName = $notification->seller?->name ?? 'Vendedor';
                                $points = number_format($notification->points ?? 0, 2, ',', '.');
                                $dateLabel = optional($notification->created_at)->format('d/m/Y H:i');
                                $typeLabel = 'Nova ' . strtolower($saleTerm ?? 'Venda');
                                $searchText = strtolower($typeLabel . ' ' . $occurrence . ' ' . $sellerName . ' ' . $points . ' ' . $dateLabel);
                            @endphp
                            <tr data-search="{{ $searchText }}">
                                <td class="py-3 text-white font-medium">{{ $typeLabel }}</td>
                                <td class="py-3 text-slate-300">{{ $occurrence }}</td>
                                <td class="py-3 text-slate-300">{{ $sellerName }}</td>
                                <td class="py-3 text-blue-300">+{{ $points }}</td>
                                <td class="py-3 text-right text-slate-500">{{ $dateLabel }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400">
                                    Nenhuma notificação no momento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('notifications-search');
        const tableBody = document.getElementById('notifications-table');
        const count = document.getElementById('notifications-count');
        if (!searchInput || !tableBody || !count) return;

        const rows = Array.from(tableBody.querySelectorAll('tr'));

        const applyFilter = () => {
            const query = searchInput.value.trim().toLowerCase();
            let visible = 0;

            rows.forEach((row) => {
                const text = row.dataset.search || '';
                const match = !query || text.includes(query);
                row.classList.toggle('hidden', !match);
                if (match && row.querySelectorAll('td').length > 1) {
                    visible += 1;
                }
            });

            count.textContent = String(visible);
        };

        searchInput.addEventListener('input', applyFilter);
        applyFilter();
    });
</script>
@endpush
