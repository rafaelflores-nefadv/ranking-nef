<x-guest-layout>
    <div class="min-h-screen bg-[#0a0e1a] flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-8 shadow-2xl">
                <div class="flex flex-col items-center mb-8">
                    <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2">Ranking de {{ $saleTermLower ?? 'vendas' }}</h1>
                    <p class="text-slate-400 text-sm">Acesse sua conta</p>
                </div>

                <!-- Session Status -->
                @if(session('status'))
                <div class="mb-4 bg-green-500/10 border border-green-500/20 rounded-lg p-3">
                    <p class="text-green-400 text-sm text-center">{{ session('status') }}</p>
                </div>
                @endif

                <!-- Validation Errors -->
                @if($errors->any())
                <div class="mb-4 bg-red-500/10 border border-red-500/20 rounded-lg p-3">
                    <ul class="list-disc list-inside text-red-400 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-2">E-mail</label>
                        <input id="email" class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="seu@email.com" />
                        @error('email')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Senha</label>
                        <input id="password" class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                        @error('password')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="block mb-4">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded border-slate-600 bg-slate-800 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                            <span class="ms-2 text-sm text-slate-400">Lembrar-me</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between mb-4">
                        @if (Route::has('password.request'))
                            <a class="underline text-sm text-blue-400 hover:text-blue-300" href="{{ route('password.request') }}">
                                Esqueceu sua senha?
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="w-full px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 font-medium">
                        Entrar
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-slate-400 text-sm">
                        Não tem uma conta? 
                        <a href="{{ route('register') }}" class="text-blue-500 hover:text-blue-400 font-medium">
                            Cadastre-se
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
