<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $monitor->name ?? 'Monitor' }} - Ranking NEF</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css'])
    <style>
        /* Fullscreen para TV */
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        /* Escala de fonte baseada na configuração do monitor */
        body {
            font-size: calc(1rem * {{ $dashboardConfig['font_scale'] ?? 1.0 }});
        }
    </style>
    @stack('styles')
</head>
<body class="font-sans antialiased bg-[#0a0e1a] w-screen h-screen overflow-hidden">
    <!-- Modal de alerta personalizado -->
    <div id="custom-alert-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" onclick="closeCustomAlert()"></div>
        <div class="relative bg-slate-900/95 border border-slate-700/60 rounded-xl shadow-xl backdrop-blur-sm p-6 max-w-md w-full">
            <div class="flex items-start gap-4">
                <div id="alert-icon" class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"></div>
                <div class="flex-1">
                    <h3 id="alert-title" class="text-lg font-semibold text-white mb-2"></h3>
                    <p id="alert-message" class="text-slate-300 text-sm mb-4"></p>
                    <div class="flex justify-end">
                        <button onclick="closeCustomAlert()" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuração global do dashboard para o monitor (definir antes dos scripts)
        window.DASHBOARD_CONFIG = @json($dashboardConfig ?? []);
        
        // Debug: Log da configuração carregada
        console.log('Monitor: DASHBOARD_CONFIG carregado', {
            config: window.DASHBOARD_CONFIG,
            voice_enabled: window.DASHBOARD_CONFIG?.voice_enabled,
            voice_enabled_type: typeof window.DASHBOARD_CONFIG?.voice_enabled,
            voice_enabled_stringified: JSON.stringify(window.DASHBOARD_CONFIG?.voice_enabled)
        });

        // Sistema de alertas personalizados
        function showCustomAlert(title, message, type = 'info') {
            const modal = document.getElementById('custom-alert-modal');
            const alertTitle = document.getElementById('alert-title');
            const alertMessage = document.getElementById('alert-message');
            const alertIcon = document.getElementById('alert-icon');

            alertTitle.textContent = title || 'Aviso';
            alertMessage.textContent = message || '';

            // Configurar ícone baseado no tipo
            alertIcon.className = 'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center';
            alertIcon.innerHTML = '';

            const iconSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            iconSvg.setAttribute('class', 'w-6 h-6');
            iconSvg.setAttribute('fill', 'none');
            iconSvg.setAttribute('stroke', 'currentColor');
            iconSvg.setAttribute('viewBox', '0 0 24 24');

            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('stroke-linecap', 'round');
            path.setAttribute('stroke-linejoin', 'round');
            path.setAttribute('stroke-width', '2');

            if (type === 'error' || type === 'danger') {
                alertIcon.classList.add('bg-red-600/20');
                path.setAttribute('d', 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z');
                iconSvg.classList.add('text-red-400');
            } else if (type === 'success') {
                alertIcon.classList.add('bg-green-600/20');
                path.setAttribute('d', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z');
                iconSvg.classList.add('text-green-400');
            } else if (type === 'warning') {
                alertIcon.classList.add('bg-yellow-600/20');
                path.setAttribute('d', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z');
                iconSvg.classList.add('text-yellow-400');
            } else {
                alertIcon.classList.add('bg-blue-600/20');
                path.setAttribute('d', 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z');
                iconSvg.classList.add('text-blue-400');
            }

            iconSvg.appendChild(path);
            alertIcon.appendChild(iconSvg);

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeCustomAlert() {
            const modal = document.getElementById('custom-alert-modal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Substituir alert() padrão
        window.originalAlert = window.alert;
        window.alert = function(message) {
            showCustomAlert('Aviso', message, 'info');
        };
    </script>
    
    @yield('content')

    @stack('scripts')
    
    {{-- Leitura por voz do ranking --}}
    @php
        $settings = $monitor->getMergedSettings();
        $monitorVoiceEnabled = ($settings['voice_enabled'] ?? false) === true || ($settings['voice_enabled'] ?? false) === 'true' || ($settings['voice_enabled'] ?? false) === 1;
        $systemVoiceEnabled = (App\Models\Config::where('key', 'notifications_voice_enabled')->value('value') ?? 'false') === 'true';
        $voiceMode = App\Models\Config::where('key', 'notifications_voice_mode')->value('value') ?? 'server';
        $browserVoiceName = App\Models\Config::where('key', 'notifications_voice_browser_name')->value('value') ?? '';
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.MONITOR_VOICE_AUTOMATION_HANDLED) {
                return;
            }

            // Verificar se voz está habilitada no monitor E no sistema
            const monitorVoiceEnabled = @json($monitorVoiceEnabled);
            const systemVoiceEnabled = @json($systemVoiceEnabled);
            const voiceMode = @json($voiceMode);
            const browserVoiceName = @json($browserVoiceName);
            const voiceIntervalMinutes = @json((int) (App\Models\Config::where('key', 'notifications_voice_interval_minutes')->value('value') ?? 15));

            // Voz só funciona se habilitada no monitor E no sistema E modo browser/both
            if (!monitorVoiceEnabled || !systemVoiceEnabled || !['browser', 'both'].includes(voiceMode)) {
                console.log('Monitor: Leitura por voz desabilitada', {
                    monitorVoiceEnabled,
                    systemVoiceEnabled,
                    voiceMode
                });
                return;
            }

            if (!('speechSynthesis' in window)) {
                console.warn('Monitor: SpeechSynthesis não suportado no navegador');
                return;
            }

            const monitorSlug = window.DASHBOARD_CONFIG?.monitor_slug || '';
            const voiceScope = @json($configs['notifications_voice_scope'] ?? 'global');
            const voiceOnlyWhenChanged = @json((($configs['notifications_voice_only_when_changed'] ?? 'false') === 'true'));
            let lastVoiceHash = null;

            let voicesCache = [];
            const updateVoices = () => {
                voicesCache = window.speechSynthesis.getVoices() || [];
            };
            updateVoices();
            window.speechSynthesis.addEventListener('voiceschanged', updateVoices);

            const resolveVoice = () => {
                if (!browserVoiceName) return null;
                return voicesCache.find((voice) => voice.name === browserVoiceName) || null;
            };

            const queue = [];
            let speaking = false;

            const speakNext = () => {
                if (!queue.length) {
                    speaking = false;
                    if (typeof window.updateVoiceButtonState === 'function') {
                        window.updateVoiceButtonState(false);
                    }
                    return;
                }

                speaking = true;
                if (typeof window.updateVoiceButtonState === 'function') {
                    window.updateVoiceButtonState(true);
                }

                const text = queue.shift();
                const utterance = new SpeechSynthesisUtterance(text);
                const voice = resolveVoice();

                if (voice) {
                    utterance.voice = voice;
                }

                utterance.onend = speakNext;
                utterance.onerror = speakNext;

                window.speechSynthesis.speak(utterance);
            };

            const enqueue = (text) => {
                if (!text) return;
                queue.push(text);
                if (!speaking) {
                    speakNext();
                }
            };

            const hashText = (text) => {
                let hash = 0;
                for (let i = 0; i < text.length; i += 1) {
                    hash = ((hash << 5) - hash) + text.charCodeAt(i);
                    hash |= 0;
                }
                return String(hash);
            };

            const fetchVoiceContent = async () => {
                if (document.visibilityState !== 'visible' || speaking) {
                    return;
                }
                try {
                    const normalizedScope = ['global', 'teams', 'both'].includes(voiceScope) ? voiceScope : 'global';
                    const url = new URL(`/monitor/${monitorSlug}/voice`, window.location.origin);
                    url.searchParams.set('scope', normalizedScope);
                    const response = await fetch(url.toString(), {
                        headers: { 'Accept': 'application/json' },
                    });

                    if (!response.ok) return;

                    const result = await response.json();
                    const content = result?.content || '';
                    if (!content) return;

                    if (voiceOnlyWhenChanged) {
                        const nextHash = hashText(content);
                        if (lastVoiceHash && lastVoiceHash === nextHash) {
                            return;
                        }
                        lastVoiceHash = nextHash;
                    }

                    enqueue(content);
                } catch (error) {
                    console.error('Monitor: Erro ao buscar leitura por voz:', error);
                }
            };

            const startVoiceTimer = () => {
                const intervalMs = Math.max(1, Number(voiceIntervalMinutes || 15)) * 60000;
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

                const scheduleNext = () => {
                    const delayMs = Math.max(0, nextExecution - Date.now());
                    setTimeout(async () => {
                        await fetchVoiceContent();
                        nextExecution = Date.now() + intervalMs;
                        scheduleNext();
                    }, delayMs);
                };

                startInfoLog();
                scheduleNext();
            };

            console.log('Monitor: Leitura por voz habilitada');
            startVoiceTimer();
        });
    </script>
</body>
</html>
