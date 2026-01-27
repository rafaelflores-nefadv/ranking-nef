<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Monitor;
use App\Models\Team;
use App\Services\SectorService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MonitorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);
        $monitors = Monitor::where('sector_id', $sectorId)->orderBy('name')->get();

        return view('admin.monitors.index', compact('monitors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);
        $teams = Team::where('sector_id', $sectorId)->orderBy('name')->get();

        return view('admin.monitors.create', compact('teams'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:monitors,slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'settings' => 'nullable|json',
            'refresh_interval' => 'nullable|integer|min:5000',
            'auto_rotate_teams' => 'boolean',
            'teams' => 'nullable|array',
            'teams.*' => Rule::exists('teams', 'id')->where('sector_id', $sectorId),
            'notifications_enabled' => 'boolean',
            'sound_enabled' => 'boolean',
            'voice_enabled' => 'boolean',
            'font_scale' => 'nullable|numeric|min:0.5|max:3.0',
        ]);

        // Gerar slug se não fornecido
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Garantir que o slug seja único
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Monitor::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Preparar settings
        // Usar $request->boolean() para checkboxes (retorna true se checkbox marcado, false caso contrário)
        $settings = [
            'refresh_interval' => $validated['refresh_interval'] ?? 30000,
            'auto_rotate_teams' => $request->boolean('auto_rotate_teams', true), // padrão true
            'teams' => $validated['teams'] ?? [],
            'notifications_enabled' => $request->boolean('notifications_enabled', false),
            'sound_enabled' => $request->boolean('sound_enabled', false),
            'voice_enabled' => $request->boolean('voice_enabled', false), // false se não marcado, true se marcado
            'font_scale' => $validated['font_scale'] ?? 1.0,
        ];

        // Se settings foi passado como JSON, mesclar
        if (!empty($validated['settings'])) {
            $jsonSettings = json_decode($validated['settings'], true);
            if (is_array($jsonSettings)) {
                $settings = array_merge($settings, $jsonSettings);
            }
        }

        $monitor = Monitor::create([
            'sector_id' => $sectorId,
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'settings' => $settings,
        ]);

        return redirect()->route('admin.monitors.index')
            ->with('success', 'Monitor criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Monitor $monitor)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $publicUrl = route('monitor.show', $monitor->slug);

        return view('admin.monitors.show', compact('monitor', 'publicUrl'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Monitor $monitor)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $teams = Team::where('sector_id', $monitor->sector_id)->orderBy('name')->get();
        $settings = $monitor->getMergedSettings();

        return view('admin.monitors.edit', compact('monitor', 'teams', 'settings'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Monitor $monitor)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }
        $sectorId = $monitor->sector_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:monitors,slug,' . $monitor->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'settings' => 'nullable|json',
            'refresh_interval' => 'nullable|integer|min:5000',
            'auto_rotate_teams' => 'boolean',
            'teams' => 'nullable|array',
            'teams.*' => Rule::exists('teams', 'id')->where('sector_id', $sectorId),
            'notifications_enabled' => 'boolean',
            'sound_enabled' => 'boolean',
            'voice_enabled' => 'boolean',
            'font_scale' => 'nullable|numeric|min:0.5|max:3.0',
        ]);

        // Atualizar slug se fornecido
        if (!empty($validated['slug']) && $validated['slug'] !== $monitor->slug) {
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Monitor::where('slug', $validated['slug'])->where('id', '!=', $monitor->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
            $monitor->slug = $validated['slug'];
        }

        // Preparar settings
        $currentSettings = $monitor->settings ?? [];
        // Usar $request->boolean() para checkboxes (retorna true se checkbox marcado, false caso contrário)
        // Se checkbox não foi enviado no request, manter valor atual ou usar padrão
        $settings = [
            'refresh_interval' => $validated['refresh_interval'] ?? $currentSettings['refresh_interval'] ?? 30000,
            'auto_rotate_teams' => $request->has('auto_rotate_teams') 
                ? $request->boolean('auto_rotate_teams', true) 
                : ($currentSettings['auto_rotate_teams'] ?? true),
            'teams' => $validated['teams'] ?? $currentSettings['teams'] ?? [],
            'notifications_enabled' => $request->has('notifications_enabled') 
                ? $request->boolean('notifications_enabled', false) 
                : ($currentSettings['notifications_enabled'] ?? false),
            'sound_enabled' => $request->has('sound_enabled') 
                ? $request->boolean('sound_enabled', false) 
                : ($currentSettings['sound_enabled'] ?? false),
            'voice_enabled' => $request->has('voice_enabled') 
                ? $request->boolean('voice_enabled', false) 
                : ($currentSettings['voice_enabled'] ?? false),
            'font_scale' => $validated['font_scale'] ?? $currentSettings['font_scale'] ?? 1.0,
        ];

        // Se settings foi passado como JSON, mesclar
        if (!empty($validated['settings'])) {
            $jsonSettings = json_decode($validated['settings'], true);
            if (is_array($jsonSettings)) {
                $settings = array_merge($settings, $jsonSettings);
            }
        }

        $monitor->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'settings' => $settings,
        ]);

        return redirect()->route('admin.monitors.index')
            ->with('success', 'Monitor atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Monitor $monitor)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $monitor->delete();

        return redirect()->route('admin.monitors.index')
            ->with('success', 'Monitor excluído com sucesso!');
    }

    /**
     * Toggle status do monitor
     */
    public function toggleStatus(Request $request, Monitor $monitor)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $monitor->is_active = !$monitor->is_active;
        $monitor->save();

        $message = $monitor->is_active ? 'Monitor ativado com sucesso!' : 'Monitor desativado com sucesso!';

        return redirect()->route('admin.monitors.index')
            ->with('success', $message);
    }
}
