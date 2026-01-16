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
</head>
<body class="font-sans antialiased bg-[#0a0e1a] min-h-screen">
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
                    const sellerName = sale?.seller?.name || 'Vendedor';
                    const occurrenceLabel = sale?.occurrence?.description || sale?.occurrence?.type || `${saleTerm} registrada`;
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
                    badge.textContent = unread > 9 ? '9+' : String(unread);
                } else {
                    badge.classList.add('hidden');
                    badge.textContent = '';
                }
            };

            const fetchNotifications = async () => {
                try {
                    const response = await fetch('/scores/recent?limit=3', {
                        headers: { 'Accept': 'application/json' },
                    });

                    if (!response.ok) return;

                    const result = await response.json();
                    const items = result?.data || [];
                    latestTimestamp = items[0]?.created_at || latestTimestamp;

                    renderNotifications(items);
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

                if (!lastSeen) {
                    lastSeen = new Date().toISOString();
                    localStorage.setItem(STORAGE_KEY, lastSeen);
                }

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
                    try {
                        const params = new URLSearchParams({ limit: '10', since: lastSeen });
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
</body>
</html>
