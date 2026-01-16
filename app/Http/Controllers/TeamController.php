<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
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

    public function edit(Team $team)
    {
        $this->authorize('update', $team);

        return view('teams.edit', compact('team'));
    }

    public function update(UpdateTeamRequest $request, Team $team)
    {
        $team->update($request->validated());

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
