@extends('layouts.app')

@php
    $saleTerm = $configs['sale_term'] ?? 'Venda';
    $saleTermLower = strtolower($saleTerm);
@endphp

@section('title', "Dashboard - Ranking de {$saleTermLower}")

@section('content')
<div class="min-h-screen bg-[#0a0e1a] relative overflow-hidden">
    <!-- Background animado -->
    <div class="fixed inset-0 pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-950/20 via-slate-950 to-purple-950/20"></div>
        <div class="absolute top-1/4 -left-32 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 -right-32 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl"></div>
    </div>

    <!-- Notificações de vendas -->
    <div id="sale-notifications" class="fixed bottom-4 right-4 z-50 flex flex-col gap-3"></div>

    <!-- GameHeader -->
    <div class="bg-slate-900/80 backdrop-blur-md border-b border-slate-700/50 px-6 py-3 relative z-10">
        <div class="flex items-center justify-between max-w-7xl mx-auto">
            <!-- Left section - Time info -->
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2 px-4 py-2 bg-slate-800/50 rounded-lg border border-slate-700/50">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span class="text-slate-400 text-sm">Total de tempo:</span>
                    <span id="total-participants" class="text-cyan-400 font-bold">{{ str_pad($stats['totalParticipants'] ?? 0, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                
                <div class="flex items-center gap-2 px-4 py-2 bg-slate-800/50 rounded-lg border border-slate-700/50">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                        <polyline points="16 7 22 7 22 13"></polyline>
                    </svg>
                    <span class="text-slate-400 text-sm">Porcentagem do time:</span>
                    <span id="team-percentage" class="text-green-400 font-bold">{{ number_format((($stats['totalPoints'] ?? 0) / 500000) * 100, 2) }}%</span>
                </div>
            </div>

            <!-- Center - Current time -->
            <div class="flex items-center gap-3">
                <div class="text-center px-4 py-2 bg-blue-600/20 rounded-lg border border-blue-500/30">
                    <span class="text-blue-400 font-mono text-lg font-bold" id="current-time">{{ date('H:i:s') }}</span>
                </div>
            </div>

            <!-- Right section - User info -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 px-4 py-2 bg-slate-800/50 rounded-lg border border-slate-700/50">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span class="text-slate-400 text-sm">Usuários:</span>
                    <span class="text-purple-400 font-bold">(<span id="active-participants">{{ $stats['activeParticipants'] ?? 0 }}</span>/<span id="total-participants-inline">{{ $stats['totalParticipants'] ?? 0 }}</span>)</span>
                </div>
                
                <a href="{{ route('settings') }}" class="p-2 hover:bg-slate-800 rounded-lg transition-colors hidden">
                    <svg class="w-5 h-5 text-slate-400 hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="relative z-10">
        <!-- Barra Superior -->
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-4">
                <button class="p-2 hover:bg-slate-800/50 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="m15 18-6-6 6-6"></path>
                    </svg>
                </button>

                <div class="flex items-center gap-3 px-4 py-2 bg-slate-900/60 rounded-xl border border-slate-700/50">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
                            <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
                            <path d="M4 22h16"></path>
                            <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
                            <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
                            <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-white font-bold">Ranking de {{ $saleTermLower }}<span id="ranking-team-name">{{ $activeTeam ? ' - ' . $activeTeam->display_label : '' }}</span></h2>
                        <p class="text-slate-400 text-xs">Por pontuação</p>
                    </div>
                </div>
                <div id="team-chips" class="hidden lg:flex items-center gap-2">
                    <span class="text-xs text-slate-500">Equipes:</span>
                    <a data-team-id="" href="{{ route('dashboard') }}" class="px-2 py-1 rounded-full text-xs border {{ !$activeTeam ? 'border-blue-500 text-blue-300' : 'border-slate-700 text-slate-400 hover:text-white hover:border-slate-500' }}">
                        Geral
                    </a>
                    @foreach($teams as $team)
                        <a data-team-id="{{ $team->id }}" href="{{ route('dashboard', ['team' => $team->id]) }}" class="px-2 py-1 rounded-full text-xs border {{ $activeTeam && $activeTeam->id === $team->id ? 'border-blue-500 text-blue-300' : 'border-slate-700 text-slate-400 hover:text-white hover:border-slate-500' }}">
                            {{ $team->display_label }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button id="toggle-sound-btn" class="p-2 bg-slate-800/50 border border-slate-700 text-white rounded-lg hover:bg-slate-700 transition-colors" title="Alternar som">
                    <svg id="sound-icon-on" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                    </svg>
                    <svg id="sound-icon-off" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Grid Principal -->
        <div class="grid grid-cols-12 gap-6 px-6 py-4">
            <!-- Sidebar Esquerda - Classificação Geral -->
            <div class="col-span-3">
                <div id="ranking-sidebar">
                    @include('dashboard.partials.ranking', ['ranking' => $ranking, 'activeTeam' => $activeTeam])
                </div>
            </div>

            <!-- Pódio Central -->
            <div class="col-span-6 flex items-center justify-center" id="podium-area">
                @include('dashboard.partials.podium', ['top3' => $top3])
            </div>

            <!-- Controles Direita -->
            <div class="col-span-3 flex flex-col gap-4">
                <!-- Metas Ativas -->
                @if(isset($activeGoals) && $activeGoals->count() > 0)
                <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-slate-700/50 p-4">
                    <h3 class="text-white font-semibold mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Metas Ativas
                    </h3>
                    <div class="space-y-3 max-h-[400px] overflow-y-auto">
                        @foreach($activeGoals as $goal)
                        <div class="bg-slate-800/50 rounded-lg p-3 border border-slate-700/30">
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="text-sm font-medium text-white">{{ $goal->name }}</h4>
                                <span class="px-2 py-0.5 text-xs rounded-full
                                    @if($goal->scope === 'global') bg-purple-600/20 text-purple-400
                                    @elseif($goal->scope === 'team') bg-blue-600/20 text-blue-400
                                    @else bg-green-600/20 text-green-400
                                    @endif">
                                    {{ ucfirst($goal->scope) }}
                                </span>
                            </div>
                            <div class="mb-2">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-slate-400">Progresso</span>
                                    <span class="text-white font-semibold">{{ number_format($goal->progress_data['progress'], 1) }}%</span>
                                </div>
                                <div class="w-full bg-slate-700 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full transition-all
                                        @if($goal->progress_data['progress'] >= 100) bg-green-500
                                        @elseif($goal->progress_data['progress'] >= 50) bg-yellow-500
                                        @else bg-blue-500
                                        @endif"
                                        style="width: {{ min(100, $goal->progress_data['progress']) }}%">
                                    </div>
                                </div>
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ number_format($goal->progress_data['current_value'], 0, ',', '.') }} / {{ number_format($goal->target_value, 0, ',', '.') }} pontos
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('goals.index') }}" class="mt-3 block text-center text-sm text-blue-400 hover:text-blue-300">
                        Ver todas as metas →
                    </a>
                </div>
                @endif
                
                <!-- Controles de Atualização -->
                <div class="flex flex-col items-center gap-4">
                    <!-- Time selector buttons -->
                    <div class="flex flex-col gap-2">
                        <button data-refresh-interval="15000" data-refresh-label="15s" class="px-4 py-2 rounded-lg text-sm font-medium transition-all bg-slate-800/50 text-slate-400 hover:bg-slate-700/50">15s</button>
                        <button data-refresh-interval="30000" data-refresh-label="30s" class="px-4 py-2 rounded-lg text-sm font-medium transition-all bg-blue-600 text-white shadow-lg shadow-blue-500/50">30s</button>
                        <button data-refresh-interval="60000" data-refresh-label="1m" class="px-4 py-2 rounded-lg text-sm font-medium transition-all bg-slate-800/50 text-slate-400 hover:bg-slate-700/50">1m</button>
                        <button data-refresh-interval="180000" data-refresh-label="3m" class="px-4 py-2 rounded-lg text-sm font-medium transition-all bg-slate-800/50 text-slate-400 hover:bg-slate-700/50">3m</button>
                        <button data-refresh-interval="300000" data-refresh-label="5m" class="px-4 py-2 rounded-lg text-sm font-medium transition-all bg-slate-800/50 text-slate-400 hover:bg-slate-700/50">5m</button>
                        <button data-refresh-interval="600000" data-refresh-label="10m" class="px-4 py-2 rounded-lg text-sm font-medium transition-all bg-slate-800/50 text-slate-400 hover:bg-slate-700/50">10m</button>
                        <button data-refresh-interval="900000" data-refresh-label="15m" class="px-4 py-2 rounded-lg text-sm font-medium transition-all bg-slate-800/50 text-slate-400 hover:bg-slate-700/50">15m</button>
                    </div>
                    
                    <!-- Countdown display -->
                    <div class="text-xs text-slate-400 font-mono" id="refresh-countdown">--</div>

                    <!-- Play/Pause button -->
                    <button id="toggle-refresh" class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/50 hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5 text-white fill-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="14" y="4" width="4" height="16" rx="1"></rect>
                            <rect x="6" y="4" width="4" height="16" rx="1"></rect>
                        </svg>
                    </button>
                    
                    <!-- Timer display -->
                    <div class="relative w-32 h-32">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle cx="64" cy="64" r="56" stroke="rgba(100, 116, 139, 0.3)" stroke-width="8" fill="none"></circle>
                            <circle cx="64" cy="64" r="56" stroke="url(#gradient-timer)" stroke-width="8" fill="none" stroke-linecap="round" stroke-dasharray="351.86" stroke-dashoffset="87.965" class="transition-all duration-1000"></circle>
                            <defs>
                                <linearGradient id="gradient-timer" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#3b82f6"></stop>
                                    <stop offset="100%" stop-color="#8b5cf6"></stop>
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-white text-xl font-bold">2 sem 4d</span>
                            <span class="text-slate-400 text-xs">restantes</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Atualizar relógio a cada segundo
    function updateTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            timeElement.textContent = hours + ':' + minutes + ':' + seconds;
        }
    }
    setInterval(updateTime, 1000);
    updateTime();

    // Auto refresh do ranking (recarrega a página)
    const refreshButtons = Array.from(document.querySelectorAll('[data-refresh-interval]'));
    const toggleButton = document.getElementById('toggle-refresh');
    const countdownElement = document.getElementById('refresh-countdown');
    const ACTIVE_CLASSES = 'bg-blue-600 text-white shadow-lg shadow-blue-500/50';
    const INACTIVE_CLASSES = 'bg-slate-800/50 text-slate-400 hover:bg-slate-700/50';
    const STORAGE_KEY = 'ranking_refresh_interval';
    const PAUSED_KEY = 'ranking_refresh_paused';

    let refreshTimer = null;
    let countdownTimer = null;
    let remainingMs = 0;
    let selectedInterval = Number(localStorage.getItem(STORAGE_KEY)) || 30000;
    let isPaused = localStorage.getItem(PAUSED_KEY) === 'true';

    const setButtonState = (button, isActive) => {
        const classes = button.className.split(' ').filter(Boolean);
        const cleaned = classes
            .filter((cls) => !ACTIVE_CLASSES.split(' ').includes(cls))
            .filter((cls) => !INACTIVE_CLASSES.split(' ').includes(cls));
        button.className = `${cleaned.join(' ')} ${isActive ? ACTIVE_CLASSES : INACTIVE_CLASSES}`.trim();
    };

    const updateActiveButton = () => {
        refreshButtons.forEach((button) => {
            const interval = Number(button.dataset.refreshInterval || 0);
            setButtonState(button, interval === selectedInterval);
        });
    };

    const updateToggleIcon = () => {
        if (!toggleButton) return;
        toggleButton.innerHTML = isPaused
            ? '<svg class="w-5 h-5 text-white fill-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="6 3 20 12 6 21 6 3"></polygon></svg>'
            : '<svg class="w-5 h-5 text-white fill-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="14" y="4" width="4" height="16" rx="1"></rect><rect x="6" y="4" width="4" height="16" rx="1"></rect></svg>';
    };

    const updateCountdownLabel = () => {
        if (!countdownElement) return;
        if (isPaused) {
            countdownElement.textContent = 'Pausado';
            return;
        }
        const remainingSeconds = Math.max(0, Math.ceil(remainingMs / 1000));
        countdownElement.textContent = `Atualiza em ${remainingSeconds}s`;
    };

    const teamsRotation = @json($teams->values()->map(fn($team) => ['id' => $team->id])->all());
    const teamIdsRotation = [null, ...teamsRotation.map((team) => team.id)];
    let currentTeamId = @json($activeTeam?->id);

    const rankingSidebar = document.getElementById('ranking-sidebar');
    const podiumArea = document.getElementById('podium-area');
    const teamNameSpan = document.getElementById('ranking-team-name');
    const teamChips = document.getElementById('team-chips');
    const totalParticipantsEl = document.getElementById('total-participants');
    const totalParticipantsInlineEl = document.getElementById('total-participants-inline');
    const activeParticipantsEl = document.getElementById('active-participants');
    const teamPercentageEl = document.getElementById('team-percentage');

    const updateTeamChips = () => {
        if (!teamChips) return;
        const chips = Array.from(teamChips.querySelectorAll('[data-team-id]'));
        chips.forEach((chip) => {
            const chipTeamId = chip.dataset.teamId || null;
            const isActive = (chipTeamId || null) === (currentTeamId || null);
            chip.classList.toggle('border-blue-500', isActive);
            chip.classList.toggle('text-blue-300', isActive);
            chip.classList.toggle('border-slate-700', !isActive);
            chip.classList.toggle('text-slate-400', !isActive);
        });
    };

    const updateHeaderTeamName = (teamName) => {
        if (!teamNameSpan) return;
        teamNameSpan.textContent = teamName ? ` - ${teamName}` : '';
    };

    const updateStats = (stats) => {
        if (totalParticipantsEl) {
            totalParticipantsEl.textContent = String(stats.totalParticipants || 0).padStart(4, '0');
        }
        if (totalParticipantsInlineEl) {
            totalParticipantsInlineEl.textContent = String(stats.totalParticipants || 0);
        }
        if (activeParticipantsEl) {
            activeParticipantsEl.textContent = String(stats.activeParticipants || 0);
        }
        if (teamPercentageEl) {
            teamPercentageEl.textContent = `${stats.percentage || '0.00'}%`;
        }
    };

    const withFadeTransition = (element, updateCallback) => {
        if (!element) return;
        element.classList.add('fade-transition');
        element.classList.add('fade-enter');
        requestAnimationFrame(() => {
            element.classList.add('fade-active');
            element.classList.remove('fade-enter');
        });

        updateCallback();

        setTimeout(() => {
            element.classList.remove('fade-active');
        }, 300);
    };

    const fetchDashboardData = async () => {
        const url = new URL('/dashboard/data', window.location.origin);
        if (currentTeamId) {
            url.searchParams.set('team', currentTeamId);
        }

        try {
            const response = await fetch(url.toString(), {
                headers: { 'Accept': 'application/json' },
            });
            if (!response.ok) return;
            const data = await response.json();
            if (rankingSidebar && data.rankingHtml) {
                const scrollContainer = rankingSidebar.querySelector('.custom-scrollbar');
                const previousScrollTop = scrollContainer ? scrollContainer.scrollTop : 0;

                withFadeTransition(rankingSidebar, () => {
                    rankingSidebar.innerHTML = data.rankingHtml;
                });

                requestAnimationFrame(() => {
                    const nextScrollContainer = rankingSidebar.querySelector('.custom-scrollbar');
                    if (nextScrollContainer) {
                        nextScrollContainer.scrollTop = previousScrollTop;
                    }
                });
            }
            if (podiumArea && data.podiumHtml) {
                withFadeTransition(podiumArea, () => {
                    podiumArea.innerHTML = data.podiumHtml;
                });
            }
            updateHeaderTeamName(data.activeTeamName || '');
            updateStats(data.stats || {});
        } catch (error) {
            console.error('Erro ao atualizar dashboard:', error);
        }
    };

    const rotateTeamAndRefresh = () => {
        const currentIndex = teamIdsRotation.indexOf(currentTeamId ?? null);
        const nextIndex = currentIndex >= 0 ? (currentIndex + 1) % teamIdsRotation.length : 0;
        currentTeamId = teamIdsRotation[nextIndex];
        updateTeamChips();
        fetchDashboardData();
    };

    const startAutoRefresh = () => {
        if (refreshTimer) clearInterval(refreshTimer);
        if (countdownTimer) clearInterval(countdownTimer);
        if (!isPaused) {
            remainingMs = selectedInterval;
            updateCountdownLabel();
            countdownTimer = setInterval(() => {
                remainingMs -= 1000;
                if (remainingMs < 0) {
                    remainingMs = 0;
                }
                updateCountdownLabel();
            }, 1000);
            refreshTimer = setInterval(() => {
                rotateTeamAndRefresh();
            }, selectedInterval);
        } else {
            updateCountdownLabel();
        }
    };

    refreshButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const interval = Number(button.dataset.refreshInterval || 0);
            if (!interval) return;
            selectedInterval = interval;
            localStorage.setItem(STORAGE_KEY, String(selectedInterval));
            updateActiveButton();
            startAutoRefresh();
        });
    });

    if (toggleButton) {
        toggleButton.addEventListener('click', () => {
            isPaused = !isPaused;
            localStorage.setItem(PAUSED_KEY, String(isPaused));
            updateToggleIcon();
            startAutoRefresh();
        });
    }

    if (teamChips) {
        const chips = Array.from(teamChips.querySelectorAll('[data-team-id]'));
        chips.forEach((chip) => {
            chip.addEventListener('click', (event) => {
                event.preventDefault();
                currentTeamId = chip.dataset.teamId || null;
                updateTeamChips();
                fetchDashboardData();
            });
        });
    }

    updateActiveButton();
    updateToggleIcon();
    startAutoRefresh();

    // Notificações de vendas em tempo real (polling)
    const notificationsEnabled = @json((($configs['notifications_system_enabled'] ?? 'true') === 'true'));
    const SOUND_STORAGE_KEY = 'dashboard_sound_enabled';
    let soundEnabled = localStorage.getItem(SOUND_STORAGE_KEY);
    if (soundEnabled === null) {
        soundEnabled = @json((($configs['notifications_sound_enabled'] ?? 'true') === 'true'));
        localStorage.setItem(SOUND_STORAGE_KEY, soundEnabled);
    } else {
        soundEnabled = soundEnabled === 'true';
    }
    const notificationsContainer = document.getElementById('sale-notifications');
    const SALES_STORAGE_KEY = 'ranking_sales_last_timestamp';
    const NOTIFICATIONS_STORAGE_KEY = 'ranking_notifications_last_timestamp';
    const SALES_POLLING_MIN_INTERVAL = 4000;
    const SALES_POLLING_MAX_INTERVAL = 60000;
    const SALES_POLLING_BACKOFF_FACTOR = 1.5;

    // Configurações de sons
    const soundsConfig = @json(json_decode($configs['notifications_sounds_config'] ?? '{}', true) ?: []);
    const customSoundsPaths = @json(json_decode($configs['notifications_custom_sounds'] ?? '{}', true) ?: []);
    const customSounds = {};
    @php
        $customSoundsPaths = json_decode($configs['notifications_custom_sounds'] ?? '{}', true) ?: [];
    @endphp
    @foreach($customSoundsPaths as $eventKey => $filePath)
        customSounds['{{ $eventKey }}'] = '{{ asset("storage/" . $filePath) }}';
    @endforeach
    const notificationEventsConfig = @json($notificationEventsConfig ?? []);

    const sinceSales = localStorage.getItem(SALES_STORAGE_KEY);
    let sinceNotifications = localStorage.getItem(NOTIFICATIONS_STORAGE_KEY);
    let salesPollingTimer = null;
    let salesPollingDelay = SALES_POLLING_MIN_INTERVAL;
    let salesPollingInFlight = false;
    let lastSalesCountLog = null;

    const formatPoints = (points) => {
        const value = Number(points || 0);
        return value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    };

    const saleTermLabel = @json($saleTerm);
    const saleTermLower = @json($saleTermLower);

    const toastQueue = [];
    const MAX_VISIBLE_TOASTS = parseInt(@json($configs['notifications_popup_max_count'] ?? '2'), 10);
    const AUTO_CLOSE_SECONDS = parseInt(@json($configs['notifications_popup_auto_close_seconds'] ?? '7'), 10) * 1000;
    
    // Debug: verificar configurações carregadas
    console.log('Configurações de notificações:', {
        maxVisibleToasts: MAX_VISIBLE_TOASTS,
        autoCloseSeconds: AUTO_CLOSE_SECONDS / 1000
    });

    const getVisibleToasts = () => {
        if (!notificationsContainer) return [];
        return Array.from(notificationsContainer.querySelectorAll('[data-toast="sale"]'));
    };

    const showNextToast = () => {
        if (!notificationsContainer) return;
        if (toastQueue.length === 0) return;
        if (getVisibleToasts().length >= MAX_VISIBLE_TOASTS) return;

        const sale = toastQueue.shift();

        const sellerName = sale?.seller?.name || 'Colaborador';
        const occurrenceLabel = sale?.occurrence?.type || `${saleTermLabel} registrada`;
        const pointsLabel = formatPoints(sale?.points);

        const toast = document.createElement('div');
        toast.dataset.toast = 'sale';
        toast.className = 'bg-slate-900/90 border border-blue-500/40 text-white px-4 py-3 rounded-xl shadow-lg backdrop-blur-sm flex items-start gap-3 max-w-sm';
        toast.innerHTML = `
            <div class="flex-1">
                <p class="text-sm font-semibold">Nova ${saleTermLower} registrada</p>
                <p class="text-xs text-slate-300 mt-1">${occurrenceLabel}</p>
                <p class="text-xs text-blue-300 mt-1">+${pointsLabel} pontos para ${sellerName}</p>
            </div>
            <button class="text-slate-400 hover:text-white" aria-label="Fechar">✕</button>
        `;

        const closeButton = toast.querySelector('button');
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                toast.remove();
                showNextToast();
            });
        }

        notificationsContainer.appendChild(toast);

        // Tocar som quando a notificação é exibida na tela
        playNotificationSound('sale_registered');

        setTimeout(() => {
            if (toast.isConnected) {
                toast.remove();
                // Mostrar próximo toast da fila quando este fechar
                showNextToast();
            }
        }, AUTO_CLOSE_SECONDS);
    };

    // AudioContext global para evitar problemas de autoplay
    let globalAudioContext = null;
    
    function getAudioContext() {
        if (!globalAudioContext) {
            globalAudioContext = new (window.AudioContext || window.webkitAudioContext)();
        }
        // Resumir contexto se estiver suspenso (necessário para autoplay)
        if (globalAudioContext.state === 'suspended') {
            globalAudioContext.resume();
        }
        return globalAudioContext;
    }

    // Função para tocar som padrão
    function playSound(type) {
        try {
            const audioContext = getAudioContext();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            const frequencies = {
                'notification': 800,
                'success': 1000,
                'error': 400,
                'warning': 600,
                'info': 700,
            };

            oscillator.frequency.value = frequencies[type] || 800;
            oscillator.type = 'sine';
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        } catch (error) {
            console.error('Erro ao tocar som padrão:', error);
        }
    }

    // Função para tocar som personalizado (MP3)
    function playCustomSound(fileUrl) {
        try {
            const audio = new Audio(fileUrl);
            audio.volume = 0.7;
            audio.play().catch(error => {
                console.error('Erro ao tocar som personalizado:', error);
            });
        } catch (error) {
            console.error('Erro ao criar áudio personalizado:', error);
        }
    }

    // Função para tocar som de notificação baseado no evento
    function playNotificationSound(eventKey) {
        if (!soundEnabled) return;
        
        // Verificar se o som está habilitado para este evento
        const eventConfig = notificationEventsConfig[eventKey];
        if (!eventConfig || !eventConfig.sound) {
            return;
        }

        // Obter o tipo de som configurado
        const soundType = soundsConfig[eventKey] || 'notification';
        
        if (soundType === 'custom' && customSounds[eventKey]) {
            // Tocar arquivo personalizado
            playCustomSound(customSounds[eventKey]);
        } else {
            // Tocar som padrão
            playSound(soundType);
        }
    }

    const createSaleToast = (sale) => {
        if (!notificationsContainer) return;

        // Adicionar à fila - o som será tocado quando a notificação aparecer na tela
        toastQueue.push(sale);
        showNextToast();
    };

    const getNewestTimestamp = (sales) => {
        return sales.reduce((latest, sale) => {
            const ts = sale?.created_at;
            if (!ts) return latest;
            if (!latest || ts > latest) return ts;
            return latest;
        }, null);
    };

    const scheduleNextSalesPoll = (delayMs) => {
        if (salesPollingTimer) {
            clearTimeout(salesPollingTimer);
        }
        if (!notificationsEnabled || document.visibilityState !== 'visible') {
            return;
        }
        salesPollingTimer = setTimeout(() => {
            fetchRecentSales();
        }, delayMs);
    };

    const updateSalesPollingDelay = (hasData) => {
        if (hasData) {
            salesPollingDelay = SALES_POLLING_MIN_INTERVAL;
            return;
        }
        salesPollingDelay = Math.min(
            SALES_POLLING_MAX_INTERVAL,
            Math.round(salesPollingDelay * SALES_POLLING_BACKOFF_FACTOR)
        );
    };

    const fetchRecentSales = async () => {
        if (!notificationsEnabled) return;
        if (document.visibilityState !== 'visible') {
            return;
        }
        if (salesPollingInFlight) {
            return;
        }
        salesPollingInFlight = true;

        const params = new URLSearchParams();
        if (sinceNotifications) {
            params.append('since', sinceNotifications);
        }
        params.append('limit', '20');

        try {
            const response = await fetch(`/scores/recent?${params.toString()}`, {
                headers: { 'Accept': 'application/json' },
            });

            if (!response.ok) {
                return;
            }

            const result = await response.json();
            const sales = result?.data || [];

            if (sales.length > 0) {
                sales.forEach(createSaleToast);
                const newestTimestamp = getNewestTimestamp(sales);
                if (newestTimestamp && newestTimestamp !== sinceNotifications) {
                    sinceNotifications = newestTimestamp;
                    localStorage.setItem(NOTIFICATIONS_STORAGE_KEY, sinceNotifications);
                }
                if (lastSalesCountLog !== sales.length) {
                    console.log('Monitor: Novas vendas recebidas', { count: sales.length });
                    lastSalesCountLog = sales.length;
                }
                updateSalesPollingDelay(true);
            } else {
                if (lastSalesCountLog !== 0) {
                    console.log('Monitor: Nenhuma nova venda');
                    lastSalesCountLog = 0;
                }
                updateSalesPollingDelay(false);
            }
        } catch (error) {
            console.error('Erro ao buscar vendas recentes:', error);
            updateSalesPollingDelay(false);
        } finally {
            salesPollingInFlight = false;
            scheduleNextSalesPoll(salesPollingDelay);
        }
    };

    // Inicializar AudioContext quando a página carregar
    if (soundEnabled) {
        // Tentar inicializar o AudioContext imediatamente
        try {
            getAudioContext();
        } catch (error) {
            console.log('AudioContext não pode ser inicializado ainda:', error);
        }
        
        // Inicializar AudioContext na primeira interação do usuário (necessário para autoplay)
        const initAudioOnInteraction = () => {
            getAudioContext();
            document.removeEventListener('click', initAudioOnInteraction);
            document.removeEventListener('touchstart', initAudioOnInteraction);
            document.removeEventListener('keydown', initAudioOnInteraction);
        };
        
        document.addEventListener('click', initAudioOnInteraction, { once: true });
        document.addEventListener('touchstart', initAudioOnInteraction, { once: true });
        document.addEventListener('keydown', initAudioOnInteraction, { once: true });
    }

    // Botão de toggle de som
    const toggleSoundBtn = document.getElementById('toggle-sound-btn');
    const soundIconOn = document.getElementById('sound-icon-on');
    const soundIconOff = document.getElementById('sound-icon-off');

    const updateSoundButton = () => {
        if (soundEnabled) {
            soundIconOn.style.display = 'block';
            soundIconOff.style.display = 'none';
            toggleSoundBtn.setAttribute('title', 'Som ativo - Clique para desativar');
            toggleSoundBtn.classList.remove('bg-slate-800/50');
            toggleSoundBtn.classList.add('bg-green-600/20', 'border-green-500/50');
        } else {
            soundIconOn.style.display = 'none';
            soundIconOff.style.display = 'block';
            toggleSoundBtn.setAttribute('title', 'Som desativado - Clique para ativar');
            toggleSoundBtn.classList.remove('bg-green-600/20', 'border-green-500/50');
            toggleSoundBtn.classList.add('bg-slate-800/50');
        }
    };

    if (toggleSoundBtn) {
        toggleSoundBtn.addEventListener('click', () => {
            soundEnabled = !soundEnabled;
            localStorage.setItem(SOUND_STORAGE_KEY, soundEnabled);
            updateSoundButton();
        });
        updateSoundButton();
    }

    const startSalesPolling = () => {
        scheduleNextSalesPoll(0);
    };

    const stopSalesPolling = () => {
        if (salesPollingTimer) {
            clearTimeout(salesPollingTimer);
            salesPollingTimer = null;
        }
    };

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            console.log('Monitor: Página visível, retomando polling de notificações');
            startSalesPolling();
        } else {
            console.log('Monitor: Página perdeu o foco, pausando polling de notificações');
            stopSalesPolling();
        }
    });

    if (notificationsEnabled) {
        startSalesPolling();
    }
</script>
@endpush

@push('styles')
<style>
    .podium-card {
        animation: podium-float 5s ease-in-out infinite;
        transform-origin: center;
        will-change: transform, filter;
    }

    .podium-first {
        animation-duration: 4.5s;
        filter: drop-shadow(0 0 12px rgba(250, 204, 21, 0.35));
    }

    .podium-second {
        animation-duration: 5.2s;
        filter: drop-shadow(0 0 10px rgba(34, 211, 238, 0.3));
    }

    .podium-third {
        animation-duration: 5.8s;
        filter: drop-shadow(0 0 10px rgba(251, 146, 60, 0.3));
    }

    .fade-transition {
        opacity: 1;
        transform: translateY(0);
    }

    .fade-transition.fade-enter {
        opacity: 0;
        transform: translateY(8px);
    }

    .fade-transition.fade-active {
        transition: opacity 300ms ease, transform 300ms ease;
    }


    @keyframes podium-float {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-8px);
        }
    }
</style>
@endpush
@endsection
