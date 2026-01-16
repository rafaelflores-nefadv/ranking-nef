<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Seller;
use App\Models\Team;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        private GamificationService $gamificationService
    ) {}

    private function buildDashboardData(?string $teamId): array
    {
        $teams = Team::orderBy('name')->get(['id', 'name']);
        $activeTeam = $teamId ? $teams->firstWhere('id', $teamId) : null;

        $sellersQuery = Seller::with(['team', 'season']);
        if ($teamId) {
            $sellersQuery->where('team_id', $teamId);
        }

        $sellers = $sellersQuery
            ->orderBy('points', 'desc')
            ->limit(100)
            ->get();

        $ranking = $sellers->map(function ($seller) {
            $gamification = $this->gamificationService->getGamificationInfo($seller->points);
            return [
                'id' => $seller->id,
                'name' => $seller->name,
                'email' => $seller->email,
                'points' => $seller->points,
                'level' => $gamification['level'],
                'badge' => $gamification['badge'],
                'progress' => $gamification['progress'],
                'position' => 0,
                'team' => $seller->team?->name,
                'season' => $seller->season?->name,
            ];
        })->values();

        $ranking = $ranking->map(function ($entry, $index) {
            $entry['position'] = $index + 1;
            return $entry;
        });

        $top3 = $ranking->take(3)->values();

        $stats = [
            'totalPoints' => $sellers->sum('points'),
            'totalParticipants' => $sellers->count(),
            'activeParticipants' => $sellers->where('status', 'active')->count(),
            'averagePoints' => $sellers->avg('points') ?? 0,
        ];

        $percentage = number_format((($stats['totalPoints'] ?? 0) / 500000) * 100, 2);

        return compact('teams', 'activeTeam', 'ranking', 'top3', 'stats', 'percentage');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $data = $this->buildDashboardData($request->query('team'));
        $configs = Config::all()->pluck('value', 'key');

        return view('dashboard', array_merge($data, ['configs' => $configs]));
    }

    public function data(Request $request)
    {
        $data = $this->buildDashboardData($request->query('team'));

        return response()->json([
            'activeTeamName' => $data['activeTeam']?->name,
            'rankingHtml' => view('dashboard.partials.ranking', [
                'ranking' => $data['ranking'],
                'activeTeam' => $data['activeTeam'],
            ])->render(),
            'podiumHtml' => view('dashboard.partials.podium', [
                'top3' => $data['top3'],
            ])->render(),
            'stats' => [
                'totalParticipants' => $data['stats']['totalParticipants'],
                'activeParticipants' => $data['stats']['activeParticipants'],
                'percentage' => $data['percentage'],
            ],
        ]);
    }
}
