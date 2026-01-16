<?php

namespace App\Http\Controllers;

use App\Models\NotificationHistory;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : null;
        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : null;

        $notifications = Score::with([
            'seller:id,name',
            'scoreRule:id,ocorrencia,description',
        ])
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('notifications.index', compact('notifications', 'startDate', 'endDate'));
    }

    /**
     * Retorna leituras de voz recentes para o navegador.
     */
    public function voiceRecent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'since' => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $limit = $validated['limit'] ?? 10;
        $since = isset($validated['since']) ? Carbon::parse($validated['since']) : null;

        $query = NotificationHistory::where('type', 'voice_ranking')
            ->orderBy('created_at', 'desc');

        if ($since) {
            $query->where('created_at', '>', $since);
        }

        $items = $query->limit($limit)->get()->values();

        return response()->json([
            'data' => $items->map(function (NotificationHistory $history) {
                return [
                    'id' => $history->id,
                    'scope' => $history->scope,
                    'content' => $history->content,
                    'created_at' => $history->created_at?->toIso8601String(),
                ];
            }),
        ]);
    }
}
