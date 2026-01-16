<?php

namespace App\Http\Controllers;

use App\Models\Score;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ScoreController extends Controller
{
    /**
     * Retorna vendas recentes para notificação em tempo real.
     */
    public function recent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'since' => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $limit = $validated['limit'] ?? 10;
        $since = isset($validated['since']) ? Carbon::parse($validated['since']) : null;

        $query = Score::with([
            'seller:id,name',
            'scoreRule:id,ocorrencia,description',
        ])->orderBy('created_at', 'desc');

        if ($since) {
            $query->where('created_at', '>', $since);
        }

        $scores = $query->limit($limit)->get()->values();

        return response()->json([
            'data' => $scores->map(function (Score $score) {
                return [
                    'id' => $score->id,
                    'created_at' => $score->created_at?->toIso8601String(),
                    'points' => (float) $score->points,
                    'seller' => [
                        'id' => $score->seller?->id,
                        'name' => $score->seller?->name,
                    ],
                    'occurrence' => [
                        'id' => $score->scoreRule?->id,
                        'type' => $score->scoreRule?->ocorrencia,
                        'description' => $score->scoreRule?->description,
                    ],
                ];
            }),
        ]);
    }
}
