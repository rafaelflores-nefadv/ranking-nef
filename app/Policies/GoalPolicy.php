<?php

namespace App\Policies;

use App\Models\Goal;
use App\Models\User;
use App\Services\PermissionService;
use App\Services\SectorService;

class GoalPolicy
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
            return PermissionService::can($user, 'goals', 'view');
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Goal $goal): bool
    {
        // user pode ver todos
        if ($user->role === 'user') {
            return $this->isInUserSector($user, $goal);
        }
        
        // admin pode ver todos
        if ($user->role === 'admin') {
            return true;
        }
        
        // supervisor só pode ver metas das suas equipes, vendedores das suas equipes ou globais
        if ($user->role === 'supervisor') {
            $allowedTeamIds = $user->getSupervisedTeamIds();
            if (!$this->isInUserSector($user, $goal)) {
                return false;
            }
            
            // Metas globais: supervisor pode ver
            if ($goal->scope === 'global') {
                return true;
            }
            
            // Metas de equipe: verificar se a equipe está nas permitidas
            if ($goal->scope === 'team' && $goal->team_id) {
                return in_array($goal->team_id, $allowedTeamIds);
            }
            
            // Metas de vendedor: verificar se o vendedor pertence a uma equipe permitida
            if ($goal->scope === 'seller' && $goal->seller_id) {
                $goal->loadMissing('seller.teams');
                if ($goal->seller) {
                    return $goal->seller->teams->pluck('id')->intersect($allowedTeamIds)->isNotEmpty();
                }
            }
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
            return PermissionService::can($user, 'goals', 'create');
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Goal $goal): bool
    {
        // Admin sempre pode editar
        if ($user->role === 'admin') {
            return true;
        }
        
        // Supervisor: verificar permissão configurável e se a meta está nas suas equipes
        if ($user->role === 'supervisor') {
            if (!PermissionService::can($user, 'goals', 'edit')) {
                return false;
            }
            
            $allowedTeamIds = $user->getSupervisedTeamIds();
            if (!$this->isInUserSector($user, $goal)) {
                return false;
            }
            
            // Metas globais: supervisor não pode editar
            if ($goal->scope === 'global') {
                return false;
            }
            
            // Metas de equipe: verificar se a equipe está nas permitidas
            if ($goal->scope === 'team' && $goal->team_id) {
                return in_array($goal->team_id, $allowedTeamIds);
            }
            
            // Metas de vendedor: verificar se o vendedor pertence a uma equipe permitida
            if ($goal->scope === 'seller' && $goal->seller_id) {
                $goal->loadMissing('seller.teams');
                if ($goal->seller) {
                    return $goal->seller->teams->pluck('id')->intersect($allowedTeamIds)->isNotEmpty();
                }
            }
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Goal $goal): bool
    {
        // Admin sempre pode deletar
        if ($user->role === 'admin') {
            return true;
        }
        
        // Supervisor: verificar permissão configurável e se a meta está nas suas equipes
        if ($user->role === 'supervisor') {
            if (!PermissionService::can($user, 'goals', 'delete')) {
                return false;
            }
            
            $allowedTeamIds = $user->getSupervisedTeamIds();
            if (!$this->isInUserSector($user, $goal)) {
                return false;
            }
            
            // Metas globais: supervisor não pode deletar
            if ($goal->scope === 'global') {
                return false;
            }
            
            // Metas de equipe: verificar se a equipe está nas permitidas
            if ($goal->scope === 'team' && $goal->team_id) {
                return in_array($goal->team_id, $allowedTeamIds);
            }
            
            // Metas de vendedor: verificar se o vendedor pertence a uma equipe permitida
            if ($goal->scope === 'seller' && $goal->seller_id) {
                $goal->loadMissing('seller.teams');
                if ($goal->seller) {
                    return $goal->seller->teams->pluck('id')->intersect($allowedTeamIds)->isNotEmpty();
                }
            }
        }
        
        return false;
    }

    private function isInUserSector(User $user, Goal $goal): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        $sectorId = $user->sector_id ?? app(SectorService::class)->getDefaultSectorId();
        return $goal->sector_id === $sectorId;
    }
}
