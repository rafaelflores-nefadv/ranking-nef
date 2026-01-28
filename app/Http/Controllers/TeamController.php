<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Sector;
use App\Models\Team;
use App\Models\Seller;
use App\Services\SectorService;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Team::class);

        $user = $request->user();
        $allowedTeamIds = $user->getSupervisedTeamIds();
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);

        $teamsQuery = Team::withCount('sellers');
        if ($sectorId) {
            $teamsQuery->where('sector_id', $sectorId);
        }
        
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

        $user = request()->user();
        $sectors = $user && $user->role === 'admin'
            ? Sector::where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('teams.create', compact('sectors'));
    }

    public function store(StoreTeamRequest $request)
    {
        $user = $request->user();
        $sectorId = $user->role === 'admin'
            ? $request->input('sector_id')
            : $user->sector_id;

        Team::create(array_merge(
            $request->validated(),
            ['sector_id' => $sectorId]
        ));

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
        $sectorId = $user->role === 'admin'
            ? $team->sector_id
            : $user->sector_id;

        // Carregar vendedores disponíveis (respeitando permissões)
        $sellersQuery = Seller::with('teams')->orderBy('name');
        if ($sectorId) {
            $sellersQuery->where('sector_id', $sectorId);
        }
        
        // Filtrar vendedores baseado nas equipes do supervisor
        if ($allowedTeamIds !== null) {
            // Incluir vendedores das equipes permitidas ou da equipe atual
            $sellersQuery->whereHas('teams', function($query) use ($allowedTeamIds, $team) {
                $query->whereIn('teams.id', $allowedTeamIds)
                      ->orWhere('teams.id', $team->id); // Sempre incluir vendedores da equipe atual
            });
        }
        
        $sellers = $sellersQuery->get();
        
        // Carregar vendedores atuais da equipe
        $team->load('sellers');
        $currentSellerIds = $team->sellers->pluck('id')->toArray();

        $sectors = collect();
        if ($user->role === 'admin') {
            $sectors = Sector::where('is_active', true)
                ->orWhere('id', $team->sector_id)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return view('teams.edit', compact('team', 'sellers', 'currentSellerIds', 'sectors'));
    }

    public function update(UpdateTeamRequest $request, Team $team)
    {
        $validated = $request->validated();
        
        // Atualizar nome da equipe
        $team->update([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'] ?? null,
        ]);

        // Atualizar vendedores da equipe
        if (isset($validated['sellers'])) {
            // Sincronizar vendedores com a equipe usando relação many-to-many
            $team->sellers()->sync($validated['sellers']);
        } else {
            // Se nenhum vendedor foi selecionado, remover todos da equipe
            $team->sellers()->detach();
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
