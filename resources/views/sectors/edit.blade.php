@extends('layouts.app')

@section('title', 'Editar Setor')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Editar Setor</h1>
            <p class="text-slate-400">Atualize as informações do setor</p>
        </div>

        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
            <form method="POST" action="{{ route('sectors.update', $sector) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Nome *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $sector->name) }}" required
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="slug" class="block text-sm font-medium text-slate-300 mb-2">Slug</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $sector->slug) }}"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('slug')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-slate-300 mb-2">Descrição</label>
                    <textarea id="description" name="description" rows="3"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $sector->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $sector->is_active) ? 'checked' : '' }}
                            class="h-5 w-5 accent-blue-600">
                        <span class="text-slate-300">Ativo</span>
                    </label>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
                        Salvar Alterações
                    </button>
                    <a href="{{ route('sectors.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-semibold">
                        Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
