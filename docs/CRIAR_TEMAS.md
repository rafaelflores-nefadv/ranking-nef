# Como Criar Novos Temas para o Monitor

Este guia explica como criar novos temas visuais para o Monitor do Ranking NEF. Os temas permitem personalizar apenas o layout visual e estilos, mantendo toda a lógica JavaScript intacta.

## 📋 Estrutura de um Tema

Cada tema deve ser criado em `resources/views/monitors/themes/{nome_do_tema}/` e deve conter:

```
resources/views/monitors/themes/
└── {nome_do_tema}/
    ├── layout.blade.php    (Obrigatório)
    └── dashboard.blade.php (Obrigatório)
```

### Arquivos Obrigatórios

#### 1. `layout.blade.php`
O layout base do tema. Contém a estrutura HTML principal, head, body e scripts essenciais.

**Exemplo básico:**
```blade
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
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        
        body {
            font-size: calc(1rem * {{ $dashboardConfig['font_scale'] ?? 1.0 }});
        }
    </style>
    @stack('styles')
</head>
<body class="font-sans antialiased">
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
        // Configuração global do dashboard - OBRIGATÓRIO
        window.DASHBOARD_CONFIG = @json($dashboardConfig ?? []);
        
        // Sistema de alertas personalizados
        function showCustomAlert(title, message, type = 'info') {
            // ... implementação dos alertas
        }
        
        function closeCustomAlert() {
            // ... implementação para fechar alertas
        }
        
        window.originalAlert = window.alert;
        window.alert = function(message) {
            showCustomAlert('Aviso', message, 'info');
        };
    </script>
    
    @yield('content')
    
    @stack('scripts')
    
    {{-- Leitura por voz do ranking --}}
    {{-- Inclua o script de leitura por voz se necessário --}}
</body>
</html>
```

**⚠️ Importante no `layout.blade.php`:**
- **SEMPRE** inclua `window.DASHBOARD_CONFIG = @json($dashboardConfig ?? []);` - É essencial para o JavaScript funcionar
- O modal de alertas personalizado (`custom-alert-modal`) é recomendado para uma melhor experiência
- Use `@yield('content')` para inserir o conteúdo do dashboard
- Use `@stack('scripts')` e `@stack('styles')` para permitir que o dashboard adicione scripts e estilos

#### 2. `dashboard.blade.php`
O conteúdo principal do dashboard. Contém todo o HTML visual do monitor.

**Exemplo básico:**
```blade
@extends('monitors.themes.{nome_do_tema}.layout')

@php
    $saleTerm = $configs['sale_term'] ?? 'Venda';
    $saleTermLower = strtolower($saleTerm);
@endphp

@section('content')
<div class="w-full h-full">
    <!-- Seu HTML personalizado aqui -->
    <!-- Background, notificações, header, grid principal, etc. -->
    
    <!-- Exemplo: Grid principal -->
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
        <div class="col-span-3">
            <!-- Seus controles personalizados -->
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Seu JavaScript personalizado aqui
    // IMPORTANTE: Mantenha a mesma lógica do tema default
    // Apenas personalize aspectos visuais se necessário
    
    // Configuração do monitor vindo de window.DASHBOARD_CONFIG
    const config = window.DASHBOARD_CONFIG || {};
    const monitorSlug = config.monitor_slug || @json($monitor->slug ?? '') || window.location.pathname.match(/\/monitor\/([^\/]+)/)?.[1] || '';
    
    // ... resto da lógica JavaScript (copie do tema default e personalize conforme necessário)
</script>
@endpush

@push('styles')
<style>
    /* Seus estilos personalizados aqui */
</style>
@endpush
@endsection
```

**⚠️ Importante no `dashboard.blade.php`:**
- Use `@extends('monitors.themes.{nome_do_tema}.layout')` - ajuste para o nome do seu tema
- **MANTENHA** os IDs essenciais para o JavaScript funcionar:
  - `#ranking-sidebar` - Container do ranking
  - `#podium-area` - Container do pódio
  - `#sale-notifications` - Container de notificações de vendas
  - `#team-chips` - Chips de seleção de equipes
  - `#toggle-refresh` - Botão de play/pause
  - `#refresh-countdown` - Contador de atualização
  - `#toggle-sound-btn` - Botão de som
  - `#read-voice-btn` - Botão de leitura por voz
  - Outros IDs usados pelo JavaScript do tema default

- **MANTENHA** os `data-attributes` necessários:
  - `data-team-id` nos chips de equipes
  - `data-refresh-interval` nos botões de intervalo
  - `data-refresh-label` nos botões de intervalo

- Inclua os `@include` necessários:
  - `@include('dashboard.partials.ranking', ...)`
  - `@include('dashboard.partials.podium', ...)`

## 🎨 Personalização Visual

Você pode personalizar livremente:

- ✅ Cores e esquemas de cores (usando Tailwind CSS ou CSS customizado)
- ✅ Layout e posicionamento de elementos
- ✅ Estilos de cards, botões, bordas, sombras
- ✅ Animações e transições
- ✅ Backgrounds e efeitos visuais
- ✅ Tipografia e tamanhos de fonte
- ✅ Espaçamentos e tamanhos de componentes

**⚠️ NÃO altere:**
- ❌ Lógica JavaScript principal (a menos que saiba o que está fazendo)
- ❌ IDs dos elementos essenciais
- ❌ Estrutura de dados esperada pelo JavaScript
- ❌ Variáveis globais como `window.DASHBOARD_CONFIG`

## 📝 Passo a Passo para Criar um Novo Tema

### 1. Criar a Estrutura de Diretórios
```bash
mkdir -p resources/views/monitors/themes/meu_tema
```

### 2. Copiar o Tema Default como Base
```bash
# Copiar layout
cp resources/views/monitors/themes/default/layout.blade.php \
   resources/views/monitors/themes/meu_tema/layout.blade.php

# Copiar dashboard
cp resources/views/monitors/themes/default/dashboard.blade.php \
   resources/views/monitors/themes/meu_tema/dashboard.blade.php
```

### 3. Atualizar o @extends no dashboard.blade.php
No arquivo `dashboard.blade.php` do novo tema, altere:

```blade
@extends('monitors.themes.default.layout')
```

Para:

```blade
@extends('monitors.themes.meu_tema.layout')
```

### 4. Personalizar Visualmente
Agora você pode personalizar:
- Cores, estilos CSS
- Layout e posicionamento
- Classes Tailwind CSS
- Adicionar novos elementos visuais

### 5. Testar o Tema
1. Acesse Configurações > Temas
2. Selecione seu novo tema
3. Salve
4. Acesse um monitor público para ver o resultado

### 6. Validar Funcionalidades
Certifique-se de que funcionam:
- ✅ Atualização automática do ranking
- ✅ Rotação de equipes (se configurada)
- ✅ Notificações de vendas
- ✅ Botão de som
- ✅ Botão de leitura por voz
- ✅ Seleção de equipes
- ✅ Controles de intervalo de atualização

## 🎯 Exemplo: Tema Minimalista

Vamos criar um tema minimalista como exemplo:

### 1. Criar estrutura
```bash
mkdir -p resources/views/monitors/themes/minimal
```

### 2. Criar layout.blade.php
Copie o layout do default e personalize o body:

```blade
<body class="font-sans antialiased bg-white">
    <!-- Mesmo conteúdo do default -->
</body>
```

### 3. Criar dashboard.blade.php
Copie o dashboard do default e personalize:

```blade
@extends('monitors.themes.minimal.layout')

@section('content')
<div class="w-full h-full bg-white">
    <!-- Header minimalista -->
    <div class="bg-gray-100 border-b border-gray-200 px-6 py-4">
        <h1 class="text-2xl font-bold text-gray-900">
            Ranking de {{ $saleTermLower }}
        </h1>
    </div>

    <!-- Grid principal com cores neutras -->
    <div class="grid grid-cols-12 gap-4 p-4">
        <!-- Sidebar -->
        <div class="col-span-3">
            <div id="ranking-sidebar" class="bg-gray-50 rounded-lg p-4">
                @include('dashboard.partials.ranking', ['ranking' => $ranking, 'activeTeam' => $activeTeam])
            </div>
        </div>

        <!-- Pódio -->
        <div class="col-span-6" id="podium-area">
            @include('dashboard.partials.podium', ['top3' => $top3])
        </div>

        <!-- Controles -->
        <div class="col-span-3">
            <!-- Mantenha os mesmos controles do default -->
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Mesmo JavaScript do default
    // Copie todo o script do dashboard.blade.php do tema default
</script>
@endpush
@endsection
```

## 🔍 Variáveis Disponíveis

No `dashboard.blade.php`, você tem acesso a:

- `$monitor` - Objeto do monitor
- `$configs` - Array de configurações do sistema
- `$dashboardConfig` - Configurações específicas do monitor
- `$ranking` - Array do ranking
- `$top3` - Top 3 do ranking
- `$stats` - Estatísticas (totalPoints, totalParticipants, etc.)
- `$percentage` - Porcentagem calculada
- `$teams` - Collection de equipes
- `$activeTeam` - Equipe ativa selecionada
- `$notificationEventsConfig` - Configuração de eventos de notificação

## 🐛 Troubleshooting

### Tema não aparece na lista
- Verifique se ambos os arquivos (`layout.blade.php` e `dashboard.blade.php`) existem
- Verifique se o nome do diretório não contém caracteres especiais
- Limpe o cache: `php artisan view:clear`

### JavaScript não funciona
- Verifique se `window.DASHBOARD_CONFIG` está definido no layout
- Confirme que os IDs essenciais estão presentes no HTML
- Verifique o console do navegador para erros

### Estilos não aplicam
- Verifique se o `@stack('styles')` está no layout
- Confirme que o Tailwind CSS está carregado (`@vite(['resources/css/app.css'])`)
- Limpe o cache: `php artisan view:clear` e `npm run build`

### Elementos não atualizam
- Verifique se os IDs estão corretos (ex: `#ranking-sidebar`, `#podium-area`)
- Confirme que o JavaScript está carregando corretamente
- Verifique se o `@push('scripts')` está sendo usado corretamente

## 📚 Referências

- Tema default: `resources/views/monitors/themes/default/`
- Partial de ranking: `resources/views/dashboard/partials/ranking.blade.php`
- Partial de pódio: `resources/views/dashboard/partials/podium.blade.php`
- Tailwind CSS: https://tailwindcss.com/docs
- Laravel Blade: https://laravel.com/docs/blade

## ✅ Checklist Final

Antes de considerar seu tema pronto:

- [ ] Ambos os arquivos obrigatórios existem (`layout.blade.php` e `dashboard.blade.php`)
- [ ] `@extends` no dashboard aponta para o layout correto do tema
- [ ] `window.DASHBOARD_CONFIG` está definido no layout
- [ ] Todos os IDs essenciais estão presentes no HTML
- [ ] JavaScript foi copiado e mantido do tema default
- [ ] Tema aparece na lista de Configurações > Temas
- [ ] Tema pode ser selecionado e salvo
- [ ] Monitor carrega corretamente com o novo tema
- [ ] Todas as funcionalidades JavaScript funcionam
- [ ] Visual está de acordo com o esperado

---

**Nota:** Sempre teste seu tema completamente antes de usá-lo em produção. O tema default serve como referência de implementação completa e funcional.
