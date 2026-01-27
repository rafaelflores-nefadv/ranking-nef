@extends('layouts.app')

@section('title', 'Detalhes do Usuário')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">{{ $user->name }}</h1>
            <p class="text-slate-400">Detalhes do usuário</p>
        </div>

        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
            <!-- Foto de Perfil -->
            <div class="mb-6 flex items-center justify-center">
                <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff&size=200' }}" 
                     alt="{{ $user->name }}" 
                     class="w-32 h-32 rounded-full object-cover border-4 border-slate-600 shadow-lg">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Nome</label>
                    <p class="text-white">{{ $user->name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Email</label>
                    <p class="text-white">{{ $user->email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Perfil</label>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-purple-600/20 text-purple-400' : ($user->role === 'supervisor' ? 'bg-blue-600/20 text-blue-400' : 'bg-slate-600/20 text-slate-300') }}">
                        {{ $user->role === 'admin' ? 'Administrador' : ($user->role === 'supervisor' ? 'Supervisor' : 'Usuário') }}
                    </span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Setor</label>
                    <p class="text-white">{{ $user->sector?->name ?? '—' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Status</label>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $user->is_active ? 'bg-green-600/20 text-green-400' : 'bg-red-600/20 text-red-400' }}">
                        {{ $user->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                </div>

                @if($user->role === 'supervisor' && $user->teams->count() > 0)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Equipes Responsáveis</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($user->teams as $team)
                            <span class="px-3 py-1 bg-blue-600/20 text-blue-400 rounded-full text-sm">
                                {{ $team->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="mt-6 flex items-center justify-end gap-4">
                <a href="{{ route('users.index') }}" class="px-4 py-2 text-slate-400 hover:text-white">
                    Voltar
                </a>
                @if(auth()->user() && auth()->user()->role === 'admin')
                <a href="{{ route('users.edit', $user) }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                    Editar
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
