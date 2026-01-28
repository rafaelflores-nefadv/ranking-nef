<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Ranking NEF'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css'])
    @stack('styles')
    <style>
        /* Scrollbar customizada global */
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

        /* Scrollbar customizada para elementos com overflow */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(59, 130, 246, 0.6) rgba(15, 23, 42, 0.6);
        }

        *::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        *::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.6);
        }

        *::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.6);
            border-radius: 999px;
            border: 2px solid rgba(15, 23, 42, 0.6);
        }

        *::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.85);
        }
    </style>
</head>
<body class="font-sans antialiased bg-[#0a0e1a] min-h-screen">
    <!-- Modais personalizados -->
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

    <div id="custom-confirm-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" onclick="closeCustomConfirm(false)"></div>
        <div class="relative bg-slate-900/95 border border-slate-700/60 rounded-xl shadow-xl backdrop-blur-sm p-6 max-w-md w-full">
            <div class="flex items-start gap-4">
                <div id="confirm-icon" class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center bg-yellow-600/20">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 id="confirm-title" class="text-lg font-semibold text-white mb-2">Confirmar ação</h3>
                    <p id="confirm-message" class="text-slate-300 text-sm mb-4"></p>
                    <div class="flex justify-end gap-2">
                        <button onclick="closeCustomConfirm(false)" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold">
                            Cancelar
                        </button>
                        <button onclick="closeCustomConfirm(true)" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold">
                            Confirmar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sistema de alertas e confirmações personalizados
        let customConfirmResolve = null;

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

            // Fechar com ESC
            const handleEsc = (e) => {
                if (e.key === 'Escape') {
                    closeCustomAlert();
                    document.removeEventListener('keydown', handleEsc);
                }
            };
            document.addEventListener('keydown', handleEsc);
        }

        function closeCustomAlert() {
            const modal = document.getElementById('custom-alert-modal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function showCustomConfirm(message, title = 'Confirmar ação') {
            return new Promise((resolve) => {
                customConfirmResolve = resolve;
                const modal = document.getElementById('custom-confirm-modal');
                const confirmTitle = document.getElementById('confirm-title');
                const confirmMessage = document.getElementById('confirm-message');

                confirmTitle.textContent = title;
                confirmMessage.textContent = message || 'Tem certeza que deseja continuar?';

                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                // Fechar com ESC (cancela)
                const handleEsc = (e) => {
                    if (e.key === 'Escape') {
                        closeCustomConfirm(false);
                        document.removeEventListener('keydown', handleEsc);
                    }
                };
                document.addEventListener('keydown', handleEsc);
            });
        }

        function closeCustomConfirm(result) {
            const modal = document.getElementById('custom-confirm-modal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            
            if (customConfirmResolve) {
                customConfirmResolve(result);
                customConfirmResolve = null;
            }
        }

        // Substituir alert() e confirm() padrão
        window.originalAlert = window.alert;
        window.originalConfirm = window.confirm;

        window.alert = function(message) {
            showCustomAlert('Aviso', message, 'info');
        };

        window.confirm = function(message) {
            return showCustomConfirm(message, 'Confirmar ação');
        };

        // Função auxiliar para formulários com onsubmit
        async function handleDeleteConfirm(event, message, title = 'Confirmar exclusão') {
            event.preventDefault();
            const form = event.target;
            const confirmed = await showCustomConfirm(message, title);
            if (confirmed) {
                form.submit();
            }
            return false;
        }
    </script>
    @auth
        @include('components.navigation')
    @endauth

    <main>
        @yield('content')
    </main>

    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dropdown = document.getElementById('notifications-dropdown');
            const badge = document.getElementById('notifications-badge');
            const list = document.getElementById('notifications-list');
            const empty = document.getElementById('notifications-empty');

            if (!dropdown || !badge || !list || !empty) return;

            const STORAGE_KEY = 'ranking_notifications_last_seen';
            let lastSeen = localStorage.getItem(STORAGE_KEY);
            let latestTimestamp = null;

            const saleTerm = list.dataset.saleTerm || 'Venda';
            const saleTermLower = saleTerm.toLowerCase();

            const formatPoints = (points) => {
                const value = Number(points || 0);
                return value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            };

            const formatTime = (iso) => {
                if (!iso) return '';
                const date = new Date(iso);
                return date.toLocaleString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            };

            const renderNotifications = (items) => {
                list.innerHTML = '';
                if (!items.length) {
                    empty.classList.remove('hidden');
                    return;
                }

                empty.classList.add('hidden');

                items.forEach((sale) => {
                    const sellerName = sale?.seller?.name || 'Colaborador';
                    const occurrenceLabel = sale?.occurrence?.type || `${saleTerm} registrada`;
                    const pointsLabel = formatPoints(sale?.points);
                    const timeLabel = formatTime(sale?.created_at);

                    const item = document.createElement('div');
                    item.className = 'px-4 py-3 text-sm text-slate-300 border-t border-slate-800/60';
                    item.innerHTML = `
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-semibold text-white">Nova ${saleTermLower}</span>
                            <span class="text-xs text-slate-500">${timeLabel}</span>
                        </div>
                        <div class="text-xs text-slate-400 mt-1">${occurrenceLabel}</div>
                        <div class="text-xs text-blue-300 mt-1">+${pointsLabel} pontos para ${sellerName}</div>
                    `;
                    list.appendChild(item);
                });
            };

            const updateBadge = (items) => {
                if (!items.length) {
                    badge.classList.add('hidden');
                    badge.textContent = '';
                    return;
                }

                const unread = lastSeen
                    ? items.filter((sale) => sale?.created_at && sale.created_at > lastSeen).length
                    : items.length;

                if (unread > 0) {
                    badge.classList.remove('hidden');
                    if (unread > 3) {
                        badge.textContent = '3+';
                    } else {
                        badge.textContent = String(unread);
                    }
                } else {
                    badge.classList.add('hidden');
                    badge.textContent = '';
                }
            };

            const fetchNotifications = async () => {
                try {
                    const response = await fetch('/scores/recent?limit=20', {
                        headers: { 'Accept': 'application/json' },
                    });

                    if (!response.ok) return;

                    const result = await response.json();
                    const items = result?.data || [];
                    latestTimestamp = items[0]?.created_at || latestTimestamp;

                    // Mostra apenas as 3 mais recentes no dropdown
                    const displayItems = items.slice(0, 3);
                    renderNotifications(displayItems);
                    // Mas usa todas as items para calcular o badge corretamente
                    updateBadge(items);
                } catch (error) {
                    console.error('Erro ao buscar notificações:', error);
                }
            };

            dropdown.addEventListener('toggle', () => {
                if (!dropdown.open) return;
                if (latestTimestamp) {
                    lastSeen = latestTimestamp;
                    localStorage.setItem(STORAGE_KEY, lastSeen);
                    badge.classList.add('hidden');
                    badge.textContent = '';
                }
            });

            fetchNotifications();
            setInterval(fetchNotifications, 15000);
        });
    </script>
    @auth
        @php
            $voiceEnabled = (App\Models\Config::where('key', 'notifications_voice_enabled')->value('value') ?? 'false') === 'true';
            $voiceMode = App\Models\Config::where('key', 'notifications_voice_mode')->value('value') ?? 'server';
            $browserVoiceName = App\Models\Config::where('key', 'notifications_voice_browser_name')->value('value') ?? '';
        @endphp
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const voiceEnabled = @json($voiceEnabled);
                const voiceMode = @json($voiceMode);
                const browserVoiceName = @json($browserVoiceName);

                if (!voiceEnabled || !['browser', 'both'].includes(voiceMode)) {
                    return;
                }

                if (!('speechSynthesis' in window)) {
                    return;
                }

                const STORAGE_KEY = 'ranking_voice_last_seen';
                let lastSeen = localStorage.getItem(STORAGE_KEY);

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
                        return;
                    }

                    speaking = true;
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

                const fetchVoiceEntries = async () => {
                    if (document.visibilityState !== 'visible') {
                        return;
                    }
                    try {
                        const params = new URLSearchParams({ limit: '10' });
                        if (lastSeen) {
                            params.append('since', lastSeen);
                        }
                        const response = await fetch(`/notifications/voice/recent?${params.toString()}`, {
                            headers: { 'Accept': 'application/json' },
                        });

                        if (!response.ok) return;

                        const result = await response.json();
                        const items = result?.data || [];

                        if (!items.length) return;

                        const ordered = items.slice().reverse();
                        ordered.forEach((item) => enqueue(item?.content));

                        const newest = items[0]?.created_at;
                        if (newest) {
                            lastSeen = newest;
                            localStorage.setItem(STORAGE_KEY, lastSeen);
                        }
                    } catch (error) {
                        console.error('Erro ao buscar leitura por voz:', error);
                    }
                };

                fetchVoiceEntries();
                setInterval(fetchVoiceEntries, 15000);
            });
        </script>
    @endauth

    <script>
        // Atualizar token CSRF periodicamente para evitar erro 419
        (function() {
            let csrfUpdateInterval = null;
            
            // Função para atualizar o token CSRF
            function updateCsrfToken() {
                fetch('/csrf-token', {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(response => {
                    if (!response.ok) throw new Error('Failed to fetch CSRF token');
                    return response.json();
                }).then(data => {
                    if (data.token) {
                        // Atualizar meta tag
                        const metaTag = document.querySelector('meta[name="csrf-token"]');
                        if (metaTag) {
                            metaTag.setAttribute('content', data.token);
                        }
                        
                        // Atualizar todos os inputs hidden com csrf token
                        document.querySelectorAll('input[name="_token"]').forEach(input => {
                            input.value = data.token;
                        });
                    }
                }).catch(error => {
                    console.warn('Não foi possível atualizar o token CSRF:', error);
                });
            }

            // Atualizar token quando o DOM estiver pronto
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    updateCsrfToken();
                    csrfUpdateInterval = setInterval(updateCsrfToken, 90000);
                });
            } else {
                updateCsrfToken();
                csrfUpdateInterval = setInterval(updateCsrfToken, 90000);
            }

            // Atualizar token quando a página ganha foco
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    updateCsrfToken();
                }
            });

            // Interceptar envio de formulários para verificar e atualizar token
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (form.tagName === 'FORM') {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const formToken = form.querySelector('input[name="_token"]')?.value;
                    
                    if (token && formToken && token !== formToken) {
                        form.querySelector('input[name="_token"]').value = token;
                    } else if (!formToken && token) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = '_token';
                        hiddenInput.value = token;
                        form.appendChild(hiddenInput);
                    }
                }
            }, true);

            // Limpar intervalo quando a página for descarregada
            window.addEventListener('beforeunload', function() {
                if (csrfUpdateInterval) {
                    clearInterval(csrfUpdateInterval);
                }
            });
        })();
    </script>
</body>
</html>
