<x-guest-layout>
    <div class="min-h-screen bg-[#0a0e1a] flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-8 shadow-2xl space-y-6">
                <div class="flex flex-col items-center gap-2">
                    <img src="https://i.postimg.cc/KYTJcmQn/image-removebg-preview.png" alt="Game League" class="w-16 h-16">
                    <h1 class="text-2xl font-bold text-white">Crie sua conta</h1>
                    <p class="text-slate-400 text-sm">Acesse o painel Ranking NEF</p>
                </div>

                @if(session('status'))
                    <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-3 text-center text-sm text-green-400">
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-3 text-sm text-red-400">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Nome completo</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Seu nome" class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-2">E-mail</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" placeholder="voce@empresa.com" class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Senha</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password" placeholder="••••••••" class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-2">Confirmar senha</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="••••••••" class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    </div>

                    <button type="submit" class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg font-medium hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Registrar
                    </button>
                </form>

                <p class="text-center text-sm text-slate-400">
                    Já possui conta?
                    <a href="{{ route('login') }}" class="text-blue-500 hover:text-blue-400 font-medium">
                        Entrar
                    </a>
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
