<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessApiOccurrencesJob;
use App\Models\ApiOccurrence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookOccurrenceController extends Controller
{
    /**
     * Recebe ocorrências via webhook
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email_funcionario' => 'required|string|email',
            'ocorrencia' => 'required|string',
            'credor' => 'nullable|string',
            'equipe' => 'nullable|string',
        ]);

        $occurrence = ApiOccurrence::create($validated);

        // Disparar job de processamento
        ProcessApiOccurrencesJob::dispatch();

        return response()->json([
            'message' => 'Ocorrência recebida com sucesso',
            'id' => $occurrence->id,
        ], 201);
    }
}
