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
                        <h2 class="text-white font-bold">Ranking de {{ $saleTermLower }}<span id="ranking-team-name">{{ $activeTeam ? ' - ' . $activeTeam->name : '' }}</span></h2>
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
                            {{ $team->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('sellers.index') }}" class="flex items-center gap-2 px-3 py-2 bg-slate-800/50 border border-slate-700 text-white rounded-lg hover:bg-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <line x1="19" x2="19" y1="8" y2="14"></line>
                        <line x1="22" x2="16" y1="11" y2="11"></line>
                    </svg>
                    Vendedor
                </a>

                <button class="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-lg hover:from-blue-700 hover:to-blue-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M5 12h14"></path>
                        <path d="M12 5v14"></path>
                    </svg>
                    Nova {{ $saleTerm }}
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
            <div class="col-span-3 flex items-center justify-center">
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
    const notificationsContainer = document.getElementById('sale-notifications');
    const SALES_STORAGE_KEY = 'ranking_sales_last_timestamp';
    const SALES_POLLING_INTERVAL = 4000;

    let lastSaleTimestamp =
        localStorage.getItem(SALES_STORAGE_KEY) ||
        new Date(Date.now() - 5000).toISOString();

    const formatPoints = (points) => {
        const value = Number(points || 0);
        return value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    };

    const saleTermLabel = @json($saleTerm);
    const saleTermLower = @json($saleTermLower);

    const toastQueue = [];
    const MAX_VISIBLE_TOASTS = 2;

    const getVisibleToasts = () => {
        if (!notificationsContainer) return [];
        return Array.from(notificationsContainer.querySelectorAll('[data-toast="sale"]'));
    };

    const showNextToast = () => {
        if (!notificationsContainer) return;
        if (toastQueue.length === 0) return;
        if (getVisibleToasts().length >= MAX_VISIBLE_TOASTS) return;

        const sale = toastQueue.shift();

        const sellerName = sale?.seller?.name || 'Vendedor';
        const occurrenceLabel = sale?.occurrence?.description || sale?.occurrence?.type || `${saleTermLabel} registrada`;
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

        setTimeout(() => {
            if (toast.isConnected) {
                toast.remove();
                showNextToast();
            }
        }, 7000);

        // Tenta preencher o segundo slot imediatamente
        showNextToast();
    };

    const createSaleToast = (sale) => {
        if (!notificationsContainer) return;

        toastQueue.push(sale);
        showNextToast();
    };

    const fetchRecentSales = async () => {
        if (!notificationsEnabled) return;

        const params = new URLSearchParams();
        if (lastSaleTimestamp) {
            params.append('since', lastSaleTimestamp);
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
                const newestTimestamp = sales[sales.length - 1]?.created_at;
                if (newestTimestamp) {
                    lastSaleTimestamp = newestTimestamp;
                    localStorage.setItem(SALES_STORAGE_KEY, lastSaleTimestamp);
                }
            }
        } catch (error) {
            console.error('Erro ao buscar vendas recentes:', error);
        }
    };

    if (notificationsEnabled) {
        fetchRecentSales();
        setInterval(fetchRecentSales, SALES_POLLING_INTERVAL);
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

    html, body {
        scrollbar-width: thin;
        scrollbar-color: rgba(59, 130, 246, 0.6) rgba(15, 23, 42, 0.6);
    }

    html::-webkit-scrollbar,
    body::-webkit-scrollbar {
        width: 10px;
    }

    html::-webkit-scrollbar-track,
    body::-webkit-scrollbar-track {
        background: rgba(15, 23, 42, 0.6);
    }

    html::-webkit-scrollbar-thumb,
    body::-webkit-scrollbar-thumb {
        background: rgba(59, 130, 246, 0.6);
        border-radius: 999px;
        border: 2px solid rgba(15, 23, 42, 0.6);
    }

    html::-webkit-scrollbar-thumb:hover,
    body::-webkit-scrollbar-thumb:hover {
        background: rgba(59, 130, 246, 0.85);
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
