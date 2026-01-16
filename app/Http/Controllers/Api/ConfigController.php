<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Config::class);
        
        $configs = Config::all();
        return response()->json($configs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Config::class);
        
        $validated = $request->validate([
            'key' => 'required|string|unique:configs,key',
            'value' => 'nullable|string',
        ]);

        $config = Config::create($validated);
        return response()->json($config, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $config = Config::findOrFail($id);
        $this->authorize('view', $config);
        
        return response()->json($config);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $config = Config::findOrFail($id);
        $this->authorize('update', $config);

        $validated = $request->validate([
            'key' => 'sometimes|string|unique:configs,key,' . $id,
            'value' => 'sometimes|string',
        ]);

        $config->update($validated);
        return response()->json($config);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $config = Config::findOrFail($id);
        $this->authorize('delete', $config);
        
        $config->delete();

        return response()->json(['message' => 'Config deletada com sucesso']);
    }
}
