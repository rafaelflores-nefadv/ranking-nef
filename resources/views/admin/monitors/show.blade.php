@extends('layouts.app')

@section('title', 'Monitor: ' . $monitor->name)

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">{{ $monitor->name }}</h1>
                <p class="text-slate-400">{{ $monitor->description ?? 'Sem descrição' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.monitors.edit', $monitor) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Editar
                </a>
                <a href="{{ route('admin.monitors.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg">
                    Voltar
                </a>
            </div>
        </div>

        <!-- Informações -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Informações Básicas -->
            <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
                <h2 class="text-xl font-bold text-white mb-4">Informações Básicas</h2>
                <div class="space-y-3">
                    <div>
                        <span class="text-slate-400 text-sm">Nome:</span>
                        <p class="text-white font-medium">{{ $monitor->name }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 text-sm">Slug:</span>
                        <p class="text-white font-mono text-sm">{{ $monitor->slug }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 text-sm">Status:</span>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $monitor->is_active ? 'bg-green-600/20 text-green-400' : 'bg-red-600/20 text-red-400' }}">
                            {{ $monitor->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>
                    @if($monitor->description)
                    <div>
                        <span class="text-slate-400 text-sm">Descrição:</span>
                        <p class="text-white">{{ $monitor->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- URL Pública -->
            <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
                <h2 class="text-xl font-bold text-white mb-4">URL Pública</h2>
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <input type="text" value="{{ $publicUrl }}" readonly
                               class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white font-mono text-sm focus:outline-none">
                        <button onclick="copyToClipboard('{{ $publicUrl }}')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors" title="Copiar URL">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ $publicUrl }}" target="_blank" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-center transition-colors">
                            Abrir em Nova Aba
                        </a>
                        <a href="{{ route('monitor.show', $monitor->slug) }}" target="_blank" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors" title="Preview">
                            Preview
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configurações -->
        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Configurações</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @php
                    $settings = $monitor->getMergedSettings();
                @endphp
                <div>
                    <span class="text-slate-400 text-sm">Intervalo de Atualização:</span>
                    <p class="text-white font-medium">{{ number_format($settings['refresh_interval'] / 1000, 0) }} segundos</p>
                </div>
                <div>
                    <span class="text-slate-400 text-sm">Rotação Automática:</span>
                    <p class="text-white font-medium">{{ $settings['auto_rotate_teams'] ? 'Habilitada' : 'Desabilitada' }}</p>
                </div>
                <div>
                    <span class="text-slate-400 text-sm">Notificações:</span>
                    <p class="text-white font-medium">{{ $settings['notifications_enabled'] ? 'Habilitadas' : 'Desabilitadas' }}</p>
                </div>
                <div>
                    <span class="text-slate-400 text-sm">Som:</span>
                    <p class="text-white font-medium">{{ $settings['sound_enabled'] ? 'Habilitado' : 'Desabilitado' }}</p>
                </div>
                <div>
                    <span class="text-slate-400 text-sm">Escala de Fonte:</span>
                    <p class="text-white font-medium">{{ number_format($settings['font_scale'], 1) }}x</p>
                </div>
                <div>
                    <span class="text-slate-400 text-sm">Equipes Configuradas:</span>
                    <p class="text-white font-medium">
                        @if(empty($settings['teams']))
                            Todas as equipes
                        @else
                            {{ count($settings['teams']) }} equipe(s)
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('URL copiada para a área de transferência!');
        }).catch(err => {
            console.error('Erro ao copiar:', err);
        });
    }
</script>
@endpush
@endsection
