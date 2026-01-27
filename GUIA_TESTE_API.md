# Guia de Teste da API - Ranking NEF

Este guia explica como testar a API com o usuário fornecido e o token REVO.

## Informações do Usuário de Teste

- **Nome**: Teste
- **Email**: teste@extranef.com.br
- **Pontos**: 0
- **Equipe**: - (nenhuma)
- **Temporada**: Temporada Atual
- **Status**: Ativo

## Token REVO

- **Token**: `rknf_LEQRc2mBKNviubO9rQijMNFrT4fwQAO1`
- **Secret**: `wK7e985cqiKDJEww4cnBUXG8gPq8g1TI5YV1almafDMZK30yzog3j5oWjAoRiOjC`

> **Nota**: O secret não é necessário para fazer requisições à API, apenas o token.

## Regras de Pontuação Cadastradas

As seguintes regras de pontuação estão disponíveis no sistema:

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

**Antes de executar**, ajuste a variável `$baseUrl` no início do arquivo se necessário.

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
  -H "Authorization: Bearer rknf_LEQRc2mBKNviubO9rQijMNFrT4fwQAO1" \
  -d '{
    "email_funcionario": "teste@extranef.com.br",
    "ocorrencia": "30.1 - C/ PROPOSTA"
  }'
```

#### Exemplo 2: Ocorrência com Credor

```bash
curl -X POST http://localhost:8000/api/webhook/occurrences \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer rknf_LEQRc2mBKNviubO9rQijMNFrT4fwQAO1" \
  -d '{
    "email_funcionario": "teste@extranef.com.br",
    "ocorrencia": "30.8 -  BOLETO PAGO",
    "credor": "Cliente Teste LTDA"
  }'
```

#### Exemplo 3: Ocorrência Completa (com credor e equipe)

```bash
curl -X POST http://localhost:8000/api/webhook/occurrences \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer rknf_LEQRc2mBKNviubO9rQijMNFrT4fwQAO1" \
  -d '{
    "email_funcionario": "teste@extranef.com.br",
    "ocorrencia": "30.6 - BOLETO ENVIADO",
    "credor": "Cliente Teste LTDA",
    "equipe": "Equipe Teste"
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
3. **Validação**: O sistema verifica se existe um vendedor com o email informado
4. **Criação Automática**: Se o vendedor não existir, ele será criado automaticamente com:
   - Nome: o próprio email (pode ser atualizado depois)
   - Pontos: 0
   - Status: active
5. **Regras de Pontuação**: O sistema busca a regra de pontuação correspondente ao tipo de ocorrência
6. **Atualização**: Se a regra existir e estiver ativa, os pontos são atribuídos ao vendedor

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
\App\Models\Seller::where('email', 'teste@extranef.com.br')->first();
```

### 3. Verificar os Pontos Atribuídos

```php
$seller = \App\Models\Seller::where('email', 'teste@extranef.com.br')->first();
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
     -H "Authorization: Bearer rknf_LEQRc2mBKNviubO9rQijMNFrT4fwQAO1" \
     -d '{
       "email_funcionario": "teste@extranef.com.br",
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
   $seller = \App\Models\Seller::where('email', 'teste@extranef.com.br')->first();
   echo "Pontos: " . $seller->points; // Deve ser 4 (BOLETO PAGO)
   ```

## Notas Importantes

- O campo `ocorrencia` deve corresponder **exatamente** ao valor cadastrado na tabela `score_rules`
- O sistema cria automaticamente vendedores que não existem, mas é recomendado criar o vendedor antes de enviar ocorrências
- As ocorrências são processadas de forma assíncrona, então pode haver um pequeno delay entre o envio e a atualização dos pontos
- O campo `equipe` é opcional e não afeta a pontuação, apenas serve para referência
