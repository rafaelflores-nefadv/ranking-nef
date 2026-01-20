<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use App\Models\Seller;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Team::class);

        $user = $request->user();
        $allowedTeamIds = $user->getSupervisedTeamIds();

        $teamsQuery = Team::withCount('sellers');
        
        // Filtrar equipes baseado no papel do usuário
        if ($allowedTeamIds !== null) {
            $teamsQuery->whereIn('id', $allowedTeamIds);
        }
        
        $teams = $teamsQuery->orderBy('name')->paginate(20);

        return view('teams.index', compact('teams'));
    }

    public function create()
    {
        $this->authorize('create', Team::class);

        return view('teams.create');
    }

    public function store(StoreTeamRequest $request)
    {
        Team::create($request->validated());

        return redirect()->route('teams.index')
            ->with('success', 'Equipe criada com sucesso!');
    }

    public function show(Team $team)
    {
        $this->authorize('view', $team);

        $team->load('sellers');

        return view('teams.show', compact('team'));
    }

    public function edit(Request $request, Team $team)
    {
        $this->authorize('update', $team);

        $user = $request->user();
        $allowedTeamIds = $user->getSupervisedTeamIds();

        // Carregar vendedores disponíveis (respeitando permissões)
        $sellersQuery = Seller::with('team')->orderBy('name');
        
        // Filtrar vendedores baseado nas equipes do supervisor
        if ($allowedTeamIds !== null) {
            // Incluir vendedores das equipes permitidas, sem equipe, ou da equipe atual
            $sellersQuery->where(function($query) use ($allowedTeamIds, $team) {
                $query->whereIn('team_id', $allowedTeamIds)
                      ->orWhereNull('team_id')
                      ->orWhere('team_id', $team->id); // Sempre incluir vendedores da equipe atual
            });
        }
        
        $sellers = $sellersQuery->get();
        
        // Carregar vendedores atuais da equipe
        $team->load('sellers');
        $currentSellerIds = $team->sellers->pluck('id')->toArray();

        return view('teams.edit', compact('team', 'sellers', 'currentSellerIds'));
    }

    public function update(UpdateTeamRequest $request, Team $team)
    {
        $validated = $request->validated();
        
        // Atualizar nome da equipe
        $team->update(['name' => $validated['name']]);

        // Atualizar vendedores da equipe
        if (isset($validated['sellers'])) {
            // Atualizar team_id dos vendedores selecionados
            Seller::whereIn('id', $validated['sellers'])
                ->update(['team_id' => $team->id]);
            
            // Remover vendedores que não foram selecionados mas estavam na equipe
            Seller::where('team_id', $team->id)
                ->whereNotIn('id', $validated['sellers'])
                ->update(['team_id' => null]);
        } else {
            // Se nenhum vendedor foi selecionado, remover todos da equipe
            Seller::where('team_id', $team->id)
                ->update(['team_id' => null]);
        }

        return redirect()->route('teams.index')
            ->with('success', 'Equipe atualizada com sucesso!');
    }

    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);

        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Equipe excluída com sucesso!');
    }
}
