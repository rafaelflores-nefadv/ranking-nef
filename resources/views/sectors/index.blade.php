@extends('layouts.app')

@section('title', 'Setores')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Setores</h1>
                <p class="text-slate-400">Gerencie os setores do sistema</p>
            </div>
            <a href="{{ route('sectors.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                Novo Setor
            </a>
        </div>

        @if(session('success'))
        <div class="mb-4 bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-4">
            <p class="text-emerald-400">{{ session('success') }}</p>
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

        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Slug</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($sectors as $sector)
                        <tr class="hover:bg-slate-800/30">
                            <td class="px-6 py-4 whitespace-nowrap text-white">{{ $sector->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-400">{{ $sector->slug }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sector->is_active ? 'bg-green-600/20 text-green-400' : 'bg-red-600/20 text-red-400' }}">
                                    {{ $sector->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('sectors.edit', $sector) }}" class="text-yellow-400 hover:text-yellow-300 transition-colors" title="Editar setor">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('sectors.toggle-status', $sector) }}" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-slate-300 hover:text-white transition-colors" title="Alternar status">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-400">
                                Nenhum setor cadastrado
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($sectors->hasPages())
            <div class="px-6 py-4 border-t border-slate-700/50">
                {{ $sectors->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
