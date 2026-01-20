<?php

namespace App\Http\Controllers\Goals;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGoalRequest;
use App\Http\Requests\UpdateGoalRequest;
use App\Models\Goal;
use App\Models\Team;
use App\Models\Season;
use App\Models\Seller;
use App\Services\GoalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoalController extends Controller
{
    public function __construct(
        private GoalService $goalService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Goal::class);

        $user = $request->user();
        $allowedTeamIds = $user->getSupervisedTeamIds();

        $goalsQuery = Goal::with(['season', 'team', 'seller']);
        
        // Filtrar metas baseado no papel do usuário
        if ($allowedTeamIds !== null) {
            // Supervisor: ver apenas metas globais, das suas equipes ou dos vendedores das suas equipes
            $goalsQuery->where(function ($query) use ($allowedTeamIds) {
                $query->where('scope', 'global')
                    ->orWhere(function ($q) use ($allowedTeamIds) {
                        $q->where('scope', 'team')
                            ->whereIn('team_id', $allowedTeamIds);
                    })
                    ->orWhereHas('seller', function ($q) use ($allowedTeamIds) {
                        $q->whereIn('team_id', $allowedTeamIds);
                    });
            });
        }
        
        // Filtro por temporada
        if ($request->has('season_id') && $request->season_id) {
            $goalsQuery->where('season_id', $request->season_id);
        }
        
        // Filtro por escopo
        if ($request->has('scope') && $request->scope) {
            $goalsQuery->where('scope', $request->scope);
        }

        $goals = $goalsQuery
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Calcular progresso para cada meta
        $goals->getCollection()->transform(function ($goal) {
            $progress = $this->goalService->calculateProgress($goal);
            $goal->progress_data = $progress;
            return $goal;
        });

        $seasons = Season::all();
        $teamsQuery = Team::orderBy('name');
        if ($allowedTeamIds !== null) {
            $teamsQuery->whereIn('id', $allowedTeamIds);
        }
        $teams = $teamsQuery->get();

        return view('goals.index', compact('goals', 'seasons', 'teams'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Goal::class);

        $user = $request->user();
        $allowedTeamIds = $user->getSupervisedTeamIds();

        // Filtrar equipes baseado no papel do usuário
        $teamsQuery = Team::orderBy('name');
        if ($allowedTeamIds !== null) {
            $teamsQuery->whereIn('id', $allowedTeamIds);
        }
        $teams = $teamsQuery->get();
        
        $seasons = Season::all();
        
        // Filtrar vendedores baseado nas equipes permitidas
        $sellersQuery = Seller::with(['team'])->orderBy('name');
        if ($allowedTeamIds !== null) {
            $sellersQuery->whereIn('team_id', $allowedTeamIds);
        }
        $sellers = $sellersQuery->get();

        return view('goals.create', compact('teams', 'seasons', 'sellers'));
    }

    public function store(StoreGoalRequest $request)
    {
        $validated = $request->validated();
        
        // Criar meta única
        if ($request->scope !== 'team' || !$request->has('create_for_all_team_sellers')) {
            Goal::create($validated);
            
            return redirect()->route('goals.index')
                ->with('success', 'Meta criada com sucesso!');
        }
        
        // Criar metas em massa para todos os vendedores da equipe
        if ($validated['scope'] === 'team' && $request->has('create_for_all_team_sellers')) {
            $teamId = $validated['team_id'];
            $sellers = Seller::where('team_id', $teamId)->get();
            
            if ($sellers->isEmpty()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'A equipe selecionada não possui vendedores.');
            }
            
            DB::transaction(function () use ($validated, $sellers) {
                foreach ($sellers as $seller) {
                    Goal::create([
                        'scope' => 'seller',
                        'season_id' => $validated['season_id'],
                        'team_id' => null,
                        'seller_id' => $seller->id,
                        'name' => $validated['name'] . ' - ' . $seller->name,
                        'description' => $validated['description'],
                        'target_value' => $validated['target_value'],
                        'starts_at' => $validated['starts_at'],
                        'ends_at' => $validated['ends_at'],
                    ]);
                }
            });
            
            return redirect()->route('goals.index')
                ->with('success', "Metas criadas com sucesso para {$sellers->count()} vendedores!");
        }
        
        return redirect()->route('goals.index')
            ->with('success', 'Meta criada com sucesso!');
    }

    public function show(Goal $goal)
    {
        $this->authorize('view', $goal);

        $goal->load(['season', 'team', 'seller']);
        $progress = $this->goalService->calculateProgress($goal);

        return view('goals.show', compact('goal', 'progress'));
    }

    public function edit(Request $request, Goal $goal)
    {
        $this->authorize('update', $goal);

        $user = $request->user();
        $allowedTeamIds = $user->getSupervisedTeamIds();

        // Filtrar equipes baseado no papel do usuário
        $teamsQuery = Team::orderBy('name');
        if ($allowedTeamIds !== null) {
            $teamsQuery->whereIn('id', $allowedTeamIds);
        }
        $teams = $teamsQuery->get();
        
        $seasons = Season::all();
        
        // Filtrar vendedores baseado nas equipes permitidas
        $sellersQuery = Seller::with(['team'])->orderBy('name');
        if ($allowedTeamIds !== null) {
            $sellersQuery->whereIn('team_id', $allowedTeamIds);
        }
        $sellers = $sellersQuery->get();

        $goal->load(['season', 'team', 'seller']);

        return view('goals.edit', compact('goal', 'teams', 'seasons', 'sellers'));
    }

    public function update(UpdateGoalRequest $request, Goal $goal)
    {
        $goal->update($request->validated());

        return redirect()->route('goals.index')
            ->with('success', 'Meta atualizada com sucesso!');
    }

    public function destroy(Goal $goal)
    {
        $this->authorize('delete', $goal);

        $goal->delete();

        return redirect()->route('goals.index')
            ->with('success', 'Meta excluída com sucesso!');
    }

    /**
     * Duplica uma meta para uma nova temporada
     */
    public function duplicate(Request $request, Goal $goal)
    {
        $this->authorize('create', Goal::class);

        $request->validate([
            'season_id' => 'required|uuid|exists:seasons,id',
        ]);

        $newGoal = $this->goalService->duplicateForSeason($goal, $request->season_id);

        return redirect()->route('goals.index')
            ->with('success', 'Meta duplicada com sucesso!');
    }
}
