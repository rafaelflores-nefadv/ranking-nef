#!/bin/bash

# Script de Teste da API - Ranking NEF (Bash/cURL)
# 
# Este script testa o envio de ocorrências via API usando um token de integração.
# 
# Uso:
#   chmod +x test-api-curl.sh
#   ./test-api-curl.sh

# ============================================
# CONFIGURAÇÕES
# ============================================

# URL base da API (ajuste conforme necessário)
BASE_URL="${RANKING_NEF_BASE_URL:-http://localhost:8000}"  # ou "https://seu-dominio.com"

# Token da integração (defina em RANKING_NEF_API_TOKEN)
TOKEN="${RANKING_NEF_API_TOKEN:-}"

if [ -z "$TOKEN" ]; then
  echo "ERRO: defina a variável de ambiente RANKING_NEF_API_TOKEN com o token da integração."
  exit 1
fi

# Dados do usuário de teste
USUARIO_EMAIL="${RANKING_NEF_TEST_IDENTIFIER:-vendedor@empresa.com}"
USUARIO_NOME="Teste"

# ============================================
# FUNÇÕES
# ============================================

enviar_ocorrencia() {
    local email_funcionario=$1
    local ocorrencia=$2
    local credor=$3
    local equipe=$4
    
    local url="${BASE_URL}/api/webhook/occurrences"
    
    local json_data="{"
    json_data+="\"email_funcionario\":\"${email_funcionario}\","
    json_data+="\"ocorrencia\":\"${ocorrencia}\""
    
    if [ -n "$credor" ]; then
        json_data+=",\"credor\":\"${credor}\""
    fi
    
    if [ -n "$equipe" ]; then
        json_data+=",\"equipe\":\"${equipe}\""
    fi
    
    json_data+="}"
    
    echo "Enviando: $json_data"
    echo ""
    
    response=$(curl -s -w "\n%{http_code}" -X POST "$url" \
        -H "Content-Type: application/json" \
        -H "Authorization: Bearer ${TOKEN}" \
        -d "$json_data")
    
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    echo "Código HTTP: $http_code"
    echo "Resposta: $body"
    echo ""
    
    if [ "$http_code" = "201" ]; then
        echo "✓ SUCESSO!"
    else
        echo "✗ ERRO!"
    fi
    
    echo "----------------------------------------"
    echo ""
}

# ============================================
# EXECUÇÃO
# ============================================

echo "============================================================"
echo "TESTE DA API - RANKING NEF"
echo "============================================================"
echo ""
echo "Usuário de Teste:"
echo "  Nome: $USUARIO_NOME"
echo "  Email: $USUARIO_EMAIL"
echo ""
echo "Token: ${TOKEN:0:20}..."
echo "URL Base: $BASE_URL"
echo ""

# Teste 1: Ocorrência simples
echo "============================================================"
echo "TESTE 1: Ocorrência Simples"
echo "============================================================"
enviar_ocorrencia "$USUARIO_EMAIL" "30.1 - C/ PROPOSTA"

# Teste 2: Ocorrência com credor
echo "============================================================"
echo "TESTE 2: Ocorrência com Credor"
echo "============================================================"
enviar_ocorrencia "$USUARIO_EMAIL" "30.8 -  BOLETO PAGO" "Cliente Teste LTDA"

# Teste 3: Ocorrência com credor e equipe
echo "============================================================"
echo "TESTE 3: Ocorrência com Credor e Equipe"
echo "============================================================"
# Nota: "equipe" deve ser o nome técnico (teams.name), usado pelas integrações/API.
enviar_ocorrencia "$USUARIO_EMAIL" "30.6 - BOLETO ENVIADO" "Cliente Teste LTDA" "equipe_teste"

echo ""
echo "============================================================"
echo "TIPOS DE OCORRÊNCIA DISPONÍVEIS:"
echo "============================================================"
echo "  20.1 - SEM PROPOSTA/SEM PROMESSA => 1 ponto"
echo "  20.2 - NÃO QUER PAGAR => 1 ponto"
echo "  20.3 - DESCONHECE DÍVIDA => 1 ponto"
echo "  30.1 - C/ PROPOSTA => 2 pontos"
echo "  30.2 - C/PROMESSA => 2 pontos"
echo "  30.3 - SOLICITOU RETORNO => 1 ponto"
echo "  30.4 - AGÊNCIA => 1 ponto"
echo "  30.5 - CPC WHATS => 1 ponto"
echo "  30.6 - BOLETO ENVIADO => 3 pontos"
echo "  30.7 - ALEGOU PAGAMENTO => 1 ponto"
echo "  30.8 -  BOLETO PAGO => 4 pontos"
echo "  10 - NÃO ATENDE => 1 ponto"
echo "  10.2 - NÃO ATENDE - WHATS => 1 ponto"
echo "  98 - QUEBRA DE ACORDO => 1 ponto"
echo ""
