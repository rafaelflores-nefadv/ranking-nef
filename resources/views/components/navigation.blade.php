@php
    $user = auth()->user();
    $isAdmin = $user && $user->role === 'admin';
    $isSupervisor = $user && $user->role === 'supervisor';
    $canViewSellers = $user && in_array($user->role, ['admin', 'supervisor', 'user']);
    $canViewTeams = $user && in_array($user->role, ['admin', 'supervisor']);
@endphp

<nav class="bg-[#0d1117] border-b border-slate-800/50 backdrop-blur-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                        </div>
                        <span class="text-white font-bold">Ranking NEF</span>
                    </a>
                </div>

                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('dashboard') ? 'border-blue-500 text-white' : 'border-transparent text-slate-400 hover:text-white hover:border-slate-300' }} text-sm font-medium">
                        <span class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z"></path>
                            </svg>
                            Dashboard
                        </span>
                    </a>
                    @if($isAdmin)
                        <a href="{{ route('sectors.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('sectors.*') ? 'border-blue-500 text-white' : 'border-transparent text-slate-400 hover:text-white hover:border-slate-300' }} text-sm font-medium">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                Setores
                            </span>
                        </a>
                    @endif
                    @if($user && in_array($user->role, ['admin', 'supervisor']))
                        <a href="{{ route('teams.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('teams.*') ? 'border-blue-500 text-white' : 'border-transparent text-slate-400 hover:text-white hover:border-slate-300' }} text-sm font-medium">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1m15-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM21 20v-1a4 4 0 0 0-3-3.87"></path>
                                </svg>
                                Equipes
                            </span>
                        </a>
                    @endif
                    @if($user && in_array($user->role, ['admin', 'supervisor', 'user']))
                        <a href="{{ route('sellers.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('sellers.*') ? 'border-blue-500 text-white' : 'border-transparent text-slate-400 hover:text-white hover:border-slate-300' }} text-sm font-medium">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 14a4 4 0 1 0-8 0m12 6v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"></path>
                                </svg>
                                Vendedores
                            </span>
                        </a>
                    @endif
                    @can('viewAny', App\Models\Goal::class)
                        <a href="{{ route('goals.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('goals.*') ? 'border-blue-500 text-white' : 'border-transparent text-slate-400 hover:text-white hover:border-slate-300' }} text-sm font-medium">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Metas
                            </span>
                        </a>
                    @endcan
                    @if($user && in_array($user->role, ['admin', 'supervisor', 'user']))
                        <details class="relative group inline-flex items-center">
                            <summary class="list-none cursor-pointer inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('reports.*') ? 'border-blue-500 text-white' : 'border-transparent text-slate-400 hover:text-white hover:border-slate-300' }} text-sm font-medium">
                                <span class="inline-flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"></path>
                                    </svg>
                                    Relatórios
                                    <svg class="w-3 h-3 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </span>
                            </summary>
                            <div class="absolute left-0 top-full mt-2 w-56 rounded-xl bg-slate-900/95 border border-slate-700/60 shadow-xl backdrop-blur-sm py-2 z-50">
                                <a href="{{ route('reports.ranking-general') }}" class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-800/70 transition-colors">
                                    Ranking Geral
                                </a>
                                <a href="{{ route('reports.ranking-team') }}" class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-800/70 transition-colors">
                                    Ranking por Equipe
                                </a>
                                <a href="{{ route('reports.score-evolution') }}" class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-800/70 transition-colors">
                                    Evolução de Pontuação
                                </a>
                                @if($user && in_array($user->role, ['admin', 'supervisor']))
                                    <a href="{{ route('reports.occurrences') }}" class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-800/70 transition-colors">
                                        Ocorrências
                                    </a>
                                @endif
                                <a href="{{ route('reports.gamification') }}" class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-800/70 transition-colors">
                                    Gamificação
                                </a>
                            </div>
                        </details>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if($isAdmin && isset($sectorOptions) && $sectorOptions->isNotEmpty())
                    <div class="hidden lg:flex items-center gap-2">
                        <span class="text-xs text-slate-400">Setor</span>
                        <select id="sector-selector" class="w-36 bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($sectorOptions as $sector)
                                <option value="{{ $sector->id }}" {{ ($currentSectorId ?? null) === $sector->id ? 'selected' : '' }}>
                                    {{ $sector->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            const selector = document.getElementById('sector-selector');
                            if (!selector) return;
                            selector.addEventListener('change', (event) => {
                                const url = new URL(window.location.href);
                                url.searchParams.set('sector', event.target.value);
                                window.location.href = url.toString();
                            });
                        });
                    </script>
                @endif
                <details class="relative" id="notifications-dropdown">
                    <summary class="list-none cursor-pointer p-2 hover:bg-slate-800/60 rounded-lg transition-colors text-slate-300 hover:text-white relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.268 21a2 2 0 0 0 3.464 0"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"></path>
                        </svg>
                        <span id="notifications-badge" class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] leading-[18px] text-center"></span>
                    </summary>
                    <div class="absolute right-0 mt-2 w-64 rounded-xl bg-slate-900/95 border border-slate-700/60 shadow-xl backdrop-blur-sm py-2">
                        <div class="px-4 py-2 text-xs uppercase tracking-wide text-slate-500">Notificações</div>
                        <div id="notifications-list" data-sale-term="{{ $saleTerm ?? 'Venda' }}" class="max-h-72 overflow-y-auto"></div>
                        <div id="notifications-empty" class="px-4 py-3 text-sm text-slate-400">
                            Nenhuma notificação no momento.
                        </div>
                        <div class="border-t border-slate-800/60 mt-1"></div>
                        <a href="{{ route('notifications.index') }}" class="flex items-center justify-between px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-800/70">
                            Ver todas
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </details>
                <details class="relative">
                    <summary class="list-none cursor-pointer text-slate-300 hover:text-white text-sm font-medium inline-flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-800/60">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-800 text-white text-xs font-semibold">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        </span>
                        <span class="hidden sm:inline">{{ $user->name ?? '' }}</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="absolute right-0 mt-2 w-52 rounded-xl bg-slate-900/95 border border-slate-700/60 shadow-xl backdrop-blur-sm py-2">
                        @if(Route::has('profile.edit'))
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-800/70">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1 1 18.88 6.196 9 9 0 0 1 5.12 17.804ZM12 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm-6.32 9.906a6 6 0 0 1 12.64 0"></path>
                                </svg>
                                Perfil
                            </a>
                        @endif
                        @if($isAdmin)
                            <a href="{{ route('settings') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-800/70">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3a2 2 0 0 1 2 2v.18a2 2 0 0 0 1 1.73l.43.25a2 2 0 0 0 2 0l.15-.08a2 2 0 0 1 2.73.73l.22.38a2 2 0 0 1-.73 2.73l-.15.1a2 2 0 0 0-1 1.72v.51a2 2 0 0 0 1 1.74l.15.09a2 2 0 0 1 .73 2.73l-.22.38a2 2 0 0 1-2.73.73l-.15-.08a2 2 0 0 0-2 0l-.43.25a2 2 0 0 0-1 1.73V20a2 2 0 0 1-2 2h-.44a2 2 0 0 1-2-2v-.18a2 2 0 0 0-1-1.73l-.43-.25a2 2 0 0 0-2 0l-.15.08a2 2 0 0 1-2.73-.73l-.22-.39a2 2 0 0 1 .73-2.73l.15-.08a2 2 0 0 0 1-1.74v-.5a2 2 0 0 0-1-1.74l-.15-.09a2 2 0 0 1-.73-2.73l.22-.38a2 2 0 0 1 2.73-.73l.15.08a2 2 0 0 0 2 0l.43-.25a2 2 0 0 0 1-1.73V5a2 2 0 0 1 2-2h.44Zm-.22 6a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z"></path>
                                </svg>
                                Configurações
                            </a>
                            <a href="{{ route('admin.monitors.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-800/70">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Monitores
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-800/70">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4m-4-4 4-4-4-4m4 4H3"></path>
                                </svg>
                                Sair
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </div>
    </div>
</nav>
