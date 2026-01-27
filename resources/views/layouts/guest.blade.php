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
    <body class="font-sans antialiased bg-[#0a0e1a]">
        {{ $slot }}

        @stack('scripts')
        <script>
            // Atualizar token CSRF periodicamente para evitar erro 419
            document.addEventListener('DOMContentLoaded', function() {
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

                // Atualizar token imediatamente ao carregar
                updateCsrfToken();

                // Atualizar token a cada 90 segundos (antes de expirar - sessão padrão é 120 min)
                csrfUpdateInterval = setInterval(updateCsrfToken, 90000); // 90 segundos

                // Atualizar token quando a página ganha foco (usuário volta para a aba)
                document.addEventListener('visibilitychange', function() {
                    if (!document.hidden) {
                        updateCsrfToken();
                    }
                });

                // Interceptar envio de formulários para verificar e atualizar token
                document.querySelectorAll('form').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const formToken = form.querySelector('input[name="_token"]')?.value;
                        
                        // Se os tokens não coincidem, atualizar antes de enviar
                        if (token && formToken && token !== formToken) {
                            form.querySelector('input[name="_token"]').value = token;
                        } else if (!formToken && token) {
                            // Se não há token no form mas há na meta tag, adicionar
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = '_token';
                            hiddenInput.value = token;
                            form.appendChild(hiddenInput);
                        }
                    }, { capture: true });
                });

                // Limpar intervalo quando a página for descarregada
                window.addEventListener('beforeunload', function() {
                    if (csrfUpdateInterval) {
                        clearInterval(csrfUpdateInterval);
                    }
                });
            });
        </script>
    </body>
</html>
