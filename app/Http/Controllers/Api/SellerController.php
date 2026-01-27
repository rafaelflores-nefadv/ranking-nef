<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Services\SectorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SellerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Seller::class);
        
        $user = $request->user();
        $allowedTeamIds = $user->getSupervisedTeamIds();
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);
        
        $sellersQuery = Seller::with(['teams', 'season']);
        if ($sectorId) {
            $sellersQuery->where('sector_id', $sectorId);
        }
        
        // Filtrar vendedores baseado nas equipes do supervisor
        if ($allowedTeamIds !== null) {
            $sellersQuery->whereHas('teams', function ($q) use ($allowedTeamIds) {
                $q->whereIn('teams.id', $allowedTeamIds);
            });
        }
        
        $sellers = $sellersQuery->get();
        return response()->json($sellers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Seller::class);
        
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);
        $validated = $request->validate([
            'team_id' => [
                'nullable',
                'uuid',
                Rule::exists('teams', 'id')->where('sector_id', $sectorId),
            ],
            'season_id' => 'nullable|uuid|exists:seasons,id',
            'name' => 'required|string',
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique('sellers')->where('sector_id', $sectorId),
            ],
            'points' => 'nullable|numeric',
            'status' => 'nullable|in:active,inactive',
        ]);

        $seller = Seller::create(array_merge(
            $validated,
            ['sector_id' => $sectorId]
        ));
        if (!empty($validated['team_id'])) {
            $seller->teams()->sync([$validated['team_id']]);
        }
        $seller->load(['teams', 'season']);

        return response()->json($seller, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $seller = Seller::with(['teams', 'season', 'scores'])->findOrFail($id);
        $this->authorize('view', $seller);
        
        return response()->json($seller);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $seller = Seller::findOrFail($id);
        $this->authorize('update', $seller);

        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);
        $validated = $request->validate([
            'team_id' => [
                'nullable',
                'uuid',
                Rule::exists('teams', 'id')->where('sector_id', $sectorId),
            ],
            'season_id' => 'nullable|uuid|exists:seasons,id',
            'name' => 'sometimes|string',
            'email' => [
                'sometimes',
                'string',
                'email',
                Rule::unique('sellers')->where('sector_id', $sectorId)->ignore($id),
            ],
            'points' => 'sometimes|numeric',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $seller->update($validated);
        if (array_key_exists('team_id', $validated)) {
            $seller->teams()->sync($validated['team_id'] ? [$validated['team_id']] : []);
        }
        $seller->load(['teams', 'season']);

        return response()->json($seller);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $seller = Seller::findOrFail($id);
        $this->authorize('delete', $seller);
        
        $seller->delete();

        return response()->json(['message' => 'Seller deletado com sucesso']);
    }
}
