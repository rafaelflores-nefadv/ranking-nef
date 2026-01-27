<?php

namespace App\Services;

use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SectorService
{
    private const SESSION_KEY = 'current_sector_id';

    public function getDefaultSectorId(): ?string
    {
        return Sector::where('is_active', true)
            ->orderBy('created_at')
            ->value('id');
    }

    public function getActiveSectors(): Collection
    {
        return Sector::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }

    public function resolveSectorIdForRequest(Request $request): ?string
    {
        $user = $request->user();
        $requested = $request->query('sector');

        if (!$user) {
            return $this->getDefaultSectorId();
        }

        if ($user->role === 'admin') {
            return $this->resolveAdminSector($request, $requested);
        }

        if ($user->role === 'supervisor') {
            return $user->sector_id;
        }

        return $user->sector_id ?? $this->getDefaultSectorId();
    }

    private function resolveAdminSector(Request $request, ?string $requested): ?string
    {
        $current = $request->session()->get(self::SESSION_KEY);

        if ($requested) {
            $sectorId = $this->validateSectorId($requested);
            if ($sectorId) {
                $request->session()->put(self::SESSION_KEY, $sectorId);
                return $sectorId;
            }
        }

        if ($current) {
            $sectorId = $this->validateSectorId($current);
            if ($sectorId) {
                return $sectorId;
            }
        }

        $default = $this->getDefaultSectorId();
        if ($default) {
            $request->session()->put(self::SESSION_KEY, $default);
        }

        return $default;
    }

    private function validateSectorId(string $sectorId): ?string
    {
        return Sector::where('id', $sectorId)
            ->where('is_active', true)
            ->value('id');
    }
}
