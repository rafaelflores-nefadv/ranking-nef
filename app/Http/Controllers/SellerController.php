<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Team;
use App\Models\Season;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Seller::class);

        $sellers = Seller::with(['team', 'season'])
            ->orderBy('points', 'desc')
            ->paginate(20);

        $teams = Team::all();
        $seasons = Season::all();

        return view('sellers.index', compact('sellers', 'teams', 'seasons'));
    }

    public function create()
    {
        $this->authorize('create', Seller::class);

        $teams = Team::all();
        $seasons = Season::all();

        return view('sellers.create', compact('teams', 'seasons'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Seller::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:sellers,email',
            'team_id' => 'nullable|exists:teams,id',
            'season_id' => 'nullable|exists:seasons,id',
            'status' => 'required|in:active,inactive',
        ]);

        Seller::create($validated);

        return redirect()->route('sellers.index')
            ->with('success', 'Vendedor criado com sucesso!');
    }

    public function show(Seller $seller)
    {
        $this->authorize('view', $seller);

        $seller->load(['team', 'season']);

        return view('sellers.show', compact('seller'));
    }

    public function edit(Seller $seller)
    {
        $this->authorize('update', $seller);

        $teams = Team::all();
        $seasons = Season::all();

        return view('sellers.edit', compact('seller', 'teams', 'seasons'));
    }

    public function update(Request $request, Seller $seller)
    {
        $this->authorize('update', $seller);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:sellers,email,' . $seller->id,
            'team_id' => 'nullable|exists:teams,id',
            'season_id' => 'nullable|exists:seasons,id',
            'status' => 'required|in:active,inactive',
        ]);

        $seller->update($validated);

        return redirect()->route('sellers.index')
            ->with('success', 'Vendedor atualizado com sucesso!');
    }

    public function destroy(Seller $seller)
    {
        $this->authorize('delete', $seller);

        $seller->delete();

        return redirect()->route('sellers.index')
            ->with('success', 'Vendedor excluído com sucesso!');
    }
}
