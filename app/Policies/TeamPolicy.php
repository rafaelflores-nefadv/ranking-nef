<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // admin e supervisor podem ver
        return in_array($user->role, ['admin', 'supervisor']);
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
        // Apenas admin pode criar
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team $team): bool
    {
        // Apenas admin pode editar
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team $team): bool
    {
        // Apenas admin pode deletar
        return $user->role === 'admin';
    }
}
