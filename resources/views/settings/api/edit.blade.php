@extends('layouts.app')

@section('title', 'Editar Integração API')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Editar Integração API</h1>
            <p class="text-slate-400">Gerencie os dados da integração e tokens de acesso</p>
        </div>

        <!-- Mensagens -->
        @if(session('status'))
        <div class="mb-4 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm px-4 py-3 rounded-lg">
            {{ session('status') }}
        </div>
        @endif

        @if(session('token_generated'))
        <div class="mb-4 bg-yellow-900/30 border border-yellow-700/40 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-yellow-200 font-semibold">Token gerado com sucesso!</h4>
                <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-yellow-400 hover:text-yellow-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p class="text-yellow-200 text-sm mb-3">⚠️ <strong>IMPORTANTE:</strong> Guarde o token e o secret em um local seguro. O secret não será exibido novamente.</p>
            <div class="space-y-3 bg-slate-900/50 p-4 rounded-lg">
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Token:</label>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 px-3 py-2 bg-slate-800 border border-slate-700 rounded text-sm text-white font-mono break-all">{{ session('token_generated')['token'] }}</code>
                        <button type="button" onclick="copyToClipboard('{{ session('token_generated')['token'] }}', 'token-copy-btn')" id="token-copy-btn" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg font-semibold">
                            Copiar
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Secret:</label>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 px-3 py-2 bg-slate-800 border border-slate-700 rounded text-sm text-white font-mono break-all">{{ session('token_generated')['secret'] }}</code>
                        <button type="button" onclick="copyToClipboard('{{ session('token_generated')['secret'] }}', 'secret-copy-btn')" id="secret-copy-btn" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg font-semibold">
                            Copiar
                        </button>
                    </div>
                </div>
            </div>
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

        <div class="space-y-6">
            <!-- Formulário de Edição -->
            <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
                <h2 class="text-xl font-bold text-white mb-4">Dados da Integração</h2>
                
                <form method="POST" action="{{ route('settings.api.update', $apiIntegration) }}">
                    @csrf
                    @method('PUT')

                    <!-- Setor -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Setor *</label>
                        <select name="sector_id" required
                            class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($sectors ?? [] as $sector)
                                <option value="{{ $sector->id }}" {{ $sector->id === $apiIntegration->sector_id ? 'selected' : '' }}>
                                    {{ $sector->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('sector_id')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nome -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Nome da Integração *</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $apiIntegration->name) }}" required
                            placeholder="Ex: ERP X, CRM Y"
                            class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Descrição -->
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-slate-300 mb-2">Descrição</label>
                        <textarea id="description" name="description" rows="3"
                            placeholder="Descrição opcional da integração"
                            class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $apiIntegration->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sistema/Fornecedor -->
                    <div class="mb-4">
                        <label for="system" class="block text-sm font-medium text-slate-300 mb-2">Sistema/Fornecedor</label>
                        <input type="text" id="system" name="system" value="{{ old('system', $apiIntegration->system) }}"
                            placeholder="Ex: ERP X, Sistema Y"
                            class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('system')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="mb-6">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $apiIntegration->is_active) ? 'checked' : '' }}
                                class="h-5 w-5 accent-blue-600">
                            <span class="text-slate-300">Ativo</span>
                        </label>
                        <p class="mt-1 text-xs text-slate-400">Integrações inativas não podem usar tokens</p>
                    </div>

                    <!-- Botões -->
                    <div class="flex items-center gap-3">
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
                            Salvar Alterações
                        </button>
                        <a href="{{ route('settings.api.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-semibold">
                            Voltar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tokens -->
            <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
                <div class="flex flex-col gap-4 mb-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-white">Tokens de Acesso</h2>
                    </div>
                    <form method="POST" action="{{ route('settings.api.tokens.generate', $apiIntegration) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Setor</label>
                            <div class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white text-sm">
                                {{ $apiIntegration->sector?->name ?? '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Identificador *</label>
                            <select name="collaborator_identifier_type" required class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white text-sm">
                                <option value="email">Email</option>
                                <option value="external_code">Código externo</option>
                            </select>
                            @error('collaborator_identifier_type')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold text-sm">
                                Gerar Novo Token
                            </button>
                        </div>
                    </form>
                </div>

                @if($apiIntegration->tokens->isEmpty())
                <p class="text-slate-400 text-center py-8">Nenhum token cadastrado. Gere um token para começar.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Token</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Setor</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Identificador</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Criado em</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Último uso</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            @foreach($apiIntegration->tokens as $token)
                            <tr class="hover:bg-slate-800/30">
                                <td class="px-4 py-4">
                                    <code class="text-xs text-slate-300 font-mono">{{ substr($token->token, 0, 20) }}...</code>
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-300">
                                    {{ $token->sector?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-300">
                                    {{ $token->collaborator_identifier_type === 'external_code' ? 'Código externo' : 'Email' }}
                                </td>
                                <td class="px-4 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $token->is_active ? 'bg-green-600/20 text-green-400' : 'bg-red-600/20 text-red-400' }}">
                                        {{ $token->is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-400">
                                    {{ $token->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-400">
                                    {{ $token->last_used_at ? $token->last_used_at->format('d/m/Y H:i') : 'Nunca usado' }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center gap-2 justify-end">
                                        <form method="POST" action="{{ route('settings.api.tokens.toggle-status', [$apiIntegration, $token]) }}" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-3 py-1 text-xs rounded-lg {{ $token->is_active ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' }} text-white font-semibold">
                                                {{ $token->is_active ? 'Desativar' : 'Ativar' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('settings.api.tokens.regenerate', [$apiIntegration, $token]) }}" class="inline-block" onsubmit="return confirm('Tem certeza que deseja regenerar este token? O token atual será desativado e um novo será criado.');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 text-xs rounded-lg bg-yellow-600 hover:bg-yellow-700 text-white font-semibold">
                                                Regenerar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            <!-- Informações de uso -->
            <div class="bg-slate-800/50 rounded-xl border border-slate-700/50 p-4">
                <h3 class="text-sm font-semibold text-white mb-2">Como usar o token</h3>
                <div class="text-xs text-slate-300 space-y-2">
                    <p>Use o token no header <code class="px-1 py-0.5 bg-slate-900 rounded">Authorization: Bearer {TOKEN}</code></p>
                    <p>Endpoint: <code class="px-1 py-0.5 bg-slate-900 rounded">POST /api/webhook/occurrences</code></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text, buttonId) {
    navigator.clipboard.writeText(text).then(function() {
        const btn = document.getElementById(buttonId);
        const originalText = btn.textContent;
        btn.textContent = 'Copiado!';
        btn.classList.add('bg-green-600', 'hover:bg-green-700');
        btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        setTimeout(function() {
            btn.textContent = originalText;
            btn.classList.remove('bg-green-600', 'hover:bg-green-700');
            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }, 2000);
    }).catch(function(err) {
        alert('Erro ao copiar: ' + err);
    });
}
</script>
@endsection
