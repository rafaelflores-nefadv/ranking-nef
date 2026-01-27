<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Services\SectorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Team::class);
        
        $user = $request->user();
        $allowedTeamIds = $user->getSupervisedTeamIds();
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);
        
        $teamsQuery = Team::with('sellers');
        if ($sectorId) {
            $teamsQuery->where('sector_id', $sectorId);
        }
        
        // Filtrar equipes baseado no papel do usuário
        if ($allowedTeamIds !== null) {
            $teamsQuery->whereIn('id', $allowedTeamIds);
        }
        
        $teams = $teamsQuery->get();
        return response()->json($teams);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Team::class);
        
        $validated = $request->validate([
            'name' => 'required|string',
        ]);

        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);
        $team = Team::create(array_merge($validated, ['sector_id' => $sectorId]));
        return response()->json($team, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $team = Team::with('sellers')->findOrFail($id);
        $this->authorize('view', $team);
        
        return response()->json($team);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $team = Team::findOrFail($id);
        $this->authorize('update', $team);

        $validated = $request->validate([
            'name' => 'sometimes|string',
        ]);

        $team->update($validated);
        return response()->json($team);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $team = Team::findOrFail($id);
        $this->authorize('delete', $team);
        
        $team->delete();

        return response()->json(['message' => 'Team deletado com sucesso']);
    }
}
