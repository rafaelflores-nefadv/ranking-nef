# Exemplo Rápido de Teste da API

## Teste Rápido com cURL

### 1. Teste Básico - Ocorrência Simples

```bash
curl -X POST http://localhost:8000/api/webhook/occurrences \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer rknf_LEQRc2mBKNviubO9rQijMNFrT4fwQAO1" \
  -d '{
    "email_funcionario": "teste@extranef.com.br",
    "ocorrencia": "30.1 - C/ PROPOSTA"
  }'
```

**Resultado esperado**: 201 Created com ID da ocorrência
**Pontos esperados**: 2 pontos (conforme regra cadastrada)

### 2. Teste com Credor

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

**Resultado esperado**: 201 Created
**Pontos esperados**: 4 pontos (maior pontuação disponível)

### 3. Teste com Credor e Equipe

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

**Resultado esperado**: 201 Created
**Pontos esperados**: 3 pontos

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
$seller = \App\Models\Seller::where('email', 'teste@extranef.com.br')->first();
echo "Nome: " . $seller->name . "\n";
echo "Email: " . $seller->email . "\n";
echo "Pontos: " . $seller->points . "\n";
echo "Status: " . $seller->status . "\n";
```

## Checklist de Teste

- [ ] Token está correto e ativo
- [ ] URL base está correta (localhost:8000 ou seu domínio)
- [ ] Email do funcionário está correto: `teste@extranef.com.br`
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
