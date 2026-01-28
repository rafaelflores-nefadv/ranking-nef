# Exemplo Rápido de Teste da API

> Importante: não utilize tokens reais em exemplos versionados. Use placeholders.

## Pré-requisitos

- Token de API ativo (gerado no painel em **Configurações > Integrações API**)
- Vendedor existente no setor do token (por email ou external_code, conforme o token)
- Regra de pontuação ativa no setor para o valor enviado em `ocorrencia`

## Teste Rápido com cURL

### 1. Teste Básico - Ocorrência Simples

```bash
curl -X POST http://localhost:8000/api/webhook/occurrences \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -d '{
    "email_funcionario": "IDENTIFICADOR_DO_VENDEDOR",
    "ocorrencia": "30.1 - C/ PROPOSTA"
  }'
```

**Resultado esperado**: 201 Created com ID da ocorrência
**Pontos esperados**: 2 pontos (conforme regra cadastrada)

### 2. Teste com Credor

```bash
curl -X POST http://localhost:8000/api/webhook/occurrences \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -d '{
    "email_funcionario": "IDENTIFICADOR_DO_VENDEDOR",
    "ocorrencia": "30.8 -  BOLETO PAGO",
    "credor": "Cliente Teste LTDA"
  }'
```

**Resultado esperado**: 201 Created
**Pontos esperados**: 4 pontos (maior pontuação disponível)

### 3. Teste com Credor e Equipe

```bash
curl -X POST http://localhost:8000/api/webhook/occurrences \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -d '{
    "email_funcionario": "IDENTIFICADOR_DO_VENDEDOR",
    "ocorrencia": "30.6 - BOLETO ENVIADO",
    "credor": "Cliente Teste LTDA",
    "equipe": "equipe_teste"
  }'
```

**Resultado esperado**: 201 Created
**Pontos esperados**: 3 pontos

> Nota: o campo `equipe` deve receber o **nome técnico** da equipe (`teams.name`). O “Nome de Exibição” (visual) não é usado pela API.

## Verificar Resultados

### Processar Ocorrências (se a fila não estiver rodando)

```bash
php artisan process:api-occurrences
```

### Verificar Pontos do Vendedor

```bash
php artisan tinker
```

```php
$seller = \App\Models\Seller::where('email', 'vendedor@empresa.com')->first();
echo "Nome: " . $seller->name . "\n";
echo "Email: " . $seller->email . "\n";
echo "Pontos: " . $seller->points . "\n";
echo "Status: " . $seller->status . "\n";
```

## Checklist de Teste

- [ ] Token está correto e ativo
- [ ] URL base está correta (localhost:8000 ou seu domínio)
- [ ] Identificador do vendedor está correto (email ou external_code, conforme token)
- [ ] Tipo de ocorrência corresponde a uma regra cadastrada
- [ ] Fila está rodando OU processar manualmente com `php artisan process:api-occurrences`
- [ ] Verificar pontos no dashboard ou via tinker

## Tipos de Ocorrência para Testar

| Ocorrência | Pontos | Comando |
|------------|--------|---------|
| 30.8 -  BOLETO PAGO | 4 | `"ocorrencia": "30.8 -  BOLETO PAGO"` |
| 30.6 - BOLETO ENVIADO | 3 | `"ocorrencia": "30.6 - BOLETO ENVIADO"` |
| 30.1 - C/ PROPOSTA | 2 | `"ocorrencia": "30.1 - C/ PROPOSTA"` |
| 30.2 - C/PROMESSA | 2 | `"ocorrencia": "30.2 - C/PROMESSA"` |
| 20.1 - SEM PROPOSTA/SEM PROMESSA | 1 | `"ocorrencia": "20.1 - SEM PROPOSTA/SEM PROMESSA"` |
