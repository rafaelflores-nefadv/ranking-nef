<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ApiIntegration;
use App\Models\ApiToken;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ApiIntegrationController extends Controller
{
    /**
     * Display a listing of the integrations.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $integrations = ApiIntegration::with(['tokens', 'sector'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('settings.api.index', compact('integrations'));
    }

    /**
     * Show the form for creating a new integration.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $sectors = Sector::where('is_active', true)->orderBy('name')->get();

        return view('settings.api.create', compact('sectors'));
    }

    /**
     * Store a newly created integration.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $validated = $request->validate([
            'sector_id' => 'required|uuid|exists:sectors,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'system' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $integration = ApiIntegration::create([
            'sector_id' => $validated['sector_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'system' => $validated['system'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('settings.api.index')
            ->with('status', 'Integração criada com sucesso!');
    }

    /**
     * Show the form for editing the specified integration.
     */
    public function edit(Request $request, ApiIntegration $apiIntegration)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $apiIntegration->load('tokens.sector', 'sector');
        $sectors = Sector::where('is_active', true)->orderBy('name')->get();

        return view('settings.api.edit', compact('apiIntegration', 'sectors'));
    }

    /**
     * Update the specified integration.
     */
    public function update(Request $request, ApiIntegration $apiIntegration)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $validated = $request->validate([
            'sector_id' => ['required', 'uuid', Rule::exists('sectors', 'id')->where('is_active', true)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'system' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $apiIntegration->update([
            'sector_id' => $validated['sector_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'system' => $validated['system'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ApiToken::where('integration_id', $apiIntegration->id)
            ->update(['sector_id' => $validated['sector_id']]);

        return redirect()
            ->route('settings.api.index')
            ->with('status', 'Integração atualizada com sucesso!');
    }

    /**
     * Remove the specified integration.
     */
    public function destroy(Request $request, ApiIntegration $apiIntegration)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $apiIntegration->delete();

        return redirect()
            ->route('settings.api.index')
            ->with('status', 'Integração excluída com sucesso!');
    }

    /**
     * Generate a new token for the integration.
     */
    public function generateToken(Request $request, ApiIntegration $apiIntegration)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $validated = $request->validate([
            'collaborator_identifier_type' => 'required|in:email,external_code',
        ]);

        if (!$apiIntegration->sector_id) {
            return redirect()
                ->route('settings.api.edit', $apiIntegration)
                ->with('error', 'Defina o setor da integração antes de gerar tokens.');
        }

        $generated = ApiToken::generate();
        
        $token = new ApiToken();
        $token->integration_id = $apiIntegration->id;
        $token->sector_id = $apiIntegration->sector_id;
        $token->collaborator_identifier_type = $validated['collaborator_identifier_type'];
        $token->token = $generated['token'];
        $token->setSecretHashFromPlainSecret($generated['secret']);
        $token->is_active = true;
        $token->save();

        return redirect()
            ->route('settings.api.edit', $apiIntegration)
            ->with('token_generated', [
                'token' => $generated['token'],
                'secret' => $generated['secret'],
            ])
            ->with('status', 'Token gerado com sucesso! Guarde o token e o secret com segurança.');
    }

    /**
     * Toggle token status (activate/deactivate).
     */
    public function toggleTokenStatus(Request $request, ApiIntegration $apiIntegration, ApiToken $apiToken)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        // Verificar se o token pertence à integração
        if ($apiToken->integration_id !== $apiIntegration->id) {
            abort(404);
        }

        $apiToken->update([
            'is_active' => !$apiToken->is_active,
        ]);

        $status = $apiToken->is_active ? 'ativado' : 'desativado';

        return redirect()
            ->route('settings.api.edit', $apiIntegration)
            ->with('status', "Token {$status} com sucesso!");
    }

    /**
     * Regenerate token (creates new token and deactivates old one).
     */
    public function regenerateToken(Request $request, ApiIntegration $apiIntegration, ApiToken $apiToken)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        // Verificar se o token pertence à integração
        if ($apiToken->integration_id !== $apiIntegration->id) {
            abort(404);
        }

        // Desativar token antigo
        $apiToken->update(['is_active' => false]);

        // Gerar novo token
        $generated = ApiToken::generate();
        
        $newToken = new ApiToken();
        $newToken->integration_id = $apiIntegration->id;
        $newToken->sector_id = $apiIntegration->sector_id;
        $newToken->collaborator_identifier_type = $apiToken->collaborator_identifier_type;
        $newToken->token = $generated['token'];
        $newToken->setSecretHashFromPlainSecret($generated['secret']);
        $newToken->is_active = true;
        $newToken->save();

        return redirect()
            ->route('settings.api.edit', $apiIntegration)
            ->with('token_generated', [
                'token' => $generated['token'],
                'secret' => $generated['secret'],
            ])
            ->with('status', 'Token regenerado com sucesso! Guarde o novo token e secret com segurança.');
    }
}
