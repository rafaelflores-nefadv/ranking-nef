@extends('layouts.app')

@section('title', 'Integrações API')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Integrações API</h1>
                <p class="text-slate-400">Gerencie as integrações e tokens de acesso à API</p>
            </div>
            <a href="{{ route('settings.api.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                Nova Integração
            </a>
        </div>

        <!-- Mensagens -->
        @if(session('status'))
        <div class="mb-4 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm px-4 py-3 rounded-lg">
            {{ session('status') }}
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

        <!-- Lista de Integrações -->
        <div class="space-y-4">
            @forelse($integrations as $integration)
            <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-xl font-bold text-white">{{ $integration->name }}</h3>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $integration->is_active ? 'bg-green-600/20 text-green-400' : 'bg-red-600/20 text-red-400' }}">
                                {{ $integration->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                        @if($integration->description)
                        <p class="text-slate-300 mb-2">{{ $integration->description }}</p>
                        @endif
                        @if($integration->system)
                        <p class="text-slate-400 text-sm">Sistema/Fornecedor: {{ $integration->system }}</p>
                        @endif
                        <p class="text-slate-400 text-xs mt-2">
                            Criada em: {{ $integration->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('settings.api.edit', $integration) }}" class="px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                            Gerenciar
                        </a>
                        <form method="POST" action="{{ route('settings.api.destroy', $integration) }}" class="inline-block" onsubmit="return confirm('Tem certeza que deseja excluir esta integração? Todos os tokens associados serão removidos.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold">
                                Excluir
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Tokens -->
                <div class="border-t border-slate-700/50 pt-4 mt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-slate-300">Tokens de Acesso</h4>
                        <span class="text-xs text-slate-400">
                            {{ $integration->tokens->count() }} token(s) - {{ $integration->activeTokens->count() }} ativo(s)
                        </span>
                    </div>
                    @if($integration->tokens->isEmpty())
                    <p class="text-slate-400 text-sm">Nenhum token cadastrado</p>
                    @else
                    <div class="space-y-2">
                        @foreach($integration->tokens->take(3) as $token)
                        <div class="flex items-center justify-between p-2 bg-slate-800/50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <code class="text-xs text-slate-300 font-mono">{{ substr($token->token, 0, 20) }}...</code>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $token->is_active ? 'bg-green-600/20 text-green-400' : 'bg-red-600/20 text-red-400' }}">
                                    {{ $token->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                                @if($token->last_used_at)
                                <span class="text-xs text-slate-400">Último uso: {{ $token->last_used_at->format('d/m/Y H:i') }}</span>
                                @else
                                <span class="text-xs text-slate-400">Nunca usado</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                        @if($integration->tokens->count() > 3)
                        <p class="text-xs text-slate-400 text-center">E mais {{ $integration->tokens->count() - 3 }} token(s)...</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-8 text-center">
                <p class="text-slate-400">Nenhuma integração cadastrada</p>
                <a href="{{ route('settings.api.create') }}" class="inline-block mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Criar primeira integração
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
