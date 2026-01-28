# Documentacao do Ranking NEF

Projeto de ranking de vendas com gamificacao. Backend em Laravel e
frontend em Blade (renderizacao no servidor). O frontend **nao** usa React.

## Visao geral
- Backend: Laravel (rotas, controllers, policies, jobs, services).
- Frontend: Blade (`resources/views` e `app/View/Components`).
- Banco de dados: migrations e seeders em `database/`.
- Assets: Vite para JS/CSS.

## Stack e requisitos
- PHP 8.2+
- Composer
- Node.js 18+
- Banco: MySQL (ou PostgreSQL/SQLite)
- Laravel 12
- Vite + Tailwind CSS

## Como rodar localmente
1. Instale dependencias:
   - `composer install`
   - `npm install`
2. Crie `.env` a partir de `.env.example` e ajuste o banco:
   - `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`,
     `DB_USERNAME`, `DB_PASSWORD`
3. Gere a chave da aplicacao:
   - `php artisan key:generate`
4. Rode migrations e seeders:
   - `php artisan migrate --seed`
5. Inicie o projeto:
   - `composer run dev`

### Execucao separada (opcional)
- `php artisan serve`
- `npm run dev`
- `php artisan queue:listen --tries=1`

### Produção (cron + fila)
Em produção, para recursos assíncronos (ex.: processamento de ocorrências da API), você deve configurar:

- **Scheduler**: cron chamando `php artisan schedule:run` a cada minuto
- **Queue worker**: `php artisan queue:work ...` rodando 24/7 (systemd/supervisor)

Guia completo: veja `docs/CRON_SETUP.md`.

## Usuarios de teste (seed)
Senha padrao: `password`
- `admin@nef.local` (admin) - Acesso completo ao sistema
- `supervisor@nef.local` (supervisor) - Acesso restrito às equipes atribuídas
- `user@nef.local` (user) - Acesso apenas para visualização

## Funcionalidades
- Dashboard com ranking, top 3, estatisticas e filtro por equipe.
- CRUD de vendedores (colaboradores).
- CRUD de equipes (nome técnico + nome de exibição).
- **Gerenciamento de usuarios** (apenas admin):
  - Criar, editar, desativar e excluir usuarios
  - Atribuir perfis (admin, supervisor)
  - Para supervisores: associar uma ou mais equipes
  - Redefinicao de senha com validacao de forca
- **Controle de acesso baseado em equipes**:
  - Supervisores veem apenas dados das equipes pelas quais sao responsaveis
  - Dashboard, vendedores e equipes filtrados automaticamente
  - Policies garantem acesso apenas aos recursos permitidos
- Configuracoes administrativas:
  - regras de pontuacao
  - notificacoes
  - parametros gerais e de temporada
  - **Temas do Monitor** - Personalize o layout visual do monitor
- Notificacoes com historico e filtros por data.
- Webhook para entrada de ocorrencias.

## Rotas principais
### Web (com autenticacao)
- `/` dashboard
- `/dashboard/data` dados do dashboard (JSON para recarregar blocos)
- `/sellers` vendedores (CRUD)
- `/teams` equipes (CRUD)
- `/users` gerenciamento de usuarios (apenas admin)
  - `/users/create` criar usuario
  - `/users/{user}/edit` editar usuario
  - `/users/{user}/reset-password` redefinir senha
  - `/users/{user}/toggle-status` ativar/desativar usuario
- `/settings` configuracoes (apenas admin)
- `/notifications` historico de notificacoes
- rotas de auth em `routes/auth.php` (login, register, etc.)

### API
- `POST /api/webhook/occurrences` recebe ocorrencias externas (**requer Bearer Token**)
- `GET /scores/recent` vendas recentes para notificacoes (JSON)

**Documentacao completa da API:** Veja [API.md](./API.md) para instrucoes detalhadas sobre como integrar sistemas de terceiros.

## Webhook de ocorrencias
Endpoint: `POST /api/webhook/occurrences`

Payload:
```
{
  "email_funcionario": "vendedor@empresa.com",
  "ocorrencia": "venda",
  "credor": "Cliente X",
  "equipe": "equipe_alpha"
}
```

> Nota: o campo `equipe` (quando enviado) deve ser o **nome técnico** da equipe (campo `teams.name`), pois é o identificador usado pelas integrações/API. O **Nome de Exibição** é apenas visual e não é usado pela API.

Resposta:
```
{
  "message": "Ocorrencia recebida com sucesso",
  "id": "<uuid>"
}
```

## Modelo de dados (resumo)
- `users`: nome, email, senha, role (admin/supervisor/user), is_active
- `team_user`: tabela pivot para relacionar supervisores com equipes
- `teams`: equipes
  - `name`: nome técnico (obrigatório; único dentro do setor)
  - `display_name`: nome de exibição (opcional; visual; não precisa ser único)
- `seasons`: temporadas (ativa ou nao)
- `sellers`: vendedores (team_id, season_id, pontos, status)
- `score_rules`: regras de pontuacao por ocorrencia
- `scores`: historico de pontos por vendedor
- `api_occurrences`: buffer de ocorrencias recebidas via webhook
- `configs`: configuracoes chave/valor

## Perfis de usuario e permissoes

### Admin
- Acesso completo ao sistema
- Pode gerenciar usuarios (criar, editar, desativar, excluir)
- Pode ver e gerenciar todas as equipes e vendedores
- Acesso as configuracoes do sistema

### Supervisor
- Acesso restrito às equipes atribuídas
- Pode ver e editar apenas vendedores das suas equipes
- Pode ver apenas as equipes pelas quais é responsável
- Dashboard mostra apenas dados das suas equipes
- Não tem acesso às configurações do sistema
- Não pode criar ou excluir vendedores/equipes

### User
- Acesso apenas para visualização
- Pode ver todos os vendedores e equipes (sem filtros)
- Não pode criar, editar ou excluir recursos

## Fluxo de pontuacao
1. Ocorrencia chega via webhook e vira `api_occurrences`.
2. Job processa ocorrencias pendentes em lotes.
3. Busca a `score_rule` por tipo de ocorrencia.
4. Cria `scores` e atualiza `sellers.points`.

## Gamificacao
O `GamificationService` converte pontos em nivel, badge e progresso
para o dashboard.

## Temas do Monitor
O sistema permite criar e selecionar temas visuais personalizados para o Monitor.

- Temas disponiveis em `resources/views/monitors/themes/`
- Cada tema deve conter `layout.blade.php` e `dashboard.blade.php`
- Configuracao em: Configuracoes > Temas (apenas admin)
- Tema padrao: `default`

**Documentacao completa:** Veja [CRIAR_TEMAS.md](./CRIAR_TEMAS.md) para instrucoes detalhadas sobre como criar novos temas.

## Configuracoes do sistema
Chaves em `configs` (seed inicial):
- `auto_process_occurrences`: processar automaticamente ocorrencias
- `points_precision`: casas decimais exibidas
- `ranking_limit`: limite do ranking
- `sale_term`: nome do termo principal (ex: "Venda")
- `season_required`: exige temporada
- `season_duration_days`: duracao da temporada
- `season_auto_renew`: renovacao automatica
- `notifications_system_enabled`: canal de notificacao no painel
- `notifications_email_enabled`: canal de notificacao por email
- `notifications_sound_enabled`: canal de notificacao por som
- `notifications_events_config`: JSON com eventos e canais habilitados
- `notifications_voice_enabled`: ativa leitura por voz do ranking
- `notifications_voice_mode`: `server` | `browser` | `both`
- `notifications_voice_scope`: `global` | `teams` | `both`
- `notifications_voice_interval_minutes`: intervalo minimo entre leituras
- `notifications_voice_only_when_changed`: somente quando ranking mudar
- `notifications_voice_name`: nome da voz do TTS (opcional)
- `notifications_voice_browser_name`: nome da voz do navegador (opcional)
- `monitor_theme`: tema visual do monitor (padrao: `default`)

## Jobs e filas
- `ProcessApiOccurrencesJob`: processa ocorrencias em lotes.
- `SpeakRankingJob`: leitura por voz do ranking (TTS).
- Rodar fila (desenvolvimento): `php artisan queue:listen --tries=1`
- Rodar fila (produção): `php artisan queue:work --tries=3 --timeout=90` (recomendado via systemd/supervisor)

## Historico de leitura por voz
O historico das leituras fica em `notification_histories` com:
- `type`: `voice_ranking`
- `scope`: `global` ou `team`
- `content`: texto lido

## Comandos artisan uteis
- `process:api-occurrences` processa ocorrencias pendentes
- `seed:teams --quantity=6` cria equipes
- `seed:sellers --quantity=30` cria vendedores
- `simulate:sale` simula pontuacao para um vendedor
- `php artisan route:list` lista rotas
- `php artisan migrate:fresh --seed` recria base do zero
- `php artisan cache:clear`, `config:clear`, `route:clear`, `view:clear`

## Gerenciamento de usuarios

### Criar usuario
1. Acesse `/users/create` (apenas admin)
2. Preencha nome, email, perfil e senha
3. Se for supervisor, selecione uma ou mais equipes
4. Senha deve ter: min 8 caracteres, maiúscula, minúscula, número e caractere especial

### Editar usuario
1. Acesse `/users/{user}/edit`
2. Altere nome, email ou perfil
3. Se mudar para supervisor, selecione as equipes
4. Se mudar de supervisor para admin, as equipes são removidas automaticamente

### Redefinir senha
1. Acesse `/users/{user}/reset-password`
2. Defina nova senha com confirmação
3. Validação de força aplicada

### Ativar/Desativar usuario
- Use o botão de toggle na listagem de usuarios
- Usuario desativado não pode fazer login
- Não é possível desativar o próprio usuario

## Performance
Indices relevantes estao descritos em `PERFORMANCE.md`.
Para producao:
- `php artisan config:cache`
- `php artisan route:cache`
- `php artisan view:cache`

## Estrutura de pastas
```
app/
  Console/Commands/
  Http/
    Controllers/
      Api/        # Controllers de API
    Requests/     # Form Requests (validação)
  Jobs/
  Models/
  Policies/       # Policies de autorização
  Services/
resources/
  views/          # Blade templates (não usa React)
  css/
  js/             # Apenas Alpine.js e axios (sem React)
routes/
  web.php
  api.php
  auth.php
database/
  migrations/
  seeders/
docs/             # Documentação
  README.md       # Documentação geral
  API.md          # Documentação da API para integração com terceiros
  CRIAR_TEMAS.md  # Guia para criar novos temas do monitor
```

## Testes
- `php artisan test`

## Troubleshooting
- "could not find driver": habilite o driver PDO do banco.
- "Connection refused": verifique o servidor e credenciais do banco.
- "Port already in use": `php artisan serve --port=8001`.
- "Fila nao processa" (ocorrências ficam com `processed = 0`):
  - verifique se existe **worker** rodando (`queue:work`) e se o **cron do scheduler** está configurado
  - veja `docs/CRON_SETUP.md`
- Assets nao atualizam: rode `npm run dev`.
