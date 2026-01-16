# 🚀 Guia de Início Rápido - Ranking NEF

## Pré-requisitos

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL (ou PostgreSQL/SQLite)
- Extensões PHP: pdo_mysql, pdo_sqlite, ou pdo_pgsql

## 📋 Passo a Passo

### 1. Instalar Dependências (se ainda não instalou)

```bash
# Dependências PHP
composer install

# Dependências Node.js
npm install
```

### 2. Configurar Banco de Dados

Edite o arquivo `.env` e configure:

```env
DB_CONNECTION=mysql
DB_HOST=192.168.200.8
DB_PORT=3306
DB_DATABASE=ranking_vendas_nef
DB_USERNAME=nef_user
DB_PASSWORD="Nefbi2025@"
```

### 3. Executar Migrations e Seeders

```bash
# Criar tabelas
php artisan migrate

# Popular dados iniciais (usuários, temporada, regras, etc.)
php artisan db:seed
```

### 4. Gerar Chave da Aplicação (se necessário)

```bash
php artisan key:generate
```

### 5. Iniciar o Projeto

#### Opção A: Tudo em um comando (Recomendado)

```bash
composer run dev
```

Este comando inicia automaticamente:
- ✅ Servidor Laravel (http://localhost:8000)
- ✅ Queue Worker (processa jobs)
- ✅ Vite Dev Server (hot reload do React)
- ✅ Logs em tempo real

#### Opção B: Separado (para debug)

**Terminal 1 - Servidor Laravel:**
```bash
php artisan serve
```

**Terminal 2 - Vite (React):**
```bash
npm run dev
```

**Terminal 3 - Queue Worker (opcional, para processar ocorrências):**
```bash
php artisan queue:work
```

### 6. Acessar a Aplicação

Abra no navegador: **http://localhost:8000**

## 👤 Usuários de Teste

Após executar `php artisan db:seed`, você terá:

| Email | Senha | Role |
|-------|-------|------|
| admin@nef.local | password | admin |
| supervisor@nef.local | password | supervisor |
| user@nef.local | password | user |

## 🔧 Comandos Úteis

```bash
# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Executar migrations novamente (apaga tudo e recria)
php artisan migrate:fresh --seed

# Processar ocorrências manualmente
php artisan process:api-occurrences

# Ver rotas disponíveis
php artisan route:list
```

## 🐛 Troubleshooting

### Erro: "could not find driver"
- Instale a extensão PDO do banco de dados no PHP
- Edite `php.ini` e descomente a extensão correspondente

### Erro: "Connection refused"
- Verifique se o servidor MySQL está rodando
- Confirme as credenciais no `.env`

### Erro: "Port 8000 already in use"
- Use outra porta: `php artisan serve --port=8001`

### React não carrega
- Certifique-se de que `npm run dev` está rodando
- Verifique o console do navegador para erros

## 📝 Próximos Passos

1. ✅ Fazer login com um dos usuários de teste
2. ✅ Verificar o ranking de vendedores
3. ✅ Testar CRUD de sellers, teams, seasons
4. ✅ Enviar ocorrência via webhook: `POST /api/webhook/occurrences`

## 🎯 Estrutura do Projeto

```
ranking-nef/
├── app/
│   ├── Http/Controllers/Api/    # Controllers da API
│   ├── Jobs/                     # Jobs (ProcessApiOccurrencesJob)
│   ├── Models/                   # Models Eloquent
│   ├── Policies/                 # Policies de permissão
│   └── Services/                 # Services (GamificationService)
├── database/
│   ├── migrations/               # Migrations
│   └── seeders/                  # Seeders
├── resources/
│   ├── js/                       # Frontend React
│   │   ├── core/                 # Core (api, store, services)
│   │   ├── pages/                # Páginas React
│   │   └── components/           # Componentes React
│   └── views/                    # Views Blade
└── routes/
    ├── api.php                   # Rotas API
    └── web.php                   # Rotas Web (SPA)
```
