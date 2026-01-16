# Script de Migração do Frontend React para Laravel
# 
# Formas de uso:
# 1. Com parâmetro: .\migrar-para-laravel.ps1 -LaravelPath "C:\caminho\para\projeto-laravel"
# 2. Sem parâmetro: .\migrar-para-laravel.ps1 (o script pedirá o caminho)
# 3. Arrastar pasta: .\migrar-para-laravel.ps1 "C:\caminho\para\projeto-laravel"

param(
    [Parameter(Mandatory=$false)]
    [string]$LaravelPath
)

# Se o caminho não foi fornecido, pedir interativamente
if (-not $LaravelPath) {
    Write-Host ""
    Write-Host "📁 Informe o caminho do projeto Laravel:" -ForegroundColor Cyan
    Write-Host "   (Exemplo: C:\Projetos\meu-projeto-laravel)" -ForegroundColor Gray
    Write-Host ""
    $LaravelPath = Read-Host "Caminho"
    
    # Remover aspas se o usuário colou com aspas
    $LaravelPath = $LaravelPath.Trim('"').Trim("'")
    
    if ([string]::IsNullOrWhiteSpace($LaravelPath)) {
        Write-Host "❌ Erro: Caminho não informado!" -ForegroundColor Red
        exit 1
    }
}

Write-Host ""
Write-Host "🚀 Iniciando migração do frontend para Laravel..." -ForegroundColor Cyan
Write-Host "📂 Caminho do Laravel: $LaravelPath" -ForegroundColor Gray
Write-Host ""

# Verificar se o caminho do Laravel existe
if (-not (Test-Path $LaravelPath)) {
    Write-Host "❌ Erro: Caminho do Laravel não encontrado: $LaravelPath" -ForegroundColor Red
    Write-Host "   Verifique se o caminho está correto e tente novamente." -ForegroundColor Yellow
    exit 1
}

# Verificar se é realmente um projeto Laravel (procurar por artisan)
$artisanPath = Join-Path $LaravelPath "artisan"
if (-not (Test-Path $artisanPath)) {
    Write-Host "⚠️  Aviso: Arquivo 'artisan' não encontrado no caminho especificado." -ForegroundColor Yellow
    Write-Host "   Certifique-se de que está apontando para a raiz do projeto Laravel." -ForegroundColor Yellow
    $continue = Read-Host "   Deseja continuar mesmo assim? (S/N)"
    if ($continue -ne "S" -and $continue -ne "s") {
        Write-Host "❌ Operação cancelada." -ForegroundColor Red
        exit 1
    }
}

# Verificar se existe a pasta resources/js
$resourcesJsPath = Join-Path $LaravelPath "resources\js"
if (-not (Test-Path $resourcesJsPath)) {
    Write-Host "📁 Criando pasta resources/js..." -ForegroundColor Yellow
    New-Item -ItemType Directory -Path $resourcesJsPath -Force | Out-Null
}

# Caminho atual do projeto
$currentPath = $PSScriptRoot
$srcPath = Join-Path $currentPath "src"

# Verificar se a pasta src existe
if (-not (Test-Path $srcPath)) {
    Write-Host "❌ Erro: Pasta 'src' não encontrada no projeto atual." -ForegroundColor Red
    Write-Host "   Certifique-se de executar o script na raiz do projeto React." -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "📋 Resumo da migração:" -ForegroundColor Cyan
Write-Host "   Origem: $currentPath" -ForegroundColor Gray
Write-Host "   Destino: $LaravelPath" -ForegroundColor Gray
Write-Host ""
$confirm = Read-Host "Deseja continuar? (S/N)"
if ($confirm -ne "S" -and $confirm -ne "s") {
    Write-Host "❌ Operação cancelada." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "📂 Copiando arquivos..." -ForegroundColor Cyan

# Copiar componentes
$componentsDest = Join-Path $resourcesJsPath "components"
Write-Host "  - Copiando components..." -ForegroundColor Gray
Copy-Item -Path (Join-Path $srcPath "components") -Destination $componentsDest -Recurse -Force

# Copiar outras pastas
$folders = @("controllers", "core", "hooks", "lib", "models", "pages", "routes", "utils")
foreach ($folder in $folders) {
    $source = Join-Path $srcPath $folder
    $dest = Join-Path $resourcesJsPath $folder
    if (Test-Path $source) {
        Write-Host "  - Copiando $folder..." -ForegroundColor Gray
        Copy-Item -Path $source -Destination $dest -Recurse -Force
    }
}

# Copiar CSS
$resourcesCssPath = Join-Path $LaravelPath "resources\css"
if (-not (Test-Path $resourcesCssPath)) {
    New-Item -ItemType Directory -Path $resourcesCssPath -Force | Out-Null
}
$cssSource = Join-Path $currentPath "src\index.css"
$cssDest = Join-Path $resourcesCssPath "app.css"
if (Test-Path $cssSource) {
    Write-Host "  - Copiando CSS..." -ForegroundColor Gray
    Copy-Item -Path $cssSource -Destination $cssDest -Force
}

# Criar app.jsx se não existir
$appJsxPath = Join-Path $resourcesJsPath "app.jsx"
if (-not (Test-Path $appJsxPath)) {
    Write-Host "  - Criando app.jsx..." -ForegroundColor Gray
    $appJsxContent = @"
import React from 'react'
import ReactDOM from 'react-dom/client'
import App from '@/App.jsx'
import '@/../css/app.css'

ReactDOM.createRoot(document.getElementById('root')).render(
    <App />
)
"@
    Set-Content -Path $appJsxPath -Value $appJsxContent
}

# Copiar arquivos de configuração
Write-Host "  - Copiando arquivos de configuração..." -ForegroundColor Gray

# Tailwind config
$tailwindSource = Join-Path $currentPath "tailwind.config.js"
$tailwindDest = Join-Path $LaravelPath "tailwind.config.js"
if (Test-Path $tailwindSource) {
    Copy-Item -Path $tailwindSource -Destination $tailwindDest -Force
}

# PostCSS config
$postcssSource = Join-Path $currentPath "postcss.config.js"
$postcssDest = Join-Path $LaravelPath "postcss.config.js"
if (Test-Path $postcssSource) {
    Copy-Item -Path $postcssSource -Destination $postcssDest -Force
}

# jsconfig.json
$jsconfigSource = Join-Path $currentPath "jsconfig.json"
$jsconfigDest = Join-Path $resourcesJsPath "jsconfig.json"
if (Test-Path $jsconfigSource) {
    Copy-Item -Path $jsconfigSource -Destination $jsconfigDest -Force
}

Write-Host "✅ Migração concluída!" -ForegroundColor Green
Write-Host ""
Write-Host "Próximos passos:" -ForegroundColor Yellow
Write-Host "1. No projeto Laravel, instale as dependências: npm install" -ForegroundColor White
Write-Host "2. Configure o vite.config.js conforme o guia" -ForegroundColor White
Write-Host "3. Crie resources/views/app.blade.php" -ForegroundColor White
Write-Host "4. Configure as rotas em routes/web.php" -ForegroundColor White
Write-Host "5. Execute: npm run dev" -ForegroundColor White
