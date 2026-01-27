<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessApiOccurrencesJob;
use App\Models\ApiOccurrence;
use App\Models\ApiToken;
use App\Models\ScoreRule;
use App\Models\Seller;
use App\Models\Team;
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
        $tokenResult = $this->validateToken($request);

        if ($tokenResult instanceof JsonResponse) {
            return $tokenResult;
        }

        $token = $tokenResult;

        // Validar dados da requisição
        $rules = [
            'email_funcionario' => 'required|string',
            'ocorrencia' => 'required|string',
            'credor' => 'nullable|string',
            'equipe' => 'nullable|string',
        ];

        if ($token->collaborator_identifier_type === 'email') {
            $rules['email_funcionario'] = 'required|string|email';
        }

        $validated = $request->validate($rules);

        $seller = $this->resolveSeller($token, $validated['email_funcionario']);
        if (!$seller) {
            return response()->json([
                'message' => 'Vendedor não encontrado no setor',
                'error' => 'Unprocessable Entity',
            ], 422);
        }

        $team = null;
        if (!empty($validated['equipe'])) {
            $team = Team::where('sector_id', $token->sector_id)
                ->where('name', $validated['equipe'])
                ->first();

            if (!$team) {
                return response()->json([
                    'message' => 'Equipe fora do setor',
                    'error' => 'Unprocessable Entity',
                ], 422);
            }

            $belongsToTeam = $seller->teams()->where('teams.id', $team->id)->exists();
            if (!$belongsToTeam) {
                return response()->json([
                    'message' => 'Equipe fora do setor',
                    'error' => 'Unprocessable Entity',
                ], 422);
            }
        }

        $scoreRule = ScoreRule::where('sector_id', $token->sector_id)
            ->where('ocorrencia', $validated['ocorrencia'])
            ->where('is_active', true)
            ->first();

        if (!$scoreRule) {
            return response()->json([
                'message' => 'Regra inexistente no setor',
                'error' => 'Unprocessable Entity',
            ], 422);
        }

        // Atualizar último uso do token
        $token->update([
            'last_used_at' => now(),
        ]);

        $occurrence = ApiOccurrence::create([
            'sector_id' => $token->sector_id,
            'api_token_id' => $token->id,
            'collaborator_identifier_type' => $token->collaborator_identifier_type,
            'email_funcionario' => $validated['email_funcionario'],
            'ocorrencia' => $validated['ocorrencia'],
            'credor' => $validated['credor'] ?? null,
            'equipe' => $validated['equipe'] ?? null,
        ]);

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
    private function validateToken(Request $request): ApiToken|JsonResponse
    {
        $authorization = $request->header('Authorization');
        
        if (!$authorization) {
            return response()->json([
                'message' => 'Token inválido',
                'error' => 'Unauthorized',
            ], 401);
        }

        // Extrair token do formato "Bearer {token}"
        if (preg_match('/Bearer\s+(.+)$/i', $authorization, $matches)) {
            $tokenValue = trim($matches[1]);
        } else {
            // Tentar usar o valor diretamente se não tiver "Bearer"
            $tokenValue = trim($authorization);
        }

        if (empty($tokenValue)) {
            return response()->json([
                'message' => 'Token inválido',
                'error' => 'Unauthorized',
            ], 401);
        }

        // Buscar token no banco de dados
        $token = ApiToken::where('token', $tokenValue)->first();

        if (!$token) {
            return response()->json([
                'message' => 'Token inválido',
                'error' => 'Unauthorized',
            ], 401);
        }

        // Verificar se o token está ativo
        if (!$token->is_active) {
            return response()->json([
                'message' => 'Token inativo',
                'error' => 'Forbidden',
            ], 403);
        }

        // Verificar se a integração está ativa
        if (!$token->integration || !$token->integration->is_active) {
            return response()->json([
                'message' => 'Token inativo',
                'error' => 'Forbidden',
            ], 403);
        }

        return $token;
    }

    private function resolveSeller(ApiToken $token, string $identifier): ?Seller
    {
        $query = Seller::query()->where('sector_id', $token->sector_id);

        if ($token->collaborator_identifier_type === 'external_code') {
            return $query->where('external_code', $identifier)->first();
        }

        return $query->where('email', $identifier)->first();
    }
}
