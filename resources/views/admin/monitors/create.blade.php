@extends('layouts.app')

@section('title', 'Criar Monitor')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Criar Monitor</h1>
            <p class="text-slate-400">Configure um novo monitor de exibição pública</p>
        </div>

        <!-- Mensagens -->
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

        <!-- Formulário -->
        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
            <form method="POST" action="{{ route('admin.monitors.store') }}">
                @csrf

                <div class="space-y-6">
                    <!-- Nome -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Nome do Monitor *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ex: Monitor TV Sala Principal">
                        <p class="mt-1 text-xs text-slate-400">Nome identificador do monitor</p>
                    </div>

                    <!-- Slug -->
                    <div>
                        <label for="slug" class="block text-sm font-medium text-slate-300 mb-2">Slug (URL)</label>
                        <input type="text" id="slug" name="slug" value="{{ old('slug') }}"
                               class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ex: tv-sala-principal (gerado automaticamente se vazio)">
                        <p class="mt-1 text-xs text-slate-400">Slug único para a URL pública. Deixe vazio para gerar automaticamente.</p>
                    </div>

                    <!-- Descrição -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-slate-300 mb-2">Descrição</label>
                        <textarea id="description" name="description" rows="3"
                                  class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Descrição opcional do monitor">{{ old('description') }}</textarea>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                   class="w-4 h-4 bg-slate-800 border-slate-700 rounded text-blue-600 focus:ring-blue-500">
                            <span class="text-sm font-medium text-slate-300">Monitor ativo</span>
                        </label>
                        <p class="mt-1 text-xs text-slate-400 ml-6">Monitores inativos não são acessíveis publicamente</p>
                    </div>

                    <div class="border-t border-slate-700/50 pt-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Configurações</h3>

                        <!-- Intervalo de atualização -->
                        <div class="mb-4">
                            <label for="refresh_interval" class="block text-sm font-medium text-slate-300 mb-2">Intervalo de Atualização (ms) *</label>
                            <input type="number" id="refresh_interval" name="refresh_interval" value="{{ old('refresh_interval', 30000) }}" min="5000" step="1000" required
                                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="mt-1 text-xs text-slate-400">Intervalo em milissegundos (ex: 30000 = 30 segundos)</p>
                        </div>

                        <!-- Rotação automática de equipes -->
                        <div class="mb-4">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="auto_rotate_teams" value="1" {{ old('auto_rotate_teams', true) ? 'checked' : '' }}
                                       class="w-4 h-4 bg-slate-800 border-slate-700 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-medium text-slate-300">Rotação automática de equipes</span>
                            </label>
                            <p class="mt-1 text-xs text-slate-400 ml-6">Alterna automaticamente entre equipes no intervalo configurado</p>
                        </div>

                        <!-- Equipes permitidas -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Equipes para Exibir</label>
                            <div class="space-y-2 max-h-40 overflow-y-auto bg-slate-800/50 rounded-lg p-3 border border-slate-700/50">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" id="select-all-teams" 
                                           class="w-4 h-4 bg-slate-800 border-slate-700 rounded text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-slate-300">Selecionar todas (deixe desmarcado para todas)</span>
                                </label>
                                @foreach($teams as $team)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="teams[]" value="{{ $team->id }}" {{ in_array($team->id, old('teams', [])) ? 'checked' : '' }}
                                           class="w-4 h-4 bg-slate-800 border-slate-700 rounded text-blue-600 focus:ring-blue-500 team-checkbox">
                                    <span class="text-sm text-slate-300">{{ $team->name }}</span>
                                </label>
                                @endforeach
                            </div>
                            <p class="mt-1 text-xs text-slate-400">Selecione equipes específicas ou deixe vazio para mostrar todas</p>
                        </div>

                        <!-- Notificações -->
                        <div class="mb-4">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="notifications_enabled" value="1" {{ old('notifications_enabled', false) ? 'checked' : '' }}
                                       class="w-4 h-4 bg-slate-800 border-slate-700 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-medium text-slate-300">Notificações habilitadas</span>
                            </label>
                            <p class="mt-1 text-xs text-slate-400 ml-6">Exibir notificações de vendas no monitor</p>
                        </div>

                        <!-- Som -->
                        <div class="mb-4">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="sound_enabled" value="1" {{ old('sound_enabled', false) ? 'checked' : '' }}
                                       class="w-4 h-4 bg-slate-800 border-slate-700 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-medium text-slate-300">Som habilitado</span>
                            </label>
                            <p class="mt-1 text-xs text-slate-400 ml-6">Reproduzir sons nas notificações</p>
                        </div>

                        <!-- Voz -->
                        <div class="mb-4">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="voice_enabled" value="1" {{ old('voice_enabled', false) ? 'checked' : '' }}
                                       class="w-4 h-4 bg-slate-800 border-slate-700 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-medium text-slate-300">Leitura por voz habilitada</span>
                            </label>
                            <p class="mt-1 text-xs text-slate-400 ml-6">Reproduzir leitura do ranking por voz no navegador</p>
                        </div>

                        <!-- Escala de fonte -->
                        <div>
                            <label for="font_scale" class="block text-sm font-medium text-slate-300 mb-2">Escala de Fonte (TV)</label>
                            <input type="number" id="font_scale" name="font_scale" value="{{ old('font_scale', 1.0) }}" min="0.5" max="3.0" step="0.1"
                                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="mt-1 text-xs text-slate-400">Multiplicador de tamanho de fonte para exibição em TV (1.0 = normal, 2.0 = dobro)</p>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-end gap-4 mt-6 pt-6 border-t border-slate-700/50">
                    <a href="{{ route('admin.monitors.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 transition-colors">
                        Criar Monitor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Gerar slug automaticamente a partir do nome
    document.getElementById('name').addEventListener('input', function() {
        const slugInput = document.getElementById('slug');
        if (!slugInput.value || slugInput.dataset.autoGenerated) {
            const slug = this.value.toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.value = slug;
            slugInput.dataset.autoGenerated = 'true';
        }
    });

    // Selecionar/deselecionar todas as equipes
    document.getElementById('select-all-teams').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.team-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    // Atualizar checkbox "selecionar todas"
    document.querySelectorAll('.team-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const allCheckboxes = document.querySelectorAll('.team-checkbox');
            const checked = Array.from(allCheckboxes).filter(cb => cb.checked);
            document.getElementById('select-all-teams').checked = checked.length === allCheckboxes.length;
        });
    });
</script>
@endpush
@endsection
