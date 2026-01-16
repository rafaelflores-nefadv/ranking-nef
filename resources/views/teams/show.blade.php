@extends('layouts.app')

@section('title', 'Detalhes da Equipe')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">{{ $team->name }}</h1>
            <p class="text-slate-400">Detalhes da equipe</p>
        </div>

        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6 mb-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-400 mb-1">Nome</label>
                <p class="text-white text-xl">{{ $team->name }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">Colaboradores</label>
                <p class="text-white">{{ $team->sellers->count() }} colaboradores</p>
            </div>
        </div>

        @if($team->sellers->count() > 0)
        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 overflow-hidden">
            <div class="p-6 border-b border-slate-700/50">
                <h2 class="text-xl font-bold text-white">Colaboradores da Equipe</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Pontos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @foreach($team->sellers as $seller)
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
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="mt-6 flex items-center justify-end gap-4">
            <a href="{{ route('teams.index') }}" class="px-4 py-2 text-slate-400 hover:text-white">
                Voltar
            </a>
            @can('update', $team)
            <a href="{{ route('teams.edit', $team) }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                Editar
            </a>
            @endcan
        </div>
    </div>
</div>
@endsection
