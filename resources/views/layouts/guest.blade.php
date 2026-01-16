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
    </body>
</html>
