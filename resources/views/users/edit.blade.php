@extends('layouts.app')

@section('title', 'Editar Usuário')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Editar Usuário</h1>
            <p class="text-slate-400">Atualize as informações do usuário</p>
        </div>

        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
            <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Avatar -->
                <x-avatar-upload name="avatar" :currentAvatar="$user->avatar" label="Foto de Perfil" />

                <!-- Nome -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Nome</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Perfil -->
                <div class="mb-4">
                    <label for="role" class="block text-sm font-medium text-slate-300 mb-2">Perfil</label>
                    <select id="role" name="role" required
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="admin" {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }}>Administrador</option>
                        <option value="supervisor" {{ old('role', $user->role ?? '') === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                        <option value="user" {{ old('role', $user->role ?? '') === 'user' ? 'selected' : '' }}>Usuário</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Setor (supervisor e usuário) -->
                <div id="sector-section" class="mb-4 {{ in_array(old('role', $user->role ?? ''), ['supervisor', 'user']) ? '' : 'hidden' }}">
                    <label for="sector_id" class="block text-sm font-medium text-slate-300 mb-2">Setor</label>
                    <select id="sector_id" name="sector_id" class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione um setor</option>
                        @foreach($sectors ?? [] as $sector)
                            <option value="{{ $sector->id }}" {{ old('sector_id', $user->sector_id ?? '') === $sector->id ? 'selected' : '' }}>
                                {{ $sector->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('sector_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Equipes (apenas para supervisor) -->
                <div id="teams-section" class="mb-6 {{ old('role', $user->role ?? '') === 'supervisor' ? '' : 'hidden' }}">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Equipes Responsáveis</label>
                    <div class="space-y-2 max-h-48 overflow-y-auto bg-slate-800/50 rounded-lg p-3 border border-slate-600">
                        @forelse($teams ?? [] as $team)
                            <label class="flex items-center gap-2 text-slate-300 hover:text-white cursor-pointer">
                                <input type="checkbox" name="teams[]" value="{{ $team->id }}"
                                    {{ in_array($team->id, old('teams', $user->teams->pluck('id')->toArray() ?? [])) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 bg-slate-700 border-slate-600 rounded focus:ring-blue-500 focus:ring-2">
                                <span>{{ $team->name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-slate-400">Nenhuma equipe cadastrada</p>
                        @endforelse
                    </div>
                    @error('teams')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    @error('teams.*')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('users.index') }}" class="px-4 py-2 text-slate-400 hover:text-white">
                        Cancelar
                    </a>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                        Atualizar Usuário
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const teamsSection = document.getElementById('teams-section');
        const sectorSection = document.getElementById('sector-section');
        
        if (roleSelect) {
            function toggleTeamsSection() {
                const role = roleSelect.value;
                if (sectorSection) {
                    if (role === 'supervisor' || role === 'user') {
                        sectorSection.classList.remove('hidden');
                    } else {
                        sectorSection.classList.add('hidden');
                        const sectorSelect = sectorSection.querySelector('select');
                        if (sectorSelect) {
                            sectorSelect.value = '';
                        }
                    }
                }

                if (!teamsSection) {
                    return;
                }
                if (role === 'supervisor') {
                    teamsSection.classList.remove('hidden');
                    // Tornar obrigatório pelo menos uma equipe
                    const checkboxes = teamsSection.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach(cb => {
                        cb.setAttribute('data-required', 'true');
                    });
                } else {
                    teamsSection.classList.add('hidden');
                    // Desmarcar todas as equipes se não for supervisor
                    const checkboxes = teamsSection.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach(cb => {
                        cb.checked = false;
                        cb.removeAttribute('data-required');
                    });
                }
            }

            roleSelect.addEventListener('change', toggleTeamsSection);
        }
    });
</script>
@endpush

@endsection
