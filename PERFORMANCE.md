# Otimizações de Performance

Este documento descreve as otimizações de performance implementadas no sistema.

## Índices de Banco de Dados

Foram adicionados índices estratégicos para melhorar a performance de consultas frequentes:

### Sellers
- `points` - Para ordenação do ranking
- `team_id` - Para filtros por equipe
- `season_id` - Para filtros por temporada
- `status` - Para filtros de status

### API Occurrences
- `processed` - Para busca de ocorrências pendentes
- `ocorrencia` - Para busca por tipo de ocorrência
- `email_funcionario` - Para busca por funcionário
- `(processed, created_at)` - Índice composto para queries do Job

### Scores
- `seller_id` - Para histórico de pontuações
- `score_rule_id` - Para relatórios
- `created_at` - Para ordenação temporal

### Score Rules
- `ocorrencia` - Para busca rápida
- `is_active` - Para filtros
- `(ocorrencia, is_active)` - Índice composto para queries do Job

### Seasons
- `is_active` - Para busca de temporada ativa

## Chaves Estrangeiras

Todas as foreign keys já estão implementadas com as seguintes regras:

- `sellers.team_id` → `teams.id` (onDelete: set null)
- `sellers.season_id` → `seasons.id` (onDelete: set null)
- `scores.seller_id` → `sellers.id` (onDelete: cascade)
- `scores.score_rule_id` → `score_rules.id` (onDelete: cascade)

## Eager Loading

Os controllers utilizam eager loading para evitar N+1 queries:

- `RankingController`: `Seller::with(['team', 'season'])`
- `SellerController`: `Seller::with(['team', 'season'])` no index e show

## Otimização do Job

O `ProcessApiOccurrencesJob` foi otimizado para:

1. **Processar em lotes**: Processa 100 ocorrências por vez para evitar sobrecarga de memória
2. **Cache de Score Rules**: Armazena score rules em cache durante a execução para evitar queries repetidas
3. **Ordenação**: Usa `orderBy('created_at')` para processar na ordem de chegada

## Cache de Produção

Para melhorar ainda mais a performance em produção, execute os seguintes comandos:

```bash
# Cache de configuração
php artisan config:cache

# Cache de rotas
php artisan route:cache

# Cache de views
php artisan view:cache
```

⚠️ **Importante**: Execute estes comandos apenas em produção. Em desenvolvimento, use:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Executar Migrations

Para aplicar as otimizações de índices:

```bash
php artisan migrate
```
