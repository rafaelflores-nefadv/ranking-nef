<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Sector;
use App\Models\Team;
use App\Models\User;
use App\Services\SectorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $users = User::whereIn('role', ['admin', 'supervisor', 'user'])
            ->with('sector')
            ->orderBy('name')
            ->get();

        return view('users.index', compact('users'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $teams = Team::orderBy('name')->get();
        $sectors = Sector::orderBy('name')->get();

        return view('users.create', compact('teams', 'sectors'));
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $sectorId = $validated['sector_id'] ?? app(SectorService::class)->getDefaultSectorId();

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ];

        if ($validated['role'] !== 'admin') {
            $userData['sector_id'] = $sectorId;
        }
        
        // Processar upload de avatar
        if ($request->hasFile('avatar')) {
            $userData['avatar'] = $this->storeAvatar($request->file('avatar'));
        } elseif ($request->filled('avatar_base64')) {
            $userData['avatar'] = $this->storeAvatarFromBase64($request->input('avatar_base64'));
        }

        $user = User::create($userData);

        // Se for supervisor, associar as equipes
        if ($validated['role'] === 'supervisor' && isset($validated['teams'])) {
            $user->teams()->sync($validated['teams']);
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    public function show(Request $request, User $user)
    {
        $authUser = $request->user();
        
        if (!$authUser || $authUser->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $user->load('teams', 'sector');

        return view('users.show', compact('user'));
    }

    public function edit(Request $request, User $user)
    {
        $authUser = $request->user();
        
        if (!$authUser || $authUser->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $teams = Team::orderBy('name')->get();
        $sectors = Sector::orderBy('name')->get();
        $user->load('teams');

        return view('users.edit', compact('user', 'teams', 'sectors'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $sectorId = $validated['sector_id'] ?? app(SectorService::class)->getDefaultSectorId();
        $user->sector_id = $validated['role'] === 'admin' ? null : $sectorId;
        
        // Processar upload de avatar
        if ($request->hasFile('avatar')) {
            // Remover avatar antigo se existir
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $this->storeAvatar($request->file('avatar'));
        } elseif ($request->filled('avatar_base64')) {
            // Remover avatar antigo se existir
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $this->storeAvatarFromBase64($request->input('avatar_base64'));
        }
        
        $user->save();

        // Atualizar equipes do supervisor
        if ($validated['role'] === 'supervisor') {
            // Sincroniza as equipes (a validação garante que sempre terá pelo menos uma)
            if (isset($validated['teams'])) {
                $user->teams()->sync($validated['teams']);
            }
        } else {
            // Se mudou de supervisor para admin, remover todas as equipes
            $user->teams()->sync([]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
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

    public function destroy(Request $request, User $user)
    {
        $authUser = $request->user();
        
        if (!$authUser || $authUser->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        // Não permitir deletar o próprio usuário
        if ($user->id === auth()->id()) {
            return back()
                ->withErrors(['error' => 'Você não pode excluir seu próprio usuário.']);
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuário excluído com sucesso!');
    }

    public function toggleStatus(Request $request, User $user)
    {
        $authUser = $request->user();
        
        if (!$authUser || $authUser->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        // Não permitir desativar o próprio usuário
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->withErrors(['error' => 'Você não pode desativar seu próprio usuário.']);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'ativado' : 'desativado';
        
        return redirect()->route('users.index')
            ->with('success', "Usuário {$status} com sucesso!");
    }

    public function showResetPassword(Request $request, User $user)
    {
        $authUser = $request->user();
        
        if (!$authUser || $authUser->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        return view('users.reset-password', compact('user'));
    }

    public function resetPassword(ResetPasswordRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'Senha redefinida com sucesso!');
    }
}
