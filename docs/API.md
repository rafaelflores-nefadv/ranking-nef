# Documentação da API - Ranking NEF

Esta documentação descreve como integrar sistemas de terceiros com o Ranking NEF através da API.

## Índice

1. [Visão Geral](#visão-geral)
2. [Autenticação](#autenticação)
3. [Endpoints](#endpoints)
4. [Exemplos de Uso](#exemplos-de-uso)
5. [Códigos de Status HTTP](#códigos-de-status-http)
6. [Tratamento de Erros](#tratamento-de-erros)
7. [Suporte](#suporte)

---

## Visão Geral

A API do Ranking NEF permite que sistemas externos enviem ocorrências de vendas e eventos que serão processados automaticamente para atualizar o ranking de vendedores.

### Base URL

```
https://seu-dominio.com/api
```

### Formato de Dados

Todas as requisições e respostas utilizam o formato **JSON** com `Content-Type: application/json`.

---

## Autenticação

A API utiliza autenticação via **Bearer Token**. Cada integração possui um token único que deve ser enviado no header `Authorization` de todas as requisições.

### Como Obter um Token

1. Acesse o sistema Ranking NEF com uma conta de **Administrador**
2. Navegue até **Configurações > Integrações API**
3. Crie uma nova integração ou edite uma existente
4. Gere um token para a integração
5. **IMPORTANTE**: Guarde o token e o secret com segurança. O secret não será exibido novamente.

### Formato do Header

```http
Authorization: Bearer rknf_seu_token_aqui
```

**Exemplo:**
```http
Authorization: Bearer rknf_abc123def456ghi789jkl012mno345pqr678stu901vwx234yz
```

### Validação do Token

O token será validado automaticamente em cada requisição. Um token é considerado válido quando:
- O token existe no sistema
- O token está ativo (`is_active = true`)
- A integração associada ao token está ativa

---

## Endpoints

### 1. Enviar Ocorrência (Webhook)

Endpoint principal para enviar ocorrências de vendas e eventos que serão processados pelo sistema.

#### Endpoint

```
POST /api/webhook/occurrences
```

#### Headers

```http
Content-Type: application/json
Authorization: Bearer rknf_seu_token_aqui
```

#### Body (JSON)

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `email_funcionario` | string (email) | Sim | Email do funcionário/vendedor que realizou a venda ou evento |
| `ocorrencia` | string | Sim | Tipo de ocorrência (ex: "Venda", "Contato", "Proposta", etc.) |
| `credor` | string | Não | Nome do credor (opcional) |
| `equipe` | string | Não | Nome da equipe (opcional) |

#### Exemplo de Requisição

```json
{
  "email_funcionario": "vendedor@empresa.com",
  "ocorrencia": "Venda",
  "credor": "Cliente ABC Ltda",
  "equipe": "Equipe Norte"
}
```

#### Resposta de Sucesso (201 Created)

```json
{
  "message": "Ocorrência recebida com sucesso",
  "id": "550e8400-e29b-41d4-a716-446655440000"
}
```

#### Resposta de Erro (401 Unauthorized)

```json
{
  "message": "Token inválido ou inativo",
  "error": "Unauthorized"
}
```

#### Resposta de Erro (422 Validation Error)

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

---

## Exemplos de Uso

### Exemplo 1: Enviar Ocorrência de Venda (cURL)

```bash
curl -X POST https://seu-dominio.com/api/webhook/occurrences \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer rknf_abc123def456ghi789jkl012mno345pqr678stu901vwx234yz" \
  -d '{
    "email_funcionario": "joao.silva@empresa.com",
    "ocorrencia": "Venda",
    "credor": "Empresa XYZ Ltda",
    "equipe": "Equipe Sul"
  }'
```

### Exemplo 2: Enviar Ocorrência de Venda (JavaScript/Fetch)

```javascript
async function enviarOcorrencia(dados) {
  const response = await fetch('https://seu-dominio.com/api/webhook/occurrences', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer rknf_abc123def456ghi789jkl012mno345pqr678stu901vwx234yz'
    },
    body: JSON.stringify({
      email_funcionario: dados.email,
      ocorrencia: dados.tipo,
      credor: dados.credor,
      equipe: dados.equipe
    })
  });

  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message || 'Erro ao enviar ocorrência');
  }

  return await response.json();
}

// Uso
enviarOcorrencia({
  email: 'joao.silva@empresa.com',
  tipo: 'Venda',
  credor: 'Empresa XYZ Ltda',
  equipe: 'Equipe Sul'
})
  .then(result => console.log('Sucesso:', result))
  .catch(error => console.error('Erro:', error));
```

### Exemplo 3: Enviar Ocorrência de Venda (PHP)

```php
<?php

function enviarOcorrencia($dados) {
    $url = 'https://seu-dominio.com/api/webhook/occurrences';
    $token = 'rknf_abc123def456ghi789jkl012mno345pqr678stu901vwx234yz';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 201) {
        throw new Exception('Erro ao enviar ocorrência: ' . $response);
    }
    
    return json_decode($response, true);
}

// Uso
try {
    $resultado = enviarOcorrencia([
        'email_funcionario' => 'joao.silva@empresa.com',
        'ocorrencia' => 'Venda',
        'credor' => 'Empresa XYZ Ltda',
        'equipe' => 'Equipe Sul'
    ]);
    echo "Ocorrência enviada com sucesso! ID: " . $resultado['id'];
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
```

### Exemplo 4: Enviar Ocorrência de Venda (Python)

```python
import requests
import json

def enviar_ocorrencia(dados):
    url = 'https://seu-dominio.com/api/webhook/occurrences'
    token = 'rknf_abc123def456ghi789jkl012mno345pqr678stu901vwx234yz'
    
    headers = {
        'Content-Type': 'application/json',
        'Authorization': f'Bearer {token}'
    }
    
    response = requests.post(url, headers=headers, json=dados)
    
    if response.status_code != 201:
        raise Exception(f'Erro ao enviar ocorrência: {response.text}')
    
    return response.json()

# Uso
try:
    resultado = enviar_ocorrencia({
        'email_funcionario': 'joao.silva@empresa.com',
        'ocorrencia': 'Venda',
        'credor': 'Empresa XYZ Ltda',
        'equipe': 'Equipe Sul'
    })
    print(f"Ocorrência enviada com sucesso! ID: {resultado['id']}")
except Exception as e:
    print(f"Erro: {e}")
```

---

## Códigos de Status HTTP

| Código | Descrição | Quando Ocorre |
|--------|-----------|--------------|
| `200` | OK | Requisição processada com sucesso |
| `201` | Created | Recurso criado com sucesso |
| `400` | Bad Request | Requisição malformada |
| `401` | Unauthorized | Token inválido, ausente ou inativo |
| `403` | Forbidden | Acesso negado (permissões insuficientes) |
| `404` | Not Found | Recurso não encontrado |
| `422` | Unprocessable Entity | Erro de validação dos dados |
| `500` | Internal Server Error | Erro interno do servidor |

---

## Tratamento de Erros

### Estrutura de Erro Padrão

Todas as respostas de erro seguem o formato:

```json
{
  "message": "Mensagem de erro descritiva",
  "error": "Tipo do erro (opcional)"
}
```

### Erros de Validação (422)

Quando há erros de validação, a resposta inclui detalhes dos campos inválidos:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email_funcionario": [
      "The email funcionario field is required.",
      "The email funcionario must be a valid email address."
    ],
    "ocorrencia": [
      "The ocorrencia field is required."
    ]
  }
}
```

### Erros Comuns e Soluções

#### 401 Unauthorized

**Causa:** Token inválido, ausente ou inativo.

**Solução:**
- Verifique se o token está sendo enviado no header `Authorization`
- Verifique se o token está no formato correto: `Bearer rknf_...`
- Verifique se o token está ativo no painel administrativo
- Verifique se a integração associada ao token está ativa

#### 422 Validation Error

**Causa:** Dados enviados não passaram na validação.

**Solução:**
- Verifique se todos os campos obrigatórios estão presentes
- Verifique se o email está em formato válido
- Verifique os tipos de dados (strings, números, etc.)

#### 500 Internal Server Error

**Causa:** Erro interno do servidor.

**Solução:**
- Tente novamente após alguns instantes
- Verifique se o servidor está operacional
- Entre em contato com o suporte técnico

---

## Processamento de Ocorrências

### Como Funciona

1. **Recebimento**: A ocorrência é recebida via webhook e armazenada no banco de dados
2. **Processamento Assíncrono**: Um job em background processa a ocorrência
3. **Validação**: O sistema verifica se existe um vendedor com o email informado
4. **Regras de Pontuação**: O sistema busca regras de pontuação ativas para o tipo de ocorrência
5. **Atualização**: Os pontos são atribuídos ao vendedor e o ranking é atualizado

### Requisitos para Processamento Automático (Produção)

O processamento é feito por **jobs em fila**. Para que as ocorrências sejam processadas automaticamente, é necessário:

- **Scheduler (cron)** executando `php artisan schedule:run` a cada minuto (responsável por disparar comandos agendados)
- **Worker da fila** rodando 24/7 (ex.: `php artisan queue:work ...`), especialmente quando `QUEUE_CONNECTION=database`

Se o seu sistema “recebe na API mas não processa” (ocorrências ficam com `processed = 0`), quase sempre é porque:

- o cron do scheduler não está configurado, e/ou
- o worker da fila não está em execução

Veja o guia de produção em `docs/CRON_SETUP.md`.

### Tipos de Ocorrência

O campo `ocorrencia` deve corresponder a uma regra de pontuação cadastrada no sistema. Exemplos comuns:
- `Venda`
- `Contato`
- `Proposta`
- `Reunião`
- `Follow-up`

**Nota:** O administrador do sistema deve cadastrar as regras de pontuação correspondentes a cada tipo de ocorrência.

### Vendedores

Para que uma ocorrência seja aceita e gere pontos, é necessário que exista um vendedor cadastrado **no mesmo setor do token**.

Se o vendedor não existir (ou não pertencer ao setor), a API **retorna 422** com a mensagem:

- `Vendedor não encontrado no setor`

Da mesma forma, a API pode retornar 422 quando:

- a equipe informada não existe no setor ou o vendedor não pertence a ela (`Equipe fora do setor`)
- não existe regra ativa para a ocorrência no setor (`Regra inexistente no setor`)

---

## Boas Práticas

### 1. Tratamento de Erros

Sempre implemente tratamento de erros robusto:

```javascript
try {
  const response = await fetch(url, options);
  
  if (!response.ok) {
    const error = await response.json();
    // Log do erro para debug
    console.error('Erro na API:', error);
    // Tratamento específico por código de status
    if (response.status === 401) {
      // Token inválido - reautenticar ou notificar
    } else if (response.status === 422) {
      // Erro de validação - corrigir dados
    }
    throw new Error(error.message);
  }
  
  return await response.json();
} catch (error) {
  // Tratamento de erros de rede ou outros
  console.error('Erro na requisição:', error);
  throw error;
}
```

### 2. Retry em Caso de Falha

Implemente retry para requisições que falharam por erro de rede ou servidor:

```javascript
async function enviarComRetry(dados, maxTentativas = 3) {
  for (let i = 0; i < maxTentativas; i++) {
    try {
      return await enviarOcorrencia(dados);
    } catch (error) {
      if (i === maxTentativas - 1) throw error;
      // Aguardar antes de tentar novamente (exponential backoff)
      await new Promise(resolve => setTimeout(resolve, Math.pow(2, i) * 1000));
    }
  }
}
```

### 3. Validação Local

Valide os dados antes de enviar para evitar erros 422:

```javascript
function validarOcorrencia(dados) {
  const erros = [];
  
  if (!dados.email_funcionario || !dados.email_funcionario.includes('@')) {
    erros.push('Email do funcionário é obrigatório e deve ser válido');
  }
  
  if (!dados.ocorrencia || dados.ocorrencia.trim() === '') {
    erros.push('Tipo de ocorrência é obrigatório');
  }
  
  return erros;
}
```

### 4. Logging

Mantenha logs das requisições para facilitar o debug:

```javascript
function enviarOcorrenciaComLog(dados) {
  console.log('[API] Enviando ocorrência:', dados);
  
  return enviarOcorrencia(dados)
    .then(resultado => {
      console.log('[API] Ocorrência enviada com sucesso:', resultado);
      return resultado;
    })
    .catch(erro => {
      console.error('[API] Erro ao enviar ocorrência:', erro);
      throw erro;
    });
}
```

### 5. Segurança do Token

- **Nunca** exponha o token em código frontend público
- Armazene o token em variáveis de ambiente ou configurações seguras
- Use HTTPS para todas as requisições
- Rotacione os tokens periodicamente
- Desative tokens que não estão mais em uso

---

## Limites e Rate Limiting

Atualmente, não há limites rígidos de requisições por minuto. No entanto, recomendamos:

- **Máximo de 100 requisições por minuto** por token
- Implementar throttling no seu sistema para evitar sobrecarga
- Processar ocorrências em lote quando possível

Se você precisar de limites maiores, entre em contato com o suporte.

---

## Suporte

Para dúvidas, problemas ou solicitações relacionadas à API:

1. **Documentação**: Consulte esta documentação primeiro
2. **Logs**: Verifique os logs do seu sistema para identificar erros
3. **Contato**: Entre em contato com a equipe de suporte técnico

### Informações para Suporte

Ao solicitar suporte, forneça:
- Token da integração (mascarado: `rknf_***`)
- URL do endpoint utilizado
- Código de status HTTP recebido
- Corpo da requisição (sem dados sensíveis)
- Corpo da resposta de erro
- Timestamp da requisição
- Logs relevantes do seu sistema

---

## Changelog

### Versão 1.0 (Atual)
- Endpoint de webhook para envio de ocorrências
- Autenticação via Bearer Token
- Processamento assíncrono de ocorrências
- Validação de dados
- Suporte a tipos de ocorrência customizados

---

## Exemplo Completo de Integração

Abaixo está um exemplo completo de integração em JavaScript/Node.js:

```javascript
class RankingNEFClient {
  constructor(baseUrl, token) {
    this.baseUrl = baseUrl;
    this.token = token;
  }

  async enviarOcorrencia(dados) {
    const url = `${this.baseUrl}/api/webhook/occurrences`;
    
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${this.token}`
      },
      body: JSON.stringify({
        email_funcionario: dados.email,
        ocorrencia: dados.tipo,
        credor: dados.credor || null,
        equipe: dados.equipe || null
      })
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Erro ao enviar ocorrência');
    }

    return await response.json();
  }

  async enviarComRetry(dados, maxTentativas = 3) {
    for (let i = 0; i < maxTentativas; i++) {
      try {
        return await this.enviarOcorrencia(dados);
      } catch (error) {
        if (i === maxTentativas - 1) throw error;
        await new Promise(resolve => setTimeout(resolve, Math.pow(2, i) * 1000));
      }
    }
  }
}

// Uso
const client = new RankingNEFClient(
  'https://seu-dominio.com',
  'rknf_abc123def456ghi789jkl012mno345pqr678stu901vwx234yz'
);

client.enviarComRetry({
  email: 'joao.silva@empresa.com',
  tipo: 'Venda',
  credor: 'Empresa XYZ Ltda',
  equipe: 'Equipe Sul'
})
  .then(result => console.log('Sucesso:', result))
  .catch(error => console.error('Erro:', error));
```

---

**Última atualização:** Janeiro 2026
