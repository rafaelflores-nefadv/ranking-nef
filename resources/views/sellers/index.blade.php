@extends('layouts.app')

@section('title', 'Vendedores')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Vendedores</h1>
                <p class="text-slate-400">Gerencie os Vendedores do sistema</p>
            </div>
            @can('create', App\Models\Seller::class)
            <div class="flex items-center gap-3">
                <a href="{{ route('sellers.import') }}" class="px-4 py-2 rounded-lg font-medium text-white transition-all duration-200" style="background: linear-gradient(90deg, #1e40af, #2563eb, rgb(243, 138, 39), rgba(243, 119, 53, 0.95));">
                    Importar Vendedores
                </a>
                <a href="{{ route('sellers.create') }}" class="px-4 py-2 rounded-lg font-medium text-white transition-all duration-200" style="background: linear-gradient(90deg, #1e40af, #2563eb, rgb(243, 138, 39), rgba(243, 119, 53, 0.95));">
                    Novo Vendedor
                </a>
            </div>
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
        <div class="mb-6 bg-slate-900/50 backdrop-blur-sm rounded-xl border border-slate-700/50 p-4 grid gap-4 md:grid-cols-4">
            <input id="seller-search" type="text" placeholder="Buscar vendedor" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select id="filter-team" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos os times</option>
                @foreach($teams as $team)
                    <option value="{{ $team->display_label ?? $team->name }}">{{ $team->display_label ?? $team->name }}</option>
                @endforeach
            </select>
            <select id="filter-status" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos os status</option>
                <option value="Ativo">Ativo</option>
                <option value="Inativo">Inativo</option>
            </select>
            <select id="filter-team-code" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos os setores</option>
                @foreach($teams as $team)
                    <option value="{{ $team->sector?->name ?? 'Sem setor' }}">{{ $team->sector?->name ?? 'Sem setor' }}</option>
                @endforeach
            </select>
        </div>

        <!-- Tabela -->
        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table id="sellers-table" class="w-full">
                    <thead class="bg-slate-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Foto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Código Externo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Pontos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Equipe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($sellers as $seller)
                        <tr class="hover:bg-slate-800/30">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <x-avatar :name="$seller->name" :path="$seller->profile_photo_path" size="w-10 h-10" pixelSize="96" />
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-white font-medium">{{ $seller->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-slate-400">{{ $seller->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-slate-400">{{ $seller->external_code ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-white font-bold">{{ number_format($seller->points, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-slate-400">{{ $seller->team?->name ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $seller->status === 'active' ? 'bg-green-600/20 text-green-400' : 'bg-red-600/20 text-red-400' }}">
                                    {{ $seller->status === 'active' ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-3">
                                    @can('view', $seller)
                                    <a href="{{ route('sellers.show', $seller) }}" class="text-blue-400 hover:text-blue-300 transition-colors" title="Ver vendedor">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    @endcan
                                    @can('update', $seller)
                                    <a href="{{ route('sellers.edit', $seller) }}" class="text-yellow-400 hover:text-yellow-300 transition-colors" title="Editar Vendedor">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    @endcan
                                    @can('delete', $seller)
                                    <form method="POST" action="{{ route('sellers.destroy', $seller) }}" class="inline-block" style="display: inline-block;" onsubmit="return handleDeleteConfirm(event, 'Tem certeza que deseja excluir este Vendedor?', 'Excluir Vendedor');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 transition-colors" title="Excluir Vendedor">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endcan
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

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables@1.10.25/media/css/jquery.dataTables.min.css">
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables@1.10.25/media/js/jquery.dataTables.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const table = $('#sellers-table').DataTable({
            responsive: true,
            columnDefs: [{ targets: -1, orderable: false }],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json'
            },
            dom: 'rtip',
            pageLength: 10
        });

        $('#seller-search').on('keyup change', () => {
            table.search($('#seller-search').val()).draw();
        });

        $('#filter-status').on('change', () => {
            table.column(6).search($('#filter-status').val(), false, true).draw();
        });

        $('#filter-team').on('change', function () {
            const val = this.value;
            table.column(5).search(val, false, true).draw();
        });

        $('#filter-team-code').on('change', function () {
            const val = this.value;
            table.column(5).search(val, false, true).draw();
        });
    });
</script>
@endpush
