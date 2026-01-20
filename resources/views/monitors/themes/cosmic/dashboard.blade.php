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
                {{ $team->name }}
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
    const monitorSlug = config.monitor_slug || @json($monitor->slug ?? '') || window.location.pathname.match(/\/monitor\/([^\/]+)/)?.[1] || '';
    
    let refreshInterval = {{ $dashboardConfig['refresh_interval'] ?? 60 }};
    let countdown = refreshInterval;
    let isRefreshing = true;
    let countdownInterval = null;
    let soundEnabled = {{ ($dashboardConfig['sound_enabled'] ?? false) ? 'true' : 'false' }};
    let teamRotationEnabled = {{ ($dashboardConfig['team_rotation_enabled'] ?? false) ? 'true' : 'false' }};
    let currentTeamIndex = 0;
    const teams = @json($teams ?? []);
    const notificationEventsConfig = @json($notificationEventsConfig ?? []);

    // ===== FUNÇÕES DE ÁUDIO =====
    function playSound(soundFile) {
        if (!soundEnabled) return;
        try {
            const audio = new Audio(`/sounds/${soundFile}`);
            audio.volume = 0.5;
            audio.play().catch(err => console.log('Erro ao reproduzir som:', err));
        } catch (error) {
            console.log('Erro ao criar áudio:', error);
        }
    }

    // ===== SISTEMA DE NOTIFICAÇÕES =====
    function showSaleNotification(sale) {
        const container = document.getElementById('sale-notifications');
        if (!container) return;

        const notification = document.createElement('div');
        notification.className = 'bg-slate-900/95 border-2 border-blue-500 rounded-xl p-4 shadow-2xl backdrop-blur-sm transform transition-all duration-500 pulse-animation';
        
        notification.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center flex-shrink-0 shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-white font-bold text-sm mb-1">🎉 Nova ${sale.event_type || 'venda'}!</h4>
                    <p class="text-slate-300 text-sm"><strong>${sale.participant_name}</strong></p>
                    <p class="text-blue-400 font-semibold">${sale.points} pontos</p>
                </div>
            </div>
        `;

        container.insertBefore(notification, container.firstChild);

        // Tocar som se configurado
        const eventConfig = notificationEventsConfig.find(e => e.event_type === sale.event_type);
        if (eventConfig?.sound) {
            playSound(eventConfig.sound);
        }

        // Criar confete
        createConfetti();

        // Remover após 5 segundos
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => notification.remove(), 500);
        }, 5000);
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

            // Verificar novas vendas
            if (data.new_sales && data.new_sales.length > 0) {
                data.new_sales.forEach(sale => showSaleNotification(sale));
            }

        } catch (error) {
            console.error('Erro ao atualizar dashboard:', error);
        }
    }

    // ===== COUNTDOWN =====
    function startCountdown() {
        if (countdownInterval) clearInterval(countdownInterval);
        
        countdown = refreshInterval;
        const countdownEl = document.getElementById('refresh-countdown');
        
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
        if (voiceBtn) {
            voiceBtn.addEventListener('click', function() {
                showCustomAlert('Leitura por Voz', 'Recurso em desenvolvimento', 'info');
            });
        }

        // Seleção de equipes
        const teamChips = document.querySelectorAll('#team-chips button');
        teamChips.forEach(chip => {
            chip.addEventListener('click', function() {
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
            setInterval(rotateTeams, {{ $dashboardConfig['team_rotation_interval'] ?? 30 }} * 1000);
        }
    });
</script>
@endpush
@endsection
