<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Team::class);

        $teams = Team::withCount('sellers')
            ->orderBy('name')
            ->paginate(20);

        return view('teams.index', compact('teams'));
    }

    public function create()
    {
        $this->authorize('create', Team::class);

        return view('teams.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Team::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name',
        ]);

        Team::create($validated);

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

    public function update(Request $request, Team $team)
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name,' . $team->id,
        ]);

        $team->update($validated);

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
