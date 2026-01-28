@extends('monitors.themes.cosmic.layout')

@php
    $saleTerm = $configs['sale_term'] ?? 'Venda';
    $saleTermLower = strtolower($saleTerm);
@endphp

@section('content')
<div class="w-full h-full relative" style="z-index: 10;">
    
    <!-- Container de notificações de vendas -->
    <div id="sale-notifications" class="fixed top-4 right-4 z-50 space-y-2" style="max-width: 400px;"></div>

    <!-- Header com controles -->
    <div class="px-6 py-4 bg-slate-900/30 backdrop-blur-sm border-b border-slate-700/50">
        <div class="flex items-center justify-between">
            <!-- Título -->
            <div class="flex items-center gap-3">
                <svg class="w-8 h-8 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <div>
                    <h1 class="text-2xl font-bold text-white">Ranking de {{ $saleTermLower }}</h1>
                    <p class="text-slate-400 text-sm">Por pontuação</p>
                </div>
            </div>

            <!-- Controles -->
            <div class="flex items-center gap-3">
                <!-- Contador de atualização -->
                <div class="flex items-center gap-2 bg-slate-800/50 backdrop-blur-sm px-4 py-2 rounded-lg border border-slate-700/50">
                    <button id="toggle-refresh" class="text-white hover:text-blue-400 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                    <span id="refresh-countdown" class="text-white text-sm font-semibold">60s</span>
                </div>

                <!-- Botão de som -->
                <button id="toggle-sound-btn" class="bg-slate-800/50 backdrop-blur-sm px-4 py-2 rounded-lg border border-slate-700/50 text-white hover:bg-slate-700/50 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                    </svg>
                </button>

                <!-- Botão de leitura por voz -->
                <button id="read-voice-btn" class="bg-slate-800/50 backdrop-blur-sm px-4 py-2 rounded-lg border border-slate-700/50 text-white hover:bg-slate-700/50 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Seleção de equipes -->
        @if($teams && $teams->count() > 1)
        <div class="flex gap-2 mt-4" id="team-chips">
            <button data-team-id="all" class="px-4 py-2 rounded-lg text-sm font-semibold transition bg-blue-600 text-white">
                Todas
            </button>
            @foreach($teams as $team)
            <button data-team-id="{{ $team->id }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition bg-slate-700/50 text-white hover:bg-slate-600/50">
                {{ $team->display_label }}
            </button>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Grid Principal -->
    <div class="grid grid-cols-12 gap-6 px-6 py-6 h-[calc(100vh-140px)] overflow-hidden">
        
        <!-- Sidebar Esquerda - Ranking Completo -->
        <div class="col-span-3 space-y-3 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent" id="ranking-sidebar">
            @include('dashboard.partials.ranking', ['ranking' => $ranking, 'activeTeam' => $activeTeam])
        </div>

        <!-- Área Central - Pódio -->
        <div class="col-span-6 flex items-end justify-center" id="podium-area">
            @include('dashboard.partials.podium', ['top3' => $top3])
        </div>

        <!-- Sidebar Direita - Estatísticas -->
        <div class="col-span-3 space-y-4">
            <!-- Card de estatísticas -->
            <div class="bg-slate-800/50 backdrop-blur-sm rounded-xl p-6 border border-slate-700/50">
                <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                    </svg>
                    Estatísticas
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 text-sm">Total de Pontos</span>
                        <span class="text-white font-bold">{{ number_format($stats['totalPoints'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 text-sm">Participantes</span>
                        <span class="text-white font-bold">{{ $stats['totalParticipants'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 text-sm">Meta Atingida</span>
                        <span class="text-green-400 font-bold">{{ number_format($percentage ?? 0, 1) }}%</span>
                    </div>
                </div>
            </div>

            <!-- Intervalo de atualização -->
            <div class="bg-slate-800/50 backdrop-blur-sm rounded-xl p-6 border border-slate-700/50">
                <h3 class="text-white font-semibold mb-4">Intervalo de Atualização</h3>
                <div class="space-y-2">
                    <button data-refresh-interval="30" data-refresh-label="30s" class="w-full px-4 py-2 rounded-lg text-sm font-semibold transition bg-slate-700/50 text-white hover:bg-slate-600/50">
                        30 segundos
                    </button>
                    <button data-refresh-interval="60" data-refresh-label="60s" class="w-full px-4 py-2 rounded-lg text-sm font-semibold transition bg-blue-600 text-white">
                        1 minuto
                    </button>
                    <button data-refresh-interval="120" data-refresh-label="2m" class="w-full px-4 py-2 rounded-lg text-sm font-semibold transition bg-slate-700/50 text-white hover:bg-slate-600/50">
                        2 minutos
                    </button>
                    <button data-refresh-interval="300" data-refresh-label="5m" class="w-full px-4 py-2 rounded-lg text-sm font-semibold transition bg-slate-700/50 text-white hover:bg-slate-600/50">
                        5 minutos
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Gradientes para os cards do ranking */
    .gradient-gold {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #d97706 100%);
    }
    .gradient-silver {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 50%, #0e7490 100%);
    }
    .gradient-bronze {
        background: linear-gradient(135deg, #a855f7 0%, #9333ea 50%, #7e22ce 100%);
    }

    /* Scrollbar personalizada */
    .scrollbar-thin::-webkit-scrollbar {
        width: 6px;
    }
    .scrollbar-thin::-webkit-scrollbar-track {
        background: transparent;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb {
        background: rgb(51 65 85);
        border-radius: 3px;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb:hover {
        background: rgb(71 85 105);
    }

    /* Animação de pulso para notificações */
    @keyframes pulse-ring {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
        }
    }

    .pulse-animation {
        animation: pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }
</style>
@endpush

@push('scripts')
<script>
    // ===== CONFIGURAÇÃO INICIAL =====
    const config = window.DASHBOARD_CONFIG || {};
    window.MONITOR_VOICE_AUTOMATION_HANDLED = true;
    const monitorSlug = config.monitor_slug || @json($monitor->slug ?? '') || window.location.pathname.match(/\/monitor\/([^\/]+)/)?.[1] || '';

    const parseBoolean = (value) => {
        if (value === true || value === false) return value;
        if (value === 1 || value === '1') return true;
        if (value === 0 || value === '0') return false;
        if (typeof value === 'string') {
            return ['true', 'yes', 'on'].includes(value.toLowerCase());
        }
        return Boolean(value);
    };
    
    let refreshInterval = {{ $dashboardConfig['refresh_interval'] ?? 60 }};
    let countdown = refreshInterval;
    let isRefreshing = true;
    let countdownInterval = null;
    let teamRotationInterval = null;
    let soundEnabled = {{ ($dashboardConfig['sound_enabled'] ?? false) ? 'true' : 'false' }};
    let teamRotationEnabled = {{ ($dashboardConfig['team_rotation_enabled'] ?? false) ? 'true' : 'false' }};
    let currentTeamIndex = 0;
    const teams = @json($teams ?? []);
    const notificationEventsConfig = @json($notificationEventsConfig ?? []);
    const voiceScope = @json($configs['notifications_voice_scope'] ?? 'global');
    const voiceIntervalMinutes = @json((int) ($configs['notifications_voice_interval_minutes'] ?? 15));
    const voiceOnlyWhenChanged = @json((($configs['notifications_voice_only_when_changed'] ?? 'false') === 'true'));
    let lastVoiceHash = null;
    let voiceButtonEl = null;

    const hasMonitorNotificationsSetting = config.notifications_enabled !== undefined && config.notifications_enabled !== null;
    const systemNotificationsEnabled = @json((($configs['notifications_system_enabled'] ?? 'true') === 'true'));
    const notificationsEnabled = systemNotificationsEnabled && (hasMonitorNotificationsSetting ? parseBoolean(config.notifications_enabled) : true);

    const systemSoundEnabled = @json((($configs['notifications_sound_enabled'] ?? 'true') === 'true'));
    const monitorSoundEnabled = parseBoolean(config.sound_enabled);
    soundEnabled = (config.sound_enabled !== undefined && config.sound_enabled !== null) ? monitorSoundEnabled : systemSoundEnabled;

    window.isSpeaking = false;

    const voiceSyncState = {
        state: 'idle',
        previousAuto: null,
    };

    const setVoiceState = (nextState) => {
        voiceSyncState.state = nextState;
    };

    const updateVoiceButtonState = (isSpeaking) => {
        if (!voiceButtonEl) return;
        window.isSpeaking = isSpeaking;
        voiceButtonEl.disabled = isSpeaking;
        voiceButtonEl.classList.toggle('opacity-50', isSpeaking);
        voiceButtonEl.classList.toggle('cursor-not-allowed', isSpeaking);
        voiceButtonEl.setAttribute('title', isSpeaking ? 'Leitura em andamento...' : 'Ler ranking por voz');
    };

    // ===== FUNÇÕES DE ÁUDIO =====
    let globalAudioContext = null;

    const getAudioContext = () => {
        if (!globalAudioContext) {
            globalAudioContext = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (globalAudioContext.state === 'suspended') {
            globalAudioContext.resume();
        }
        return globalAudioContext;
    };

    function playSound(type) {
        if (!soundEnabled) return;
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
            console.log('Erro ao tocar som:', error);
        }
    }

    // ===== SISTEMA DE NOTIFICAÇÕES =====
    const saleTermLabel = @json($saleTerm);
    const saleTermLower = @json($saleTermLower);

    const formatPoints = (points) => {
        const value = Number(points || 0);
        return value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    };

    const MAX_VISIBLE_TOASTS = parseInt(@json($configs['notifications_popup_max_count'] ?? '2'), 10);
    const AUTO_CLOSE_MS = parseInt(@json($configs['notifications_popup_auto_close_seconds'] ?? '7'), 10) * 1000;
    const toastQueue = [];

    const showNextToast = () => {
        const container = document.getElementById('sale-notifications');
        if (!container) return;
        if (toastQueue.length === 0) return;
        if (container.querySelectorAll('[data-toast="sale"]').length >= MAX_VISIBLE_TOASTS) return;
        const sale = toastQueue.shift();
        renderSaleToast(sale);
    };

    function renderSaleToast(sale) {
        const container = document.getElementById('sale-notifications');
        if (!container) return;

        console.log('Monitor: Renderizando notificação', {
            id: sale?.id,
            created_at: sale?.created_at,
            seller: sale?.seller?.name
        });

        const sellerName = sale?.seller?.name || 'Colaborador';
        const occurrenceLabel = sale?.occurrence?.type || `${saleTermLabel} registrada`;
        const pointsLabel = formatPoints(sale?.points);

        const notification = document.createElement('div');
        notification.dataset.toast = 'sale';
        notification.className = 'bg-slate-900/95 border-2 border-blue-500 rounded-xl p-4 shadow-2xl backdrop-blur-sm transform transition-all duration-500 pulse-animation';
        
        notification.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center flex-shrink-0 shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-white font-bold text-sm mb-1">🎉 Nova ${saleTermLower} registrada!</h4>
                    <p class="text-slate-300 text-sm"><strong>${sellerName}</strong></p>
                    <p class="text-slate-400 text-xs">${occurrenceLabel}</p>
                    <p class="text-blue-400 font-semibold">+${pointsLabel} pontos</p>
                </div>
            </div>
        `;

        container.insertBefore(notification, container.firstChild);

        // Tocar som se configurado
        const eventConfig = notificationEventsConfig?.sale_registered;
        if (eventConfig?.sound) {
            playSound('notification');
        }

        // Criar confete
        createConfetti();

        // Remover após tempo configurado
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => {
                notification.remove();
                showNextToast();
            }, 500);
        }, AUTO_CLOSE_MS);
    }

    function showSaleNotification(sale) {
        if (!document.getElementById('sale-notifications')) return;
        toastQueue.push(sale);
        showNextToast();
    }

    // ===== CONFETE =====
    function createConfetti() {
        const colors = ['#fbbf24', '#06b6d4', '#a855f7', '#ec4899', '#10b981'];
        const container = document.body;
        
        for (let i = 0; i < 30; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
            confetti.style.animationDelay = (Math.random() * 0.5) + 's';
            container.appendChild(confetti);
            
            setTimeout(() => confetti.remove(), 5000);
        }
    }

    const SALES_POLLING_MIN_INTERVAL = 4000;
    const SALES_POLLING_MAX_INTERVAL = 60000;
    const SALES_POLLING_BACKOFF_FACTOR = 1.5;
    let sinceNotifications = null;
    let salesPollingTimer = null;
    let salesPollingDelay = SALES_POLLING_MIN_INTERVAL;
    let salesPollingInFlight = false;

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
        if (!notificationsEnabled || salesPollingInFlight) {
            return;
        }
        if (document.visibilityState !== 'visible') {
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
                return;
            }
            const result = await response.json();
            const sales = result?.data || [];
            console.log('Monitor: Payload de vendas recebido', { count: sales.length, payload: sales });
            if (sales.length > 0) {
                sales.forEach(showSaleNotification);
                const newest = sales[0]?.created_at;
                if (newest && newest !== sinceNotifications) {
                    sinceNotifications = newest;
                }
                updateSalesPollingDelay(true);
            } else {
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

    // ===== ATUALIZAÇÃO DO DASHBOARD =====
    async function refreshDashboard(teamId = 'all') {
        try {
            const url = `/monitor/${monitorSlug}/data?team=${teamId}`;
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();

            // Atualizar ranking
            const rankingContainer = document.getElementById('ranking-sidebar');
            if (rankingContainer && data.rankingHtml) {
                rankingContainer.innerHTML = data.rankingHtml;
            }

            // Atualizar pódio
            const podiumContainer = document.getElementById('podium-area');
            if (podiumContainer && data.podiumHtml) {
                podiumContainer.innerHTML = data.podiumHtml;
            }

        } catch (error) {
            console.error('Erro ao atualizar dashboard:', error);
        }
    }

    // ===== COUNTDOWN =====
    function startCountdown(initialCountdown = null) {
        if (countdownInterval) clearInterval(countdownInterval);
        
        countdown = typeof initialCountdown === 'number' ? initialCountdown : refreshInterval;
        const countdownEl = document.getElementById('refresh-countdown');
        if (countdownEl) {
            const minutes = Math.floor(countdown / 60);
            const seconds = countdown % 60;
            countdownEl.textContent = minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;
        }
        
        countdownInterval = setInterval(() => {
            if (isRefreshing) {
                countdown--;
                if (countdownEl) {
                    const minutes = Math.floor(countdown / 60);
                    const seconds = countdown % 60;
                    countdownEl.textContent = minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;
                }
                
                if (countdown <= 0) {
                    refreshDashboard(getCurrentTeamId());
                    countdown = refreshInterval;
                }
            }
        }, 1000);
    }

    // ===== ROTAÇÃO DE EQUIPES =====
    function getCurrentTeamId() {
        if (!teamRotationEnabled || teams.length === 0) return 'all';
        const activeChip = document.querySelector('#team-chips button.bg-blue-600');
        return activeChip ? activeChip.dataset.teamId : 'all';
    }

    function rotateTeams() {
        if (voiceSyncState.state !== 'idle') return;
        if (!teamRotationEnabled || teams.length === 0) return;
        
        currentTeamIndex = (currentTeamIndex + 1) % (teams.length + 1);
        const teamId = currentTeamIndex === 0 ? 'all' : teams[currentTeamIndex - 1].id;
        
        selectTeam(teamId);
        refreshDashboard(teamId);
    }

    function selectTeam(teamId) {
        const chips = document.querySelectorAll('#team-chips button');
        chips.forEach(chip => {
            if (chip.dataset.teamId == teamId) {
                chip.className = 'px-4 py-2 rounded-lg text-sm font-semibold transition bg-blue-600 text-white';
            } else {
                chip.className = 'px-4 py-2 rounded-lg text-sm font-semibold transition bg-slate-700/50 text-white hover:bg-slate-600/50';
            }
        });
    }

    const pauseAutoFlowForVoice = () => {
        voiceSyncState.previousAuto = {
            isRefreshing,
            countdown,
            currentTeamId: getCurrentTeamId(),
        };
        setVoiceState('voice_mode');
        window.speechSynthesis.cancel();
        isRefreshing = false;
        if (countdownInterval) clearInterval(countdownInterval);
        if (teamRotationInterval) clearInterval(teamRotationInterval);
        updateVoiceButtonState(true);
    };

    const restoreAutoFlowAfterVoice = async () => {
        const previous = voiceSyncState.previousAuto;
        setVoiceState('idle');
        if (previous) {
            const previousTeamId = previous.currentTeamId || 'all';
            selectTeam(previousTeamId);
            await refreshDashboard(previousTeamId);
            isRefreshing = previous.isRefreshing;
            if (isRefreshing) {
                startCountdown(previous.countdown);
            } else {
                const countdownEl = document.getElementById('refresh-countdown');
                if (countdownEl) {
                    const minutes = Math.floor(previous.countdown / 60);
                    const seconds = previous.countdown % 60;
                    countdownEl.textContent = minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;
                }
            }
        }
        if (teamRotationEnabled) {
            teamRotationInterval = setInterval(rotateTeams, {{ $dashboardConfig['team_rotation_interval'] ?? 30 }} * 1000);
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

    // ===== EVENT LISTENERS =====
    document.addEventListener('DOMContentLoaded', function() {
        // Play/Pause
        const toggleBtn = document.getElementById('toggle-refresh');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                isRefreshing = !isRefreshing;
                this.innerHTML = isRefreshing 
                    ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                    : '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
            });
        }

        // Som
        const soundBtn = document.getElementById('toggle-sound-btn');
        if (soundBtn) {
            soundBtn.addEventListener('click', function() {
                soundEnabled = !soundEnabled;
                this.style.opacity = soundEnabled ? '1' : '0.5';
                showCustomAlert('Som', soundEnabled ? 'Som ativado' : 'Som desativado', 'info');
            });
        }

        // Leitura por voz
        const voiceBtn = document.getElementById('read-voice-btn');
        const startVoiceSyncFlow = async () => {
                    pauseAutoFlowForVoice();
                    try {
                        await ensureVoicesLoaded();

                        setVoiceState('reading_general');
                        selectTeam('all');
                        await refreshDashboard('all');

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

                        for (const team of teams) {
                            setVoiceState('reading_team');
                            selectTeam(team.id);
                            await refreshDashboard(team.id);

                            const teamText = await fetchVoiceContent({ scope: 'team', teamId: team.id });
                            if (teamText) {
                                await speakText(teamText);
                            }
                        }
                    } finally {
                        await restoreAutoFlowAfterVoice();
                    }
                };

        const startVoiceSequence = async ({ silent = false } = {}) => {
            if (window.isSpeaking || voiceSyncState.state !== 'idle') {
                return;
            }

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
                await restoreAutoFlowAfterVoice();
            }
        };

        if (voiceBtn) {
            voiceButtonEl = voiceBtn;

            voiceBtn.addEventListener('click', async () => {
                await startVoiceSequence({ silent: false });
            });
        }

        // Leitura por voz automática baseada em intervalo
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

        // Seleção de equipes
        const teamChips = document.querySelectorAll('#team-chips button');
        teamChips.forEach(chip => {
            chip.addEventListener('click', function() {
                if (voiceSyncState.state !== 'idle') {
                    return;
                }
                const teamId = this.dataset.teamId;
                selectTeam(teamId);
                refreshDashboard(teamId);
            });
        });

        // Intervalo de atualização
        const intervalBtns = document.querySelectorAll('[data-refresh-interval]');
        intervalBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                refreshInterval = parseInt(this.dataset.refreshInterval);
                countdown = refreshInterval;
                
                intervalBtns.forEach(b => {
                    b.className = 'w-full px-4 py-2 rounded-lg text-sm font-semibold transition bg-slate-700/50 text-white hover:bg-slate-600/50';
                });
                this.className = 'w-full px-4 py-2 rounded-lg text-sm font-semibold transition bg-blue-600 text-white';
            });
        });

        // Iniciar countdown
        startCountdown();

        // Rotação de equipes (se habilitado)
        if (teamRotationEnabled) {
            teamRotationInterval = setInterval(rotateTeams, {{ $dashboardConfig['team_rotation_interval'] ?? 30 }} * 1000);
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
                startSalesPolling();
            } else {
                stopSalesPolling();
            }
        });

        if (notificationsEnabled) {
            startSalesPolling();
        }
    });
</script>
@endpush
@endsection
