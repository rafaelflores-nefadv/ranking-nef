@extends('layouts.app')

@section('title', 'Vendedores')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Vendedores</h1>
                <p class="text-slate-400">Gerencie os vendedores do sistema</p>
            </div>
            @can('create', App\Models\Seller::class)
            <a href="{{ route('sellers.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                Novo Vendedor
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

        <!-- Tabela -->
        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Email</th>
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
                                <div class="text-white font-medium">{{ $seller->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-slate-400">{{ $seller->email }}</div>
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
                                <div class="flex items-center gap-2">
                                    @can('view', $seller)
                                    <a href="{{ route('sellers.show', $seller) }}" class="text-blue-400 hover:text-blue-300">Ver</a>
                                    @endcan
                                    @can('update', $seller)
                                    <a href="{{ route('sellers.edit', $seller) }}" class="text-yellow-400 hover:text-yellow-300">Editar</a>
                                    @endcan
                                    @can('delete', $seller)
                                    <form method="POST" action="{{ route('sellers.destroy', $seller) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este vendedor?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300">Excluir</button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">
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
