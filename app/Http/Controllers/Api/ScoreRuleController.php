<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScoreRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScoreRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', ScoreRule::class);
        
        $scoreRules = ScoreRule::all();
        return response()->json($scoreRules);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', ScoreRule::class);
        
        $validated = $request->validate([
            'ocorrencia' => 'required|string',
            'points' => 'required|numeric',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $scoreRule = ScoreRule::create($validated);
        return response()->json($scoreRule, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $scoreRule = ScoreRule::findOrFail($id);
        $this->authorize('view', $scoreRule);
        
        return response()->json($scoreRule);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $scoreRule = ScoreRule::findOrFail($id);
        $this->authorize('update', $scoreRule);

        $validated = $request->validate([
            'ocorrencia' => 'sometimes|string',
            'points' => 'sometimes|numeric',
            'description' => 'sometimes|string',
            'priority' => 'sometimes|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        $scoreRule->update($validated);
        return response()->json($scoreRule);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $scoreRule = ScoreRule::findOrFail($id);
        $this->authorize('delete', $scoreRule);
        
        $scoreRule->delete();

        return response()->json(['message' => 'ScoreRule deletada com sucesso']);
    }
}
