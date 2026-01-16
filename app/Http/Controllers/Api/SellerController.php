<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Seller::class);
        
        $sellers = Seller::with(['team', 'season'])->get();
        return response()->json($sellers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Seller::class);
        
        $validated = $request->validate([
            'team_id' => 'nullable|uuid|exists:teams,id',
            'season_id' => 'nullable|uuid|exists:seasons,id',
            'name' => 'required|string',
            'email' => 'required|string|email|unique:sellers,email',
            'points' => 'nullable|numeric',
            'status' => 'nullable|in:active,inactive',
        ]);

        $seller = Seller::create($validated);
        $seller->load(['team', 'season']);

        return response()->json($seller, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $seller = Seller::with(['team', 'season', 'scores'])->findOrFail($id);
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

        $validated = $request->validate([
            'team_id' => 'nullable|uuid|exists:teams,id',
            'season_id' => 'nullable|uuid|exists:seasons,id',
            'name' => 'sometimes|string',
            'email' => 'sometimes|string|email|unique:sellers,email,' . $id,
            'points' => 'sometimes|numeric',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $seller->update($validated);
        $seller->load(['team', 'season']);

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
