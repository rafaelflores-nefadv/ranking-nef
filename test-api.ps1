# Script de Teste da API - Ranking NEF (PowerShell)
# 
# Este script testa o envio de ocorrencias via API usando o token REVO fornecido.
# 
# Uso:
#   .\test-api.ps1

# ============================================
# CONFIGURACOES
# ============================================

# URL base da API (ajuste conforme necessario)
$baseUrl = "http://localhost:8000"

# Token REVO fornecido
$token = "rknf_LEQRc2mBKNviubO9rQijMNFrT4fwQAO1"

# Dados do usuario de teste
$usuario = @{
    nome = "Teste"
    email = "teste@extranef.com.br"
    pontos = 0
    equipe = $null
    status = "Ativo"
}

# Tipos de ocorrencia disponiveis (conforme regras cadastradas)
$ocorrenciasDisponiveis = @{
    "20.1 - SEM PROPOSTA/SEM PROMESSA" = 1
    "20.2 - NAO QUER PAGAR" = 1
    "20.3 - DESCONHECE DIVIDA" = 1
    "30.1 - C/ PROPOSTA" = 2
    "30.2 - C/PROMESSA" = 2
    "30.3 - SOLICITOU RETORNO" = 1
    "30.4 - AGENCIA" = 1
    "30.5 - CPC WHATS" = 1
    "30.6 - BOLETO ENVIADO" = 3
    "30.7 - ALEGOU PAGAMENTO" = 1
    "30.8 -  BOLETO PAGO" = 4
    "10 - NAO ATENDE" = 1
    "10.2 - NAO ATENDE - WHATS " = 1
    "98 - QUEBRA DE ACORDO" = 1
}

# ============================================
# FUNCOES
# ============================================

function Enviar-Ocorrencia {
    param(
        [string]$baseUrl,
        [string]$token,
        [string]$emailFuncionario,
        [string]$ocorrencia,
        [string]$credor = $null,
        [string]$equipe = $null
    )
    
    $url = "$baseUrl/api/webhook/occurrences"
    
    $body = @{
        email_funcionario = $emailFuncionario
        ocorrencia = $ocorrencia
    }
    
    if ($credor) {
        $body.credor = $credor
    }
    
    if ($equipe) {
        $body.equipe = $equipe
    }
    
    $headers = @{
        "Content-Type" = "application/json"
        "Authorization" = "Bearer $token"
    }
    
    try {
        $response = Invoke-RestMethod -Uri $url -Method Post -Headers $headers -Body ($body | ConvertTo-Json) -ErrorAction Stop
        
        return @{
            success = $true
            http_code = 201
            response = $response
            error = $null
        }
    }
    catch {
        $statusCode = $null
        $errorResponse = $null
        $errorMessage = $_.Exception.Message
        
        if ($_.Exception.Response) {
            $statusCode = $_.Exception.Response.StatusCode.value__
            
            try {
                $stream = $_.Exception.Response.GetResponseStream()
                $reader = New-Object System.IO.StreamReader($stream)
                $responseBody = $reader.ReadToEnd()
                $reader.Close()
                $stream.Close()
                $errorResponse = $responseBody | ConvertFrom-Json
                if ($errorResponse -and $errorResponse.message) {
                    $errorMessage = $errorResponse.message
                }
            }
            catch {
                # Ignorar erro ao parsear JSON
            }
        }
        
        return @{
            success = $false
            http_code = $statusCode
            response = $errorResponse
            error = $errorMessage
        }
    }
}

function Exibir-Resultado {
    param(
        [hashtable]$resultado,
        [string]$ocorrencia,
        [int]$pontosEsperados
    )
    
    Write-Host ""
    Write-Host ("=" * 70)
    Write-Host "OCORRENCIA: $ocorrencia"
    Write-Host "PONTOS ESPERADOS: $pontosEsperados"
    Write-Host ("-" * 70)
    
    if ($resultado.success) {
        Write-Host "SUCESSO!" -ForegroundColor Green
        Write-Host "Codigo HTTP: $($resultado.http_code)"
        Write-Host "ID da Ocorrencia: $($resultado.response.id)"
        Write-Host "Mensagem: $($resultado.response.message)"
    }
    else {
        Write-Host "ERRO!" -ForegroundColor Red
        Write-Host "Codigo HTTP: $($resultado.http_code)"
        Write-Host "Erro: $($resultado.error)"
        if ($resultado.response -and $resultado.response.errors) {
            Write-Host "Detalhes de validacao:"
            foreach ($campo in $resultado.response.errors.PSObject.Properties.Name) {
                $erros = $resultado.response.errors.$campo -join ", "
                Write-Host "  - $campo : $erros"
            }
        }
    }
    Write-Host ("=" * 70)
}

# ============================================
# EXECUCAO
# ============================================

Write-Host ""
Write-Host ("=" * 70)
Write-Host "TESTE DA API - RANKING NEF"
Write-Host ("=" * 70)
Write-Host ""
Write-Host "Usuario de Teste:"
Write-Host "  Nome: $($usuario.nome)"
Write-Host "  Email: $($usuario.email)"
Write-Host "  Pontos Atuais: $($usuario.pontos)"
if ($usuario.equipe) {
    Write-Host "  Equipe: $($usuario.equipe)"
} else {
    Write-Host "  Equipe: Nenhuma"
}
Write-Host "  Status: $($usuario.status)"
Write-Host ""
Write-Host "Token REVO: $($token.Substring(0, 20))..."
Write-Host "URL Base: $baseUrl"
Write-Host ""

# Teste 1: Ocorrencia simples (sem credor e equipe)
Write-Host ""
Write-Host ("*" * 70)
Write-Host "TESTE 1: Ocorrencia Simples"
Write-Host ("*" * 70)

$ocorrenciaTeste1 = "30.1 - C/ PROPOSTA"
$pontosEsperados1 = $ocorrenciasDisponiveis[$ocorrenciaTeste1]

$resultado1 = Enviar-Ocorrencia -baseUrl $baseUrl -token $token -emailFuncionario $usuario.email -ocorrencia $ocorrenciaTeste1

Exibir-Resultado -resultado $resultado1 -ocorrencia $ocorrenciaTeste1 -pontosEsperados $pontosEsperados1

# Teste 2: Ocorrencia com credor
Write-Host ""
Write-Host ("*" * 70)
Write-Host "TESTE 2: Ocorrencia com Credor"
Write-Host ("*" * 70)

$ocorrenciaTeste2 = "30.8 -  BOLETO PAGO"
$pontosEsperados2 = $ocorrenciasDisponiveis[$ocorrenciaTeste2]

$resultado2 = Enviar-Ocorrencia -baseUrl $baseUrl -token $token -emailFuncionario $usuario.email -ocorrencia $ocorrenciaTeste2 -credor "Cliente Teste LTDA"

Exibir-Resultado -resultado $resultado2 -ocorrencia $ocorrenciaTeste2 -pontosEsperados $pontosEsperados2

# Teste 3: Ocorrencia com credor e equipe
if ($usuario.equipe) {
    Write-Host ""
    Write-Host ("*" * 70)
    Write-Host "TESTE 3: Ocorrencia com Credor e Equipe"
    Write-Host ("*" * 70)
    
    $ocorrenciaTeste3 = "30.6 - BOLETO ENVIADO"
    $pontosEsperados3 = $ocorrenciasDisponiveis[$ocorrenciaTeste3]
    
    $resultado3 = Enviar-Ocorrencia -baseUrl $baseUrl -token $token -emailFuncionario $usuario.email -ocorrencia $ocorrenciaTeste3 -credor "Cliente Teste LTDA" -equipe $usuario.equipe
    
    Exibir-Resultado -resultado $resultado3 -ocorrencia $ocorrenciaTeste3 -pontosEsperados $pontosEsperados3
}

# Resumo
Write-Host ""
Write-Host ("=" * 70)
Write-Host "RESUMO DOS TESTES"
Write-Host ("=" * 70)
Write-Host ""

$sucessos = 0
$erros = 0

if ($resultado1.success) {
    $sucessos++
} else {
    $erros++
}

if ($resultado2.success) {
    $sucessos++
} else {
    $erros++
}

if ($resultado3) {
    if ($resultado3.success) {
        $sucessos++
    } else {
        $erros++
    }
}

Write-Host "Testes bem-sucedidos: $sucessos"
Write-Host "Testes com erro: $erros"
Write-Host ""

if ($sucessos -gt 0) {
    Write-Host "Ocorrencias enviadas com sucesso serao processadas em background." -ForegroundColor Green
    Write-Host "  Verifique o dashboard para ver os pontos atualizados."
    Write-Host "  Execute 'php artisan queue:listen' se a fila nao estiver rodando."
}

Write-Host ""
Write-Host "TIPOS DE OCORRENCIA DISPONIVEIS:"
Write-Host ("-" * 70)
$ocorrenciasOrdenadas = $ocorrenciasDisponiveis.Keys | Sort-Object
foreach ($ocorrencia in $ocorrenciasOrdenadas) {
    $pontos = $ocorrenciasDisponiveis[$ocorrencia]
    $ocorrenciaFormatada = $ocorrencia.PadRight(45)
    Write-Host "  $ocorrenciaFormatada => $pontos pontos"
}
Write-Host ""
