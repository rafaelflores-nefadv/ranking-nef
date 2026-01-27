<?php

namespace App\Policies;

use App\Models\Seller;
use App\Models\User;
use App\Services\PermissionService;
use App\Services\SectorService;

class SellerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // user pode ver (sem mudança)
        if ($user->role === 'user') {
            return true;
        }

        // Admin sempre pode ver
        if ($user->role === 'admin') {
            return true;
        }

        // Supervisor: verificar permissão configurável
        if ($user->role === 'supervisor') {
            return PermissionService::can($user, 'sellers', 'view');
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Seller $seller): bool
    {
        // user pode ver todos
        if ($user->role === 'user') {
            return $this->isInUserSector($user, $seller);
        }
        
        // admin pode ver todos
        if ($user->role === 'admin') {
            return true;
        }
        
        // supervisor só pode ver vendedores das suas equipes
        if ($user->role === 'supervisor') {
            $allowedTeamIds = $user->getSupervisedTeamIds();
            if (!$this->isInUserSector($user, $seller)) {
                return false;
            }
            return $seller->teams->pluck('id')->intersect($allowedTeamIds)->isNotEmpty();
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin sempre pode criar
        if ($user->role === 'admin') {
            return true;
        }

        // Supervisor: verificar permissão configurável
        if ($user->role === 'supervisor') {
            return PermissionService::can($user, 'sellers', 'create');
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Seller $seller): bool
    {
        // Admin sempre pode editar
        if ($user->role === 'admin') {
            return true;
        }
        
        // Supervisor: verificar permissão configurável e se o vendedor está nas suas equipes
        if ($user->role === 'supervisor') {
            if (!PermissionService::can($user, 'sellers', 'edit')) {
                return false;
            }
            
            // Verificar se o vendedor pertence a uma equipe do supervisor
            $allowedTeamIds = $user->getSupervisedTeamIds();
            if (!$this->isInUserSector($user, $seller)) {
                return false;
            }
            return $seller->teams->pluck('id')->intersect($allowedTeamIds)->isNotEmpty();
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Seller $seller): bool
    {
        // Admin sempre pode deletar
        if ($user->role === 'admin') {
            return true;
        }

        // Supervisor: verificar permissão configurável e se o vendedor está nas suas equipes
        if ($user->role === 'supervisor') {
            if (!PermissionService::can($user, 'sellers', 'delete')) {
                return false;
            }
            
            // Verificar se o vendedor pertence a uma equipe do supervisor
            $allowedTeamIds = $user->getSupervisedTeamIds();
            if (!$this->isInUserSector($user, $seller)) {
                return false;
            }
            return $seller->teams->pluck('id')->intersect($allowedTeamIds)->isNotEmpty();
        }

        return false;
    }

    private function isInUserSector(User $user, Seller $seller): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        $sectorId = $user->sector_id ?? app(SectorService::class)->getDefaultSectorId();
        return $seller->sector_id === $sectorId;
    }
}
