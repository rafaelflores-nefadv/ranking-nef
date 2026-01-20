<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use App\Services\PermissionService;

class TeamPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin sempre pode ver
        if ($user->role === 'admin') {
            return true;
        }

        // Supervisor: verificar permissão configurável
        if ($user->role === 'supervisor') {
            return PermissionService::can($user, 'teams', 'view');
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Team $team): bool
    {
        // admin pode ver todas
        if ($user->role === 'admin') {
            return true;
        }
        
        // supervisor só pode ver suas equipes
        if ($user->role === 'supervisor') {
            $allowedTeamIds = $user->getSupervisedTeamIds();
            return in_array($team->id, $allowedTeamIds);
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
            return PermissionService::can($user, 'teams', 'create');
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team $team): bool
    {
        // Admin sempre pode editar
        if ($user->role === 'admin') {
            return true;
        }

        // Supervisor: verificar permissão configurável e se a equipe está nas suas equipes
        if ($user->role === 'supervisor') {
            if (!PermissionService::can($user, 'teams', 'edit')) {
                return false;
            }
            
            $allowedTeamIds = $user->getSupervisedTeamIds();
            return in_array($team->id, $allowedTeamIds);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team $team): bool
    {
        // Admin sempre pode deletar
        if ($user->role === 'admin') {
            return true;
        }

        // Supervisor: verificar permissão configurável e se a equipe está nas suas equipes
        if ($user->role === 'supervisor') {
            if (!PermissionService::can($user, 'teams', 'delete')) {
                return false;
            }
            
            $allowedTeamIds = $user->getSupervisedTeamIds();
            return in_array($team->id, $allowedTeamIds);
        }

        return false;
    }
}
