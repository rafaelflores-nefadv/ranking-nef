<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSellerRequest;
use App\Http\Requests\UpdateSellerRequest;
use App\Imports\SellersImport;
use App\Models\Seller;
use App\Models\Sector;
use App\Models\Team;
use App\Models\Season;
use App\Services\SectorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SellerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Seller::class);

        $user = $request->user();
        $allowedTeamIds = $user->getSupervisedTeamIds();
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);

        $sellersQuery = Seller::with(['teams', 'season']);
        if ($sectorId) {
            $sellersQuery->where('sector_id', $sectorId);
        }
        
        // Filtrar vendedores baseado nas equipes do supervisor
        if ($allowedTeamIds !== null) {
            $sellersQuery->whereHas('teams', function($query) use ($allowedTeamIds) {
                $query->whereIn('teams.id', $allowedTeamIds);
            });
        }
        
        $sellers = $sellersQuery
            ->orderBy('points', 'desc')
            ->paginate(20);

        // Filtrar equipes também
        $teamsQuery = Team::orderBy('name');
        if ($user->role !== 'admin' && $sectorId) {
            $teamsQuery->where('sector_id', $sectorId);
        }
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
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);

        // Filtrar equipes baseado no papel do usuário
        $teamsQuery = Team::withCount('sellers')->orderBy('name');
        if ($user->role !== 'admin' && $sectorId) {
            $teamsQuery->where('sector_id', $sectorId);
        }
        if ($allowedTeamIds !== null) {
            $teamsQuery->whereIn('id', $allowedTeamIds);
        }
        $teams = $teamsQuery->get();
        
        $seasons = Season::all();

        $sectors = $user->role === 'admin'
            ? Sector::orderBy('name')->get(['id', 'name'])
            : collect();

        return view('sellers.create', compact('teams', 'seasons', 'sectors'));
    }

    public function store(StoreSellerRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();
        $sectorId = $user->role === 'admin'
            ? $request->input('sector_id')
            : $user->sector_id;
        
        // Separar equipes dos outros dados
        $teams = $data['teams'] ?? [];
        unset($data['teams']);
        
        // Processar upload de avatar
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->storeAvatar($request->file('avatar'));
        } elseif ($request->filled('avatar_base64')) {
            $data['avatar'] = $this->storeAvatarFromBase64($request->input('avatar_base64'));
        }
        
        $data['sector_id'] = $sectorId;
        $seller = Seller::create($data);
        
        // Associar equipes
        if (!empty($teams)) {
            $seller->teams()->sync($teams);
        }

        return redirect()->route('sellers.index')
            ->with('success', 'Vendedor criado com sucesso!');
    }

    public function show(Seller $seller)
    {
        $this->authorize('view', $seller);

        $seller->load(['teams', 'season']);

        return view('sellers.show', compact('seller'));
    }

    public function edit(Request $request, Seller $seller)
    {
        $this->authorize('update', $seller);

        $user = $request->user();
        $allowedTeamIds = $user->getSupervisedTeamIds();
        $sectorId = $user->role === 'admin'
            ? ($request->input('sector_id') ?: $seller->sector_id)
            : $user->sector_id;

        // Filtrar equipes baseado no papel do usuário
        $teamsQuery = Team::withCount('sellers')->orderBy('name');
        if ($user->role !== 'admin' && $sectorId) {
            $teamsQuery->where('sector_id', $sectorId);
        }
        if ($allowedTeamIds !== null) {
            $teamsQuery->whereIn('id', $allowedTeamIds);
        }
        $teams = $teamsQuery->get();
        
        // Carregar equipes atuais do vendedor
        $seller->load('teams');
        $currentTeamIds = $seller->teams->pluck('id')->toArray();
        
        $seasons = Season::all();

        $sectors = $user->role === 'admin'
            ? Sector::orderBy('name')->get(['id', 'name'])
            : collect();

        return view('sellers.edit', compact('seller', 'teams', 'seasons', 'currentTeamIds', 'sectors'));
    }

    public function update(UpdateSellerRequest $request, Seller $seller)
    {
        $data = $request->validated();
        $user = $request->user();
        $sectorId = $user->role === 'admin'
            ? $request->input('sector_id')
            : $user->sector_id;
        $data['sector_id'] = $sectorId;
        
        // Separar equipes dos outros dados
        $teams = $data['teams'] ?? [];
        unset($data['teams']);
        
        // Processar upload de avatar
        if ($request->hasFile('avatar')) {
            // Remover avatar antigo se existir
            if ($seller->avatar) {
                Storage::disk('public')->delete($seller->avatar);
            }
            $data['avatar'] = $this->storeAvatar($request->file('avatar'));
        } elseif ($request->filled('avatar_base64')) {
            // Remover avatar antigo se existir
            if ($seller->avatar) {
                Storage::disk('public')->delete($seller->avatar);
            }
            $data['avatar'] = $this->storeAvatarFromBase64($request->input('avatar_base64'));
        }
        
        $seller->update($data);
        
        // Sincronizar equipes
        $seller->teams()->sync($teams);

        return redirect()->route('sellers.index')
            ->with('success', 'Colaborador atualizado com sucesso!');
    }
    
    /**
     * Armazenar avatar de arquivo
     */
    private function storeAvatar($file): string
    {
        $path = $file->store('avatars', 'public');
        return $path;
    }
    
    /**
     * Armazenar avatar de base64 (webcam)
     */
    private function storeAvatarFromBase64($base64): string
    {
        // Remover prefixo data:image/...;base64,
        $image = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $image = base64_decode($image);
        
        // Gerar nome único
        $filename = 'avatars/' . uniqid() . '_' . time() . '.jpg';
        
        // Garantir que o diretório existe
        Storage::disk('public')->makeDirectory('avatars');
        
        // Salvar arquivo
        Storage::disk('public')->put($filename, $image);
        
        return $filename;
    }

    public function destroy(Seller $seller)
    {
        $this->authorize('delete', $seller);

        $seller->delete();

        return redirect()->route('sellers.index')
            ->with('success', 'Colaborador excluído com sucesso!');
    }

    public function import()
    {
        $this->authorize('create', Seller::class);

        return view('sellers.import');
    }

    public function processImport(Request $request)
    {
        $this->authorize('create', Seller::class);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240', // 10MB max
        ]);

        try {
            $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);
            $import = new SellersImport($sectorId);
            
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $skipped = $import->getSkippedCount();
            $errors = $import->getErrors();

            $message = "Importação concluída! {$imported} vendedor(es) importado(s) com sucesso.";
            
            if ($skipped > 0) {
                $message .= " {$skipped} registro(s) foram ignorados.";
            }

            if (!empty($errors)) {
                $message .= " Erros: " . implode(', ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= " e mais " . (count($errors) - 5) . " erro(s).";
                }
            }

            return redirect()->route('sellers.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('sellers.import')
                ->with('error', 'Erro ao importar arquivo: ' . $e->getMessage());
        }
    }
}
