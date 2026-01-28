@extends('monitors.themes.default.layout')

@php
    $saleTerm = $configs['sale_term'] ?? 'Venda';
    $saleTermLower = strtolower($saleTerm);
@endphp

@section('content')
<div class="w-full h-full bg-[#0a0e1a] relative overflow-hidden">
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
                    <span id="team-percentage" class="text-green-400 font-bold">{{ number_format($percentage ?? 0, 2) }}%</span>
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
                    <a data-team-id="" href="javascript:void(0)" class="px-2 py-1 rounded-full text-xs border {{ !$activeTeam ? 'border-blue-500 text-blue-300' : 'border-slate-700 text-slate-400 hover:text-white hover:border-slate-500' }}">
                        Geral
                    </a>
                    @foreach($teams as $team)
                        <a data-team-id="{{ $team->id }}" href="javascript:void(0)" class="px-2 py-1 rounded-full text-xs border {{ $activeTeam && $activeTeam->id === $team->id ? 'border-blue-500 text-blue-300' : 'border-slate-700 text-slate-400 hover:text-white hover:border-slate-500' }}">
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
                
                <button id="read-voice-btn" class="p-2 bg-slate-800/50 border border-slate-700 text-white rounded-lg hover:bg-slate-700 transition-colors" title="Ler ranking por voz">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
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
                            <circle id="season-progress-circle" cx="64" cy="64" r="56" stroke="url(#gradient-timer)" stroke-width="8" fill="none" stroke-linecap="round" stroke-dasharray="351.86" stroke-dashoffset="{{ $activeSeason ? $activeSeason->getProgressCircleOffset() : 351.86 }}" class="transition-all duration-1000"></circle>
                            <defs>
                                <linearGradient id="gradient-timer" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#3b82f6"></stop>
                                    <stop offset="100%" stop-color="#8b5cf6"></stop>
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span id="season-remaining-time" class="text-white text-xl font-bold">{{ $activeSeason ? $activeSeason->getRemainingTimeFormatted() : '0 sem 0d' }}</span>
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
    // Configuração do monitor vindo de window.DASHBOARD_CONFIG
    const config = window.DASHBOARD_CONFIG || {};
    window.MONITOR_VOICE_AUTOMATION_HANDLED = true;
    // Pegar slug do monitor da URL ou da config
    const monitorSlug = config.monitor_slug || @json($monitor->slug ?? '') || window.location.pathname.match(/\/monitor\/([^\/]+)/)?.[1] || '';
    
    if (!monitorSlug) {
        console.error('Monitor: slug não encontrado!', { config, pathname: window.location.pathname });
    }
    
    // Dados da temporada ativa
    const activeSeason = @json($activeSeason ? [
        'ends_at' => $activeSeason->ends_at->format('Y-m-d'),
        'starts_at' => $activeSeason->starts_at->format('Y-m-d'),
    ] : null);
    
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
    
    // Atualizar contagem regressiva da temporada
    function updateSeasonCountdown() {
        if (!activeSeason) {
            const remainingTimeEl = document.getElementById('season-remaining-time');
            const progressCircle = document.getElementById('season-progress-circle');
            if (remainingTimeEl) remainingTimeEl.textContent = '0 sem 0d';
            if (progressCircle) progressCircle.style.strokeDashoffset = '351.86';
            return;
        }
        
        const now = new Date();
        const endDate = new Date(activeSeason.ends_at + 'T23:59:59');
        const startDate = new Date(activeSeason.starts_at + 'T00:00:00');
        
        // Calcular tempo restante
        let diffMs = endDate - now;
        if (diffMs < 0) {
            diffMs = 0;
        }
        
        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
        const weeks = Math.floor(diffDays / 7);
        const days = diffDays % 7;
        
        // Atualizar texto
        const remainingTimeEl = document.getElementById('season-remaining-time');
        if (remainingTimeEl) {
            remainingTimeEl.textContent = `${weeks} sem ${days}d`;
        }
        
        // Calcular progresso percentual
        const totalMs = endDate - startDate;
        const elapsedMs = now - startDate;
        let progress = 0;
        
        if (totalMs > 0) {
            if (elapsedMs < 0) {
                progress = 0;
            } else if (elapsedMs >= totalMs) {
                progress = 100;
            } else {
                progress = (elapsedMs / totalMs) * 100;
            }
        }
        
        // Atualizar círculo de progresso
        const progressCircle = document.getElementById('season-progress-circle');
        if (progressCircle) {
            const circumference = 351.86; // 2 * PI * 56 (raio)
            const offset = circumference - (progress / 100 * circumference);
            progressCircle.style.strokeDashoffset = Math.max(0, Math.min(circumference, offset));
        }
    }
    
    // Atualizar a cada minuto
    setInterval(updateSeasonCountdown, 60000);
    updateSeasonCountdown();

    // Configurações do monitor
    const refreshInterval = config.refresh_interval || 30000;
    const autoRotateTeams = config.auto_rotate_teams !== false;
    const allowedTeams = config.teams || [];
    // notificationsEnabled será definido mais abaixo (igual ao dashboard - usa apenas notifications_system_enabled)
    // soundEnabled será definido mais abaixo com localStorage

    // Dados iniciais
    const teamsRotation = @json($teams->values()->map(fn($team) => ['id' => $team->id])->all());
    let teamIdsRotation = [null];

    const parseBoolean = (value) => {
        if (value === true || value === false) return value;
        if (value === 1 || value === '1') return true;
        if (value === 0 || value === '0') return false;
        if (typeof value === 'string') {
            return ['true', 'yes', 'on'].includes(value.toLowerCase());
        }
        return Boolean(value);
    };

    const normalizeTeamId = (value) => {
        if (value === null || value === undefined || value === '') return null;
        return String(value);
    };
    
    // Filtrar equipes se houver configuração
    if (allowedTeams.length > 0) {
        // Filtrar apenas equipes permitidas e manter a ordem exibida na tela
        const allowedSet = new Set(allowedTeams.map((id) => String(id)));
        const orderedAllowedTeams = teamsRotation
            .filter((team) => allowedSet.has(String(team.id)))
            .map((team) => String(team.id));
        teamIdsRotation = [null, ...orderedAllowedTeams];
    } else {
        // Se não há equipes especificadas, usar todas
        teamIdsRotation = [null, ...teamsRotation.map((team) => String(team.id))];
    }
    
    console.log('Monitor: Configuração de rotação de equipes', {
        allowedTeams,
        teamsRotation: teamsRotation.map(t => t.id),
        teamIdsRotation,
        autoRotateTeams
    });
    
    let currentTeamId = normalizeTeamId(@json($activeTeam?->id));
    
    // Garantir que currentTeamId inicial está na rotação (se não estiver, usar null/Geral)
    if (currentTeamId && !teamIdsRotation.includes(currentTeamId)) {
        console.log('Monitor: Equipe atual não está na rotação, resetando para Geral', { currentTeamId, teamIdsRotation });
        currentTeamId = null;
    }

    const rankingSidebar = document.getElementById('ranking-sidebar');
    const podiumArea = document.getElementById('podium-area');
    const teamNameSpan = document.getElementById('ranking-team-name');
    const totalParticipantsEl = document.getElementById('total-participants');
    const totalParticipantsInlineEl = document.getElementById('total-participants-inline');
    const activeParticipantsEl = document.getElementById('active-participants');
    const teamPercentageEl = document.getElementById('team-percentage');

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
        const url = new URL(`/monitor/${monitorSlug}/data`, window.location.origin);
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
            console.error('Erro ao atualizar monitor:', error);
        }
    };

    // Auto refresh do ranking
    const refreshButtons = Array.from(document.querySelectorAll('[data-refresh-interval]'));
    const toggleButton = document.getElementById('toggle-refresh');
    const countdownElement = document.getElementById('refresh-countdown');
    const ACTIVE_CLASSES = 'bg-blue-600 text-white shadow-lg shadow-blue-500/50';
    const INACTIVE_CLASSES = 'bg-slate-800/50 text-slate-400 hover:bg-slate-700/50';
    const STORAGE_KEY = `monitor_${monitorSlug}_refresh_interval`;
    const PAUSED_KEY = `monitor_${monitorSlug}_refresh_paused`;

    let refreshTimer = null;
    let countdownTimer = null;
    let remainingMs = 0;
    // Sempre usar a configuração do monitor ao recarregar (não usar localStorage)
    let selectedInterval = refreshInterval;
    let isPaused = false; // Sempre começar ativo ao recarregar

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

    const teamChips = document.getElementById('team-chips');

    const updateTeamChips = () => {
        if (!teamChips) return;
        const chips = Array.from(teamChips.querySelectorAll('[data-team-id]'));
        chips.forEach((chip) => {
            const chipTeamId = normalizeTeamId(chip.dataset.teamId || null);
            const isActive = chipTeamId === (currentTeamId || null);
            chip.classList.toggle('border-blue-500', isActive);
            chip.classList.toggle('text-blue-300', isActive);
            chip.classList.toggle('border-slate-700', !isActive);
            chip.classList.toggle('text-slate-400', !isActive);
        });
    };

    const rotateTeamAndRefresh = () => {
        if (voiceSyncState.state !== 'idle') {
            return;
        }
        if (!autoRotateTeams) {
            updateTeamChips();
            fetchDashboardData();
            return;
        }
        
        // Garantir que teamIdsRotation tem valores válidos
        if (teamIdsRotation.length === 0) {
            teamIdsRotation = [null];
        }
        
        const currentIndex = teamIdsRotation.indexOf(currentTeamId ?? null);
        let nextIndex;
        
        if (currentIndex >= 0) {
            // Se encontrou, vai para a próxima
            nextIndex = (currentIndex + 1) % teamIdsRotation.length;
        } else {
            // Se não encontrou (por exemplo, equipe foi removida), começa do início
            nextIndex = 0;
        }
        
        currentTeamId = teamIdsRotation[nextIndex];
        
        console.log('Monitor: Rotacionando equipe', {
            currentIndex,
            nextIndex,
            currentTeamId: currentTeamId || 'Geral',
            teamIdsRotation,
            totalEquipes: teamIdsRotation.length
        });
        
        updateTeamChips();
        fetchDashboardData();
        
        // Resetar o countdown após a rotação
        if (!isPaused && countdownTimer) {
            remainingMs = selectedInterval;
            updateCountdownLabel();
        }
    };

    const startAutoRefresh = (initialRemainingMs = null) => {
        if (refreshTimer) clearInterval(refreshTimer);
        if (countdownTimer) clearInterval(countdownTimer);
        if (!isPaused) {
            remainingMs = typeof initialRemainingMs === 'number' ? initialRemainingMs : selectedInterval;
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
            // Não salvar no localStorage - usar apenas durante a sessão
            updateActiveButton();
            startAutoRefresh();
        });
    });

    if (toggleButton) {
        toggleButton.addEventListener('click', () => {
            isPaused = !isPaused;
            // Não salvar no localStorage - usar apenas durante a sessão
            updateToggleIcon();
            startAutoRefresh();
        });
    }

    if (teamChips) {
        const chips = Array.from(teamChips.querySelectorAll('[data-team-id]'));
        chips.forEach((chip) => {
            chip.addEventListener('click', (event) => {
                event.preventDefault();
                if (voiceSyncState.state !== 'idle') {
                    return;
                }
                currentTeamId = normalizeTeamId(chip.dataset.teamId || null);
                updateTeamChips();
                fetchDashboardData();
                // Resetar o countdown ao trocar de equipe manualmente
                remainingMs = selectedInterval;
                updateCountdownLabel();
            });
        });
    }

    updateActiveButton();
    updateToggleIcon();
    updateTeamChips();
    startAutoRefresh();

    // Notificações de vendas em tempo real (polling)
    // Verificar configuração do monitor primeiro, depois do sistema
    const hasMonitorNotificationsSetting = config.notifications_enabled !== undefined && config.notifications_enabled !== null;
    const monitorNotificationsEnabled = parseBoolean(config.notifications_enabled);
    const systemNotificationsEnabled = @json((($configs['notifications_system_enabled'] ?? 'true') === 'true'));
    // Notificações do monitor têm prioridade, mas devem estar habilitadas no sistema também
    const notificationsEnabled = systemNotificationsEnabled && (hasMonitorNotificationsSetting ? monitorNotificationsEnabled : true);
    
    // Som: usar configuração do monitor ao recarregar (não usar localStorage)
    const monitorSoundEnabled = parseBoolean(config.sound_enabled);
    const systemSoundEnabled = @json((($configs['notifications_sound_enabled'] ?? 'true') === 'true'));
    // Som do monitor tem prioridade se configurado, senão usa do sistema
    let soundEnabled = (config.sound_enabled !== undefined && config.sound_enabled !== null) ? monitorSoundEnabled : systemSoundEnabled;
    
    console.log('Monitor: Configurações de notificações', {
        notificationsEnabled,
        soundEnabled,
        config: config
    });
    const notificationsContainer = document.getElementById('sale-notifications');
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

    let sinceNotifications = null;
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
    const AUTO_CLOSE_MS = parseInt(@json($configs['notifications_popup_auto_close_seconds'] ?? '7'), 10) * 1000;

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
        // Usar requestAnimationFrame para garantir que o DOM foi atualizado antes de tocar o som
        requestAnimationFrame(() => {
            playNotificationSound('sale_registered');
        });

        setTimeout(() => {
            if (toast.isConnected) {
                toast.remove();
                showNextToast();
            }
        }, AUTO_CLOSE_MS);

        // Tenta preencher o segundo slot imediatamente
        showNextToast();
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

        // Garantir que o AudioContext está inicializado antes de tocar
        try {
            getAudioContext();
        } catch (error) {
            console.error('Monitor: Erro ao inicializar AudioContext:', error);
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

        console.log('Monitor: Renderizando notificação', {
            id: sale?.id,
            created_at: sale?.created_at,
            seller: sale?.seller?.name
        });

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
        if (!notificationsEnabled) {
            return;
        }
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
        if (config.monitor_slug) {
            params.append('monitor', config.monitor_slug);
        }
        if (config.sector_id) {
            params.append('sector', config.sector_id);
        }
        params.append('limit', '20');

        try {
            const url = `/scores/recent?${params.toString()}`;
            console.log('Monitor: Buscando vendas recentes', { url });
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' },
            });

            if (!response.ok) {
                console.error('Monitor: Erro na resposta', { status: response.status, statusText: response.statusText });
                return;
            }

            const result = await response.json();
            const sales = result?.data || [];
            console.log('Monitor: Payload de vendas recebido', { count: sales.length, payload: sales });
            
            if (sales.length > 0) {
                sales.forEach(createSaleToast);
                const newestTimestamp = getNewestTimestamp(sales);
                if (newestTimestamp && newestTimestamp !== sinceNotifications) {
                    sinceNotifications = newestTimestamp;
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
            console.error('Monitor: Erro ao buscar vendas recentes:', error);
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
            // Não salvar no localStorage - manter apenas durante a sessão
            // Ao recarregar, volta para a configuração do monitor
            updateSoundButton();
        });
        updateSoundButton();
    }

    // Botão de leitura por voz manual
    const readVoiceBtn = document.getElementById('read-voice-btn');
    
    // Variável global para controlar se está falando (compartilhada entre leituras automáticas e manuais)
    window.isSpeaking = false;
    
    // Função para atualizar estado do botão (tornar global para leituras automáticas)
    window.updateVoiceButtonState = (isSpeaking) => {
        if (!readVoiceBtn) return;
        window.isSpeaking = isSpeaking;
        readVoiceBtn.disabled = isSpeaking;
        readVoiceBtn.classList.toggle('opacity-50', isSpeaking);
        readVoiceBtn.classList.toggle('cursor-not-allowed', isSpeaking);
        readVoiceBtn.classList.toggle('hover:bg-slate-700', !isSpeaking);
        readVoiceBtn.setAttribute('title', isSpeaking ? 'Leitura em andamento...' : 'Ler ranking por voz');
    };

    const voiceSyncState = {
        state: 'idle',
        previousAuto: null,
    };

    const setVoiceState = (nextState) => {
        voiceSyncState.state = nextState;
    };

    const stopAutoRefreshTimers = () => {
        if (refreshTimer) clearInterval(refreshTimer);
        if (countdownTimer) clearInterval(countdownTimer);
        refreshTimer = null;
        countdownTimer = null;
    };

    const enterVoiceMode = () => {
        voiceSyncState.previousAuto = {
            isPaused,
            remainingMs,
            currentTeamId,
        };
        setVoiceState('voice_mode');
        window.speechSynthesis.cancel();
        isPaused = true;
        stopAutoRefreshTimers();
        updateToggleIcon();
        updateCountdownLabel();
        updateVoiceButtonState(true);
    };

    const exitVoiceMode = async () => {
        const previous = voiceSyncState.previousAuto;
        setVoiceState('idle');
        if (previous) {
            currentTeamId = previous.currentTeamId ?? null;
            updateTeamChips();
            await fetchDashboardData();
            isPaused = previous.isPaused;
            remainingMs = previous.remainingMs;
        }
        updateToggleIcon();
        if (!isPaused) {
            startAutoRefresh(remainingMs);
        } else {
            updateCountdownLabel();
        }
        updateVoiceButtonState(false);
    };

    const ensureVoicesLoaded = () => {
        return new Promise((resolve) => {
            if (window.speechSynthesis.getVoices().length > 0) {
                resolve();
                return;
            }
            window.speechSynthesis.addEventListener('voiceschanged', () => resolve(), { once: true });
        });
    };

    // Configurações de voz do sistema (disponíveis no PHP)
    const browserVoiceName = @json(App\Models\Config::where('key', 'notifications_voice_browser_name')->value('value') ?? '');
    const systemVoiceEnabled = @json((App\Models\Config::where('key', 'notifications_voice_enabled')->value('value') ?? 'false') === 'true');
    const voiceScope = @json($configs['notifications_voice_scope'] ?? 'global');
    const voiceMode = @json(App\Models\Config::where('key', 'notifications_voice_mode')->value('value') ?? 'server');

    const getBrowserVoice = () => {
        if (!browserVoiceName) return null;
        const voices = window.speechSynthesis.getVoices();
        return voices.find(v => v.name === browserVoiceName) || null;
    };

    const speakText = (text) => {
        return new Promise((resolve) => {
            const utterance = new SpeechSynthesisUtterance(text);
            const voice = getBrowserVoice();
            if (voice) {
                utterance.voice = voice;
            }
            utterance.onend = () => resolve();
            utterance.onerror = () => resolve();
            updateVoiceButtonState(true);
            window.speechSynthesis.speak(utterance);
        });
    };
    
    const fetchVoiceContent = async ({ scope, teamId = null }) => {
                const url = new URL(`/monitor/${monitorSlug}/voice`, window.location.origin);
                url.searchParams.set('scope', scope);
                if (teamId) {
                    url.searchParams.set('team_id', teamId);
                }
                const response = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    console.warn('Monitor: Voz indisponível', { scope, teamId, error: errorData });
                    return null;
                }
                const result = await response.json();
                return result?.content || null;
            };

    const startVoiceSyncFlow = async () => {
                enterVoiceMode();
                try {
                    await ensureVoicesLoaded();

                    setVoiceState('reading_general');
                    currentTeamId = null;
                    updateTeamChips();
                    await fetchDashboardData();

                    const generalText = await fetchVoiceContent({ scope: 'global' });
                    if (!generalText) {
                        showCustomAlert(
                            'Sem ranking',
                            'Não foi possível gerar a leitura do ranking geral no momento.',
                            'warning'
                        );
                        return;
                    }
                    await speakText(generalText);

                    const shouldIncludeTeams = ['teams', 'both'].includes(voiceScope);
                    if (!shouldIncludeTeams) {
                        return;
                    }

                    const teamQueue = teamIdsRotation.filter((teamId) => teamId !== null);
                    for (const teamId of teamQueue) {
                        setVoiceState('reading_team');
                        currentTeamId = teamId;
                        updateTeamChips();
                        await fetchDashboardData();

                        const teamText = await fetchVoiceContent({ scope: 'team', teamId });
                        if (teamText) {
                            await speakText(teamText);
                        }
                    }
                } finally {
                    await exitVoiceMode();
                }
            };

    const startVoiceSequence = async ({ silent = false } = {}) => {
        if (window.isSpeaking || voiceSyncState.state !== 'idle') {
            return;
        }
        const config = window.DASHBOARD_CONFIG || {};
        const rawVoiceEnabled = config.voice_enabled;
        const monitorVoiceEnabled = rawVoiceEnabled === true ||
            rawVoiceEnabled === 'true' ||
            rawVoiceEnabled === 1 ||
            rawVoiceEnabled === '1';

        if (!monitorVoiceEnabled) {
            if (!silent) {
                showCustomAlert(
                    'Voz não habilitada no Monitor',
                    'A leitura por voz não está habilitada para este monitor. Para habilitar, edite o monitor e marque a opção "Leitura por voz habilitada".',
                    'warning'
                );
            }
            return;
        }

        if (!systemVoiceEnabled) {
            if (!silent) {
                showCustomAlert(
                    'Voz não habilitada no Sistema',
                    'A leitura por voz não está habilitada nas configurações gerais do sistema. Para habilitar, vá em Configurações > Notificações > Leitura por Voz e marque "Ativar leitura por voz".',
                    'warning'
                );
            }
            return;
        }

        if (!['browser', 'both'].includes(voiceMode)) {
            if (!silent) {
                showCustomAlert(
                    'Modo de Voz Incompatível',
                    'O modo de voz do sistema está configurado como "Servidor" apenas, o que não permite leitura no navegador. Para usar a leitura no monitor, configure o modo de voz como "Navegador" ou "Servidor + Navegador" nas configurações gerais.',
                    'warning'
                );
            }
            return;
        }

        if (!('speechSynthesis' in window)) {
            if (!silent) {
                alert('Seu navegador não suporta leitura por voz (SpeechSynthesis).');
            }
            return;
        }

        try {
            await startVoiceSyncFlow();
        } catch (error) {
            console.error('Monitor: Erro ao executar leitura por voz sincronizada:', error);
            if (!silent) {
                showCustomAlert(
                    'Erro ao executar leitura',
                    'Ocorreu um erro ao tentar sincronizar a leitura. Verifique o console para mais detalhes.',
                    'error'
                );
            }
            await exitVoiceMode();
        }
    };

    if (readVoiceBtn) {
        readVoiceBtn.addEventListener('click', async () => {
            await startVoiceSequence({ silent: false });
        });
    }

    // Leitura por voz automática baseada em intervalo
    const voiceIntervalMinutes = @json((int) ($configs['notifications_voice_interval_minutes'] ?? 15));
    const voiceOnlyWhenChanged = @json((($configs['notifications_voice_only_when_changed'] ?? 'false') === 'true'));
    let lastVoiceHash = null;

    const getMonitorVoiceEnabled = () => {
        const rawVoiceEnabled = (window.DASHBOARD_CONFIG || {}).voice_enabled;
        return rawVoiceEnabled === true ||
            rawVoiceEnabled === 'true' ||
            rawVoiceEnabled === 1 ||
            rawVoiceEnabled === '1';
    };

    const canUseBrowserVoice = () => {
        return getMonitorVoiceEnabled() &&
            systemVoiceEnabled &&
            ['browser', 'both'].includes(voiceMode) &&
            ('speechSynthesis' in window);
    };

    const hashText = (text) => {
        let hash = 0;
        for (let i = 0; i < text.length; i += 1) {
            hash = ((hash << 5) - hash) + text.charCodeAt(i);
            hash |= 0;
        }
        return String(hash);
    };

    const shouldSpeakByContent = async () => {
        if (!voiceOnlyWhenChanged) return true;
        const scope = ['teams', 'both', 'global'].includes(voiceScope) ? voiceScope : 'global';
        try {
            const url = new URL(`/monitor/${monitorSlug}/voice`, window.location.origin);
            url.searchParams.set('scope', scope);
            const response = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            if (!response.ok) {
                return false;
            }
            const result = await response.json();
            const content = result?.content || '';
            if (!content) return false;
            const nextHash = hashText(content);
            if (lastVoiceHash && lastVoiceHash === nextHash) {
                return false;
            }
            lastVoiceHash = nextHash;
            return true;
        } catch (error) {
            console.warn('Monitor: Falha ao validar leitura por voz:', error);
            return false;
        }
    };

    const startVoiceAutoTimer = () => {
        if (!canUseBrowserVoice()) {
            return;
        }
        const intervalMs = Math.max(1, Number(voiceIntervalMinutes || 15)) * 60000;
        let timerId = null;
        let nextExecution = Date.now() + intervalMs;
        let infoLogTimer = null;

        console.log('Monitor: Leitura por voz carregada', {
            intervalo_minutos: Math.max(1, Number(voiceIntervalMinutes || 15)),
            proxima_execucao: new Date(nextExecution).toLocaleTimeString('pt-BR')
        });

        const startInfoLog = () => {
            if (infoLogTimer) clearInterval(infoLogTimer);
            infoLogTimer = setInterval(() => {
                console.log('Monitor: Leitura por voz agendada', {
                    proxima_execucao: new Date(nextExecution).toLocaleTimeString('pt-BR'),
                    minutos_restantes: Math.max(0, Math.ceil((nextExecution - Date.now()) / 60000))
                });
            }, 60000);
        };

        const scheduleNext = (delayMs) => {
            if (timerId) clearTimeout(timerId);
            timerId = setTimeout(async () => {
                if (document.visibilityState === 'visible' && voiceSyncState.state === 'idle' && !window.isSpeaking) {
                    const canSpeak = await shouldSpeakByContent();
                    if (canSpeak) {
                        await startVoiceSequence({ silent: true });
                    }
                }
                    nextExecution = Date.now() + intervalMs;
                    scheduleNext(intervalMs);
            }, delayMs);
        };

            startInfoLog();
            scheduleNext(intervalMs);
    };

    startVoiceAutoTimer();

    // Controle de polling de notificações com detecção de visibilidade
    const startSalesPolling = () => {
        scheduleNextSalesPoll(0);
    };
    
    const stopSalesPolling = () => {
        if (salesPollingTimer) {
            clearTimeout(salesPollingTimer);
            salesPollingTimer = null;
        }
    };
    
    // Detectar mudanças de visibilidade da página
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            console.log('Monitor: Página visível, retomando polling de notificações');
            startSalesPolling();
        } else {
            // Quando a página perde o foco, pausar o polling
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
    /* Custom scrollbar */
    .custom-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: rgba(148, 163, 184, 0.3) transparent;
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: rgba(148, 163, 184, 0.3);
        border-radius: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background-color: rgba(148, 163, 184, 0.5);
    }

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
