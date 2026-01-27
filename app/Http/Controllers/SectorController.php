<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SectorController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $sectors = Sector::orderBy('name')->paginate(20);

        return view('sectors.index', compact('sectors'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        return view('sectors.create');
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:sectors,slug',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);
        if (!$slug) {
            $slug = Str::slug(Str::uuid()->toString());
        }

        $originalSlug = $slug;
        $counter = 1;
        while (Sector::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        Sector::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('sectors.index')
            ->with('success', 'Setor criado com sucesso!');
    }

    public function edit(Request $request, Sector $sector)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        return view('sectors.edit', compact('sector'));
    }

    public function update(Request $request, Sector $sector)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:sectors,slug,' . $sector->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $sector->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: $sector->slug,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', $sector->is_active),
        ]);

        return redirect()
            ->route('sectors.index')
            ->with('success', 'Setor atualizado com sucesso!');
    }

    public function toggleStatus(Request $request, Sector $sector)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $sector->is_active = !$sector->is_active;
        $sector->save();

        $status = $sector->is_active ? 'ativado' : 'desativado';

        return redirect()
            ->route('sectors.index')
            ->with('success', "Setor {$status} com sucesso!");
    }
}
