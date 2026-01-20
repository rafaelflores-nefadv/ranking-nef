@extends('layouts.app')

@section('title', 'Monitores')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Monitores</h1>
                <p class="text-slate-400">Gerencie os monitores de exibição pública</p>
            </div>
            @if(auth()->user() && auth()->user()->role === 'admin')
            <a href="{{ route('admin.monitors.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                Novo Monitor
            </a>
            @endif
        </div>

        <!-- Mensagens -->
        @if(session('success'))
        <div class="mb-4 bg-green-500/10 border border-green-500/20 rounded-lg p-4">
            <p class="text-green-400">{{ session('success') }}</p>
        </div>
        @endif

        @if(session('error') || (isset($errors) && $errors->any()))
        <div class="mb-4 bg-red-500/10 border border-red-500/20 rounded-lg p-4">
            @if(session('error'))
                <p class="text-red-400">{{ session('error') }}</p>
            @endif
            @if(isset($errors))
                @foreach($errors->all() as $error)
                    <p class="text-red-400">{{ $error }}</p>
                @endforeach
            @endif
        </div>
        @endif

        <!-- Tabela -->
        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Slug</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">URL Pública</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($monitors as $monitor)
                        <tr class="hover:bg-slate-800/30">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-white font-medium">{{ $monitor->name }}</div>
                                @if($monitor->description)
                                    <div class="text-slate-400 text-sm mt-1">{{ Str::limit($monitor->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-slate-400 font-mono text-sm">{{ $monitor->slug }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('monitor.show', $monitor->slug) }}" target="_blank" class="text-blue-400 hover:text-blue-300 text-sm font-mono">
                                        /monitor/{{ $monitor->slug }}
                                    </a>
                                    <button onclick="copyToClipboard('{{ route('monitor.show', $monitor->slug) }}')" class="text-slate-400 hover:text-slate-300 transition-colors" title="Copiar URL">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $monitor->is_active ? 'bg-green-600/20 text-green-400' : 'bg-red-600/20 text-red-400' }}">
                                    {{ $monitor->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-3">
                                    @if(auth()->user() && auth()->user()->role === 'admin')
                                    <a href="{{ route('admin.monitors.show', $monitor) }}" class="text-blue-400 hover:text-blue-300 transition-colors" title="Ver monitor">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.monitors.edit', $monitor) }}" class="text-yellow-400 hover:text-yellow-300 transition-colors" title="Editar monitor">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.monitors.toggle-status', $monitor) }}" class="inline-block" style="display: inline-block;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="{{ $monitor->is_active ? 'text-orange-400 hover:text-orange-300' : 'text-green-400 hover:text-green-300' }} transition-colors" title="{{ $monitor->is_active ? 'Desativar monitor' : 'Ativar monitor' }}">
                                            @if($monitor->is_active)
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.monitors.destroy', $monitor) }}" class="inline-block" style="display: inline-block;" onsubmit="return handleDeleteConfirm(event, 'Tem certeza que deseja excluir este monitor?', 'Excluir monitor');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 transition-colors" title="Excluir monitor">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                Nenhum monitor encontrado. <a href="{{ route('admin.monitors.create') }}" class="text-blue-400 hover:text-blue-300">Criar primeiro monitor</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            // Mostrar feedback visual
            alert('URL copiada para a área de transferência!');
        }).catch(err => {
            console.error('Erro ao copiar:', err);
        });
    }
</script>
@endpush
@endsection
