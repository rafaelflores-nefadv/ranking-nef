@extends('layouts.app')

@section('title', 'Importar Vendedores')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Importar Vendedores</h1>
            <p class="text-slate-400">Importe vendedores de uma planilha Excel (.xlsx ou .xls)</p>
        </div>

        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
            <!-- Mensagens -->
            @if(session('success'))
            <div class="mb-4 bg-green-500/10 border border-green-500/20 rounded-lg p-4">
                <p class="text-green-400">{{ session('success') }}</p>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 bg-red-500/10 border border-red-500/20 rounded-lg p-4">
                <p class="text-red-400">{{ session('error') }}</p>
            </div>
            @endif

            @if(isset($errors) && $errors->any())
            <div class="mb-4 bg-red-500/10 border border-red-500/20 rounded-lg p-4">
                <ul class="list-disc list-inside text-red-400">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Instruções -->
            <div class="mb-6 bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                <h3 class="text-white font-semibold mb-2">Formato da Planilha</h3>
                <p class="text-slate-300 text-sm mb-2">A planilha deve conter as seguintes colunas na <strong>primeira linha (cabeçalho)</strong>:</p>
                <ul class="list-disc list-inside text-slate-300 text-sm space-y-1">
                    <li><strong>nome</strong> - Nome completo do vendedor</li>
                    <li><strong>e-mail</strong> - Email do vendedor (deve ser único)</li>
                </ul>
                <p class="text-slate-300 text-sm mt-3">
                    <strong>Importante:</strong> A primeira linha deve conter exatamente os cabeçalhos "nome" e "e-mail". 
                    As linhas seguintes devem conter os dados dos vendedores.
                </p>
                <p class="text-slate-300 text-sm mt-2">
                    <strong>Nota:</strong> Os vendedores serão importados com status ativo e vinculados à temporada atual ativa. 
                    Eles não serão atribuídos a nenhuma equipe inicialmente.
                </p>
            </div>

            <!-- Formulário -->
            <form method="POST" action="{{ route('sellers.import.process') }}" enctype="multipart/form-data">
                @csrf

                <!-- Arquivo -->
                <div class="mb-6">
                    <label for="file" class="block text-sm font-medium text-slate-300 mb-2">Arquivo Excel</label>
                    <input type="file" id="file" name="file" accept=".xlsx,.xls" required
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 file:cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('file')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('sellers.index') }}" class="px-4 py-2 text-slate-400 hover:text-white">
                        Cancelar
                    </a>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                        Importar Vendedores
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
