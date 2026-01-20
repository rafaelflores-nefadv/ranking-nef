<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessApiOccurrencesJob;
use App\Models\ApiOccurrence;
use App\Models\ApiToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookOccurrenceController extends Controller
{
    /**
     * Recebe ocorrências via webhook
     */
    public function store(Request $request): JsonResponse
    {
        // Validar token de autenticação
        $token = $this->validateToken($request);
        
        if (!$token) {
            return response()->json([
                'message' => 'Token inválido ou inativo',
                'error' => 'Unauthorized',
            ], 401);
        }

        // Validar dados da requisição
        $validated = $request->validate([
            'email_funcionario' => 'required|string|email',
            'ocorrencia' => 'required|string',
            'credor' => 'nullable|string',
            'equipe' => 'nullable|string',
        ]);

        // Atualizar último uso do token
        $token->update([
            'last_used_at' => now(),
        ]);

        $occurrence = ApiOccurrence::create($validated);

        // Disparar job de processamento
        ProcessApiOccurrencesJob::dispatch();

        return response()->json([
            'message' => 'Ocorrência recebida com sucesso',
            'id' => $occurrence->id,
        ], 201);
    }

    /**
     * Valida o token de autenticação do request
     */
    private function validateToken(Request $request): ?ApiToken
    {
        $authorization = $request->header('Authorization');
        
        if (!$authorization) {
            return null;
        }

        // Extrair token do formato "Bearer {token}"
        if (preg_match('/Bearer\s+(.+)$/i', $authorization, $matches)) {
            $tokenValue = trim($matches[1]);
        } else {
            // Tentar usar o valor diretamente se não tiver "Bearer"
            $tokenValue = trim($authorization);
        }

        if (empty($tokenValue)) {
            return null;
        }

        // Buscar token no banco de dados
        $token = ApiToken::where('token', $tokenValue)->first();

        if (!$token) {
            return null;
        }

        // Verificar se o token está ativo
        if (!$token->is_active) {
            return null;
        }

        // Verificar se a integração está ativa
        if (!$token->integration || !$token->integration->is_active) {
            return null;
        }

        return $token;
    }
}
