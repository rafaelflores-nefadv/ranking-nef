<?php

/**
 * Script de Teste da API - Ranking NEF
 * 
 * Este script testa o envio de ocorrências via API usando um token de integração.
 * 
 * Uso:
 *   php test-api.php
 * 
 * Ou configure as variáveis abaixo antes de executar.
 */

// ============================================
// CONFIGURAÇÕES
// ============================================

// URL base da API (ajuste conforme necessário)
$baseUrl = getenv('RANKING_NEF_BASE_URL') ?: 'http://localhost:8000'; // ou 'https://seu-dominio.com'

// Token da integração (defina em RANKING_NEF_API_TOKEN)
$token = getenv('RANKING_NEF_API_TOKEN') ?: '';

if ($token === '') {
    fwrite(STDERR, "ERRO: defina a variável de ambiente RANKING_NEF_API_TOKEN com o token da integração.\n");
    exit(1);
}

// Identificador do vendedor (email ou external_code, conforme o token)
$identifier = getenv('RANKING_NEF_TEST_IDENTIFIER') ?: 'vendedor@empresa.com';

// Dados do usuário de teste (apenas para exibição)
$usuario = [
    'nome' => 'Teste',
    'email' => $identifier,
    'pontos' => 0,
    'equipe' => null, // ou nome da equipe se houver
    'status' => 'Ativo'
];

// Tipos de ocorrência disponíveis (conforme regras cadastradas)
$ocorrenciasDisponiveis = [
    '20.1 - SEM PROPOSTA/SEM PROMESSA' => 1,
    '20.2 - NÃO QUER PAGAR' => 1,
    '20.3 - DESCONHECE DÍVIDA' => 1,
    '30.1 - C/ PROPOSTA' => 2,
    '30.2 - C/PROMESSA' => 2,
    '30.3 - SOLICITOU RETORNO' => 1,
    '30.4 - AGÊNCIA' => 1,
    '30.5 - CPC WHATS' => 1,
    '30.6 - BOLETO ENVIADO' => 3,
    '30.7 - ALEGOU PAGAMENTO' => 1,
    '30.8 -  BOLETO PAGO' => 4,
    '10 - NÃO ATENDE' => 1,
    '10.2 - NÃO ATENDE - WHATS ' => 1,
    '98 - QUEBRA DE ACORDO' => 1,
];

// ============================================
// FUNÇÕES
// ============================================

/**
 * Envia uma ocorrência via API
 */
function enviarOcorrencia($baseUrl, $token, $emailFuncionario, $ocorrencia, $credor = null, $equipe = null) {
    $url = rtrim($baseUrl, '/') . '/api/webhook/occurrences';
    
    $data = [
        'email_funcionario' => $emailFuncionario,
        'ocorrencia' => $ocorrencia,
    ];
    
    if ($credor !== null) {
        $data['credor'] = $credor;
    }
    
    if ($equipe !== null) {
        $data['equipe'] = $equipe;
    }
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'Erro cURL: ' . $error,
            'http_code' => null,
            'response' => null
        ];
    }
    
    $responseData = json_decode($response, true);
    
    return [
        'success' => $httpCode === 201,
        'http_code' => $httpCode,
        'response' => $responseData,
        'error' => $httpCode !== 201 ? ($responseData['message'] ?? 'Erro desconhecido') : null
    ];
}

/**
 * Exibe resultado formatado
 */
function exibirResultado($resultado, $ocorrencia, $pontosEsperados) {
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "OCORRÊNCIA: {$ocorrencia}\n";
    echo "PONTOS ESPERADOS: {$pontosEsperados}\n";
    echo str_repeat('-', 70) . "\n";
    
    if ($resultado['success']) {
        echo "✓ SUCESSO!\n";
        echo "Código HTTP: {$resultado['http_code']}\n";
        echo "ID da Ocorrência: " . ($resultado['response']['id'] ?? 'N/A') . "\n";
        echo "Mensagem: " . ($resultado['response']['message'] ?? 'N/A') . "\n";
    } else {
        echo "✗ ERRO!\n";
        echo "Código HTTP: " . ($resultado['http_code'] ?? 'N/A') . "\n";
        echo "Erro: " . ($resultado['error'] ?? 'Erro desconhecido') . "\n";
        if (isset($resultado['response']['errors'])) {
            echo "Detalhes de validação:\n";
            foreach ($resultado['response']['errors'] as $campo => $erros) {
                echo "  - {$campo}: " . implode(', ', $erros) . "\n";
            }
        }
    }
    echo str_repeat('=', 70) . "\n";
}

// ============================================
// EXECUÇÃO
// ============================================

echo "\n";
echo str_repeat('=', 70) . "\n";
echo "TESTE DA API - RANKING NEF\n";
echo str_repeat('=', 70) . "\n";
echo "\n";
echo "Usuário de Teste:\n";
echo "  Nome: {$usuario['nome']}\n";
echo "  Email: {$usuario['email']}\n";
echo "  Pontos Atuais: {$usuario['pontos']}\n";
echo "  Equipe: " . ($usuario['equipe'] ?? 'Nenhuma') . "\n";
echo "  Status: {$usuario['status']}\n";
echo "\n";
echo "Token: " . substr($token, 0, 20) . "...\n";
echo "URL Base: {$baseUrl}\n";
echo "\n";

// Teste 1: Ocorrência simples (sem credor e equipe)
echo "\n" . str_repeat('*', 70) . "\n";
echo "TESTE 1: Ocorrência Simples\n";
echo str_repeat('*', 70) . "\n";

$ocorrenciaTeste1 = '30.1 - C/ PROPOSTA';
$pontosEsperados1 = $ocorrenciasDisponiveis[$ocorrenciaTeste1] ?? 0;

$resultado1 = enviarOcorrencia(
    $baseUrl,
    $token,
    $usuario['email'],
    $ocorrenciaTeste1
);

exibirResultado($resultado1, $ocorrenciaTeste1, $pontosEsperados1);

// Teste 2: Ocorrência com credor
echo "\n" . str_repeat('*', 70) . "\n";
echo "TESTE 2: Ocorrência com Credor\n";
echo str_repeat('*', 70) . "\n";

$ocorrenciaTeste2 = '30.8 -  BOLETO PAGO';
$pontosEsperados2 = $ocorrenciasDisponiveis[$ocorrenciaTeste2] ?? 0;

$resultado2 = enviarOcorrencia(
    $baseUrl,
    $token,
    $usuario['email'],
    $ocorrenciaTeste2,
    'Cliente Teste LTDA'
);

exibirResultado($resultado2, $ocorrenciaTeste2, $pontosEsperados2);

// Teste 3: Ocorrência com credor e equipe
if ($usuario['equipe']) {
    echo "\n" . str_repeat('*', 70) . "\n";
    echo "TESTE 3: Ocorrência com Credor e Equipe\n";
    echo str_repeat('*', 70) . "\n";
    
    $ocorrenciaTeste3 = '30.6 - BOLETO ENVIADO';
    $pontosEsperados3 = $ocorrenciasDisponiveis[$ocorrenciaTeste3] ?? 0;
    
    $resultado3 = enviarOcorrencia(
        $baseUrl,
        $token,
        $usuario['email'],
        $ocorrenciaTeste3,
        'Cliente Teste LTDA',
        $usuario['equipe']
    );
    
    exibirResultado($resultado3, $ocorrenciaTeste3, $pontosEsperados3);
}

// Resumo
echo "\n" . str_repeat('=', 70) . "\n";
echo "RESUMO DOS TESTES\n";
echo str_repeat('=', 70) . "\n";
echo "\n";

$sucessos = 0;
$erros = 0;

if ($resultado1['success']) $sucessos++; else $erros++;
if ($resultado2['success']) $sucessos++; else $erros++;
if (isset($resultado3) && $resultado3['success']) $sucessos++; else if (isset($resultado3)) $erros++;

echo "Testes bem-sucedidos: {$sucessos}\n";
echo "Testes com erro: {$erros}\n";
echo "\n";

if ($sucessos > 0) {
    echo "✓ Ocorrências enviadas com sucesso serão processadas em background.\n";
    echo "  Verifique o dashboard para ver os pontos atualizados.\n";
    echo "  Execute 'php artisan queue:listen' se a fila não estiver rodando.\n";
}

echo "\n";
echo "TIPOS DE OCORRÊNCIA DISPONÍVEIS:\n";
echo str_repeat('-', 70) . "\n";
foreach ($ocorrenciasDisponiveis as $ocorrencia => $pontos) {
    echo sprintf("  %-45s => %2.0f pontos\n", $ocorrencia, $pontos);
}
echo "\n";
