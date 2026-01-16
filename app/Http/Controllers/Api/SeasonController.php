<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Season;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Season::class);
        
        $seasons = Season::with('sellers')->get();
        return response()->json($seasons);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Season::class);
        
        $validated = $request->validate([
            'name' => 'required|string',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        $season = Season::create($validated);
        return response()->json($season, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $season = Season::with('sellers')->findOrFail($id);
        $this->authorize('view', $season);
        
        return response()->json($season);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $season = Season::findOrFail($id);
        $this->authorize('update', $season);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'starts_at' => 'sometimes|date',
            'ends_at' => 'sometimes|date|after:starts_at',
            'is_active' => 'sometimes|boolean',
        ]);

        $season->update($validated);
        return response()->json($season);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $season = Season::findOrFail($id);
        $this->authorize('delete', $season);
        
        $season->delete();

        return response()->json(['message' => 'Season deletada com sucesso']);
    }
}
