# Guia de Teste da API - Ranking NEF

Este guia explica como testar o endpoint de webhook da API do Ranking NEF.

> Importante: este repositório **não deve** conter tokens reais. Os exemplos abaixo usam placeholders.

## Pré-requisitos

- Um **token de API ativo** (gerado no painel: **Configurações > Integrações API**)
- Um **setor** associado ao token (o token sempre opera dentro de um setor)
- Um **vendedor existente** no mesmo setor do token
- Uma **regra de pontuação ativa** no setor para o valor enviado em `ocorrencia`
- (Opcional) Se enviar `equipe`, envie o **nome técnico** da equipe (`teams.name`). A equipe deve existir no setor **e** o vendedor deve pertencer a ela.

## Autenticação (Bearer Token)

Envie o token no header `Authorization`:

```http
Authorization: Bearer SEU_TOKEN_AQUI
```

## Identificador do vendedor (`email_funcionario`)

Apesar do nome do campo ser `email_funcionario`, o valor aceito depende da configuração do token:

- Se o token estiver configurado para identificar colaboradores por **email**, envie um email válido (ex.: `vendedor@empresa.com`)
- Se o token estiver configurado para identificar por **código externo**, envie o **external_code** do vendedor (string)

## Regras de Pontuação Cadastradas

O valor do campo `ocorrencia` deve corresponder **exatamente** a uma regra ativa (`score_rules`) do setor do token.

Exemplos (dependem do seu cadastro):

| Ocorrência | Pontos |
|------------|--------|
| 20.1 - SEM PROPOSTA/SEM PROMESSA | 1 |
| 20.2 - NÃO QUER PAGAR | 1 |
| 20.3 - DESCONHECE DÍVIDA | 1 |
| 30.1 - C/ PROPOSTA | 2 |
| 30.2 - C/PROMESSA | 2 |
| 30.3 - SOLICITOU RETORNO | 1 |
| 30.4 - AGÊNCIA | 1 |
| 30.5 - CPC WHATS | 1 |
| 30.6 - BOLETO ENVIADO | 3 |
| 30.7 - ALEGOU PAGAMENTO | 1 |
| 30.8 -  BOLETO PAGO | 4 |
| 10 - NÃO ATENDE | 1 |
| 10.2 - NÃO ATENDE - WHATS | 1 |
| 98 - QUEBRA DE ACORDO | 1 |

## Como Testar

### Opção 1: Usando o Script PHP

```bash
php test-api.php
```

O script irá:
1. Enviar uma ocorrência simples (30.1 - C/ PROPOSTA)
2. Enviar uma ocorrência com credor (30.8 - BOLETO PAGO)
3. Exibir os resultados e um resumo

**Antes de executar**, defina as variáveis de ambiente (ou edite o topo do script, se preferir):

- `RANKING_NEF_BASE_URL` (ex.: `http://localhost:8000`)
- `RANKING_NEF_API_TOKEN` (token do painel)
- `RANKING_NEF_TEST_IDENTIFIER` (email ou external_code, conforme o token)

### Opção 2: Usando o Script PowerShell (Windows)

```powershell
.\test-api.ps1
```

Funciona da mesma forma que o script PHP, mas otimizado para Windows.

### Opção 3: Usando cURL Manualmente

#### Exemplo 1: Ocorrência Simples

```bash
curl -X POST http://localhost:8000/api/webhook/occurrences \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -d '{
    "email_funcionario": "IDENTIFICADOR_DO_VENDEDOR",
    "ocorrencia": "30.1 - C/ PROPOSTA"
  }'
```

#### Exemplo 2: Ocorrência com Credor

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

#### Exemplo 3: Ocorrência Completa (com credor e equipe)

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

### Opção 4: Usando o Script Bash

```bash
chmod +x test-api-curl.sh
./test-api-curl.sh
```

## Resposta Esperada

### Sucesso (201 Created)

```json
{
  "message": "Ocorrência recebida com sucesso",
  "id": "550e8400-e29b-41d4-a716-446655440000"
}
```

### Erro de Autenticação (401 Unauthorized)

```json
{
  "message": "Token inválido ou inativo",
  "error": "Unauthorized"
}
```

### Erro de Validação (422 Unprocessable Entity)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email_funcionario": [
      "The email funcionario field is required."
    ],
    "ocorrencia": [
      "The ocorrencia field is required."
    ]
  }
}
```

## Processamento das Ocorrências

1. **Recebimento**: A ocorrência é recebida via webhook e armazenada na tabela `api_occurrences`
2. **Processamento Assíncrono**: Um job em background (`ProcessApiOccurrencesJob`) processa a ocorrência
3. **Validação**: o sistema valida se existe vendedor no setor do token e se existe regra ativa para a ocorrência
4. **Atualização**: se tudo estiver OK, cria `scores` e incrementa `sellers.points`

Se o vendedor **não existir** no setor do token, a API retorna **422** com:

- `Vendedor não encontrado no setor`

## Verificando os Resultados

### 1. Verificar se a Ocorrência foi Recebida

```bash
php artisan tinker
```

```php
\App\Models\ApiOccurrence::latest()->first();
```

### 2. Verificar se o Vendedor foi Criado/Atualizado

```php
\App\Models\Seller::where('email', 'vendedor@empresa.com')->first();
```

### 3. Verificar os Pontos Atribuídos

```php
$seller = \App\Models\Seller::where('email', 'vendedor@empresa.com')->first();
$seller->points; // Pontos totais
$seller->scores; // Histórico de pontuações
```

### 4. Processar Ocorrências Pendentes

Se a fila não estiver rodando, você pode processar manualmente:

```bash
php artisan process:api-occurrences
```

Ou iniciar o worker da fila:

```bash
php artisan queue:listen --tries=1
```

## Troubleshooting

### Erro 401 (Unauthorized)

- Verifique se o token está correto
- Verifique se o token está ativo no painel administrativo
- Verifique se a integração associada ao token está ativa

### Erro 422 (Validation Error)

- Verifique se o email está em formato válido
- Verifique se o campo `ocorrencia` está preenchido
- Verifique se o tipo de ocorrência corresponde a uma regra cadastrada

### Ocorrência não está gerando pontos

1. Verifique se a fila está rodando: `php artisan queue:listen`
2. Verifique se existe uma regra de pontuação para o tipo de ocorrência:
   ```php
   \App\Models\ScoreRule::where('ocorrencia', '30.1 - C/ PROPOSTA')->first();
   ```
3. Verifique se a regra está ativa (`is_active = true`)
4. Processe manualmente: `php artisan process:api-occurrences`

### Vendedor não aparece no dashboard

- Verifique se o vendedor tem uma temporada associada
- Verifique se o status do vendedor está como "active"
- Verifique se há uma temporada ativa no sistema

## Exemplo Completo de Teste

1. **Enviar uma ocorrência**:
   ```bash
   curl -X POST http://localhost:8000/api/webhook/occurrences \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer SEU_TOKEN_AQUI" \
     -d '{
       "email_funcionario": "IDENTIFICADOR_DO_VENDEDOR",
       "ocorrencia": "30.8 -  BOLETO PAGO",
       "credor": "Cliente ABC LTDA"
     }'
   ```

2. **Verificar resposta** (deve retornar 201 com ID)

3. **Processar ocorrências** (se a fila não estiver rodando):
   ```bash
   php artisan process:api-occurrences
   ```

4. **Verificar pontos no dashboard** ou via tinker:
   ```php
   $seller = \App\Models\Seller::where('email', 'vendedor@empresa.com')->first();
   echo "Pontos: " . $seller->points; // Deve ser 4 (BOLETO PAGO)
   ```

## Notas Importantes

- O campo `ocorrencia` deve corresponder **exatamente** ao valor cadastrado na tabela `score_rules`
- O sistema **não cria vendedores automaticamente** via webhook — cadastre o vendedor (no setor correto) antes de enviar ocorrências
- As ocorrências são processadas de forma assíncrona, então pode haver um pequeno delay entre o envio e a atualização dos pontos
- O campo `equipe` é opcional e não afeta a pontuação, apenas serve para referência/validação de vínculo
- Se sua equipe tiver “Nome de Exibição” no painel, isso é apenas visual. Para integrações, use sempre o **nome técnico** (`teams.name`) no campo `equipe`.
