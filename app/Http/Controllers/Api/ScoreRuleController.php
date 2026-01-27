<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScoreRule;
use App\Services\SectorService;
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
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest(request());
        $scoreRules = ScoreRule::where('sector_id', $sectorId)->get();
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
            'is_active' => 'nullable|boolean',
        ]);

        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);
        $scoreRule = ScoreRule::create(array_merge(
            $validated,
            ['sector_id' => $sectorId]
        ));
        return response()->json($scoreRule, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $scoreRule = ScoreRule::findOrFail($id);
        $this->authorize('view', $scoreRule);
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest(request());
        if ($scoreRule->sector_id !== $sectorId) {
            abort(403, 'Acesso negado');
        }
        
        return response()->json($scoreRule);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $scoreRule = ScoreRule::findOrFail($id);
        $this->authorize('update', $scoreRule);
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);
        if ($scoreRule->sector_id !== $sectorId) {
            abort(403, 'Acesso negado');
        }

        $validated = $request->validate([
            'ocorrencia' => 'sometimes|string',
            'points' => 'sometimes|numeric',
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
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest(request());
        if ($scoreRule->sector_id !== $sectorId) {
            abort(403, 'Acesso negado');
        }
        
        $scoreRule->delete();

        return response()->json(['message' => 'ScoreRule deletada com sucesso']);
    }
}
