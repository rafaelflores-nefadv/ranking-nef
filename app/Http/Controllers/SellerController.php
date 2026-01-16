<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSellerRequest;
use App\Http\Requests\UpdateSellerRequest;
use App\Models\Seller;
use App\Models\Team;
use App\Models\Season;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Seller::class);

        $user = $request->user();
        $allowedTeamIds = $user->getSupervisedTeamIds();

        $sellersQuery = Seller::with(['team', 'season']);
        
        // Filtrar vendedores baseado nas equipes do supervisor
        if ($allowedTeamIds !== null) {
            $sellersQuery->whereIn('team_id', $allowedTeamIds);
        }
        
        $sellers = $sellersQuery
            ->orderBy('points', 'desc')
            ->paginate(20);

        // Filtrar equipes também
        $teamsQuery = Team::orderBy('name');
        if ($allowedTeamIds !== null) {
            $teamsQuery->whereIn('id', $allowedTeamIds);
        }
        $teams = $teamsQuery->get();
        
        $seasons = Season::all();

        return view('sellers.index', compact('sellers', 'teams', 'seasons'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Seller::class);

        $user = $request->user();
        $allowedTeamIds = $user->getSupervisedTeamIds();

        // Filtrar equipes baseado no papel do usuário
        $teamsQuery = Team::orderBy('name');
        if ($allowedTeamIds !== null) {
            $teamsQuery->whereIn('id', $allowedTeamIds);
        }
        $teams = $teamsQuery->get();
        
        $seasons = Season::all();

        return view('sellers.create', compact('teams', 'seasons'));
    }

    public function store(StoreSellerRequest $request)
    {
        Seller::create($request->validated());

        return redirect()->route('sellers.index')
            ->with('success', 'Vendedor criado com sucesso!');
    }

    public function show(Seller $seller)
    {
        $this->authorize('view', $seller);

        $seller->load(['team', 'season']);

        return view('sellers.show', compact('seller'));
    }

    public function edit(Request $request, Seller $seller)
    {
        $this->authorize('update', $seller);

        $user = $request->user();
        $allowedTeamIds = $user->getSupervisedTeamIds();

        // Filtrar equipes baseado no papel do usuário
        $teamsQuery = Team::orderBy('name');
        if ($allowedTeamIds !== null) {
            $teamsQuery->whereIn('id', $allowedTeamIds);
        }
        $teams = $teamsQuery->get();
        
        $seasons = Season::all();

        return view('sellers.edit', compact('seller', 'teams', 'seasons'));
    }

    public function update(UpdateSellerRequest $request, Seller $seller)
    {
        $seller->update($request->validated());

        return redirect()->route('sellers.index')
            ->with('success', 'Colaborador atualizado com sucesso!');
    }

    public function destroy(Seller $seller)
    {
        $this->authorize('delete', $seller);

        $seller->delete();

        return redirect()->route('sellers.index')
            ->with('success', 'Colaborador excluído com sucesso!');
    }
}
