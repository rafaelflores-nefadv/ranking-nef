<?php

namespace App\Policies;

use App\Models\Seller;
use App\Models\User;

class SellerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // admin, supervisor e user podem ver
        return in_array($user->role, ['admin', 'supervisor', 'user']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Seller $seller): bool
    {
        // user pode ver todos
        if ($user->role === 'user') {
            return true;
        }
        
        // admin pode ver todos
        if ($user->role === 'admin') {
            return true;
        }
        
        // supervisor só pode ver vendedores das suas equipes
        if ($user->role === 'supervisor') {
            $allowedTeamIds = $user->getSupervisedTeamIds();
            return in_array($seller->team_id, $allowedTeamIds);
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
    public function update(User $user, Seller $seller): bool
    {
        // admin pode editar todos
        if ($user->role === 'admin') {
            return true;
        }
        
        // supervisor só pode editar vendedores das suas equipes
        if ($user->role === 'supervisor') {
            $allowedTeamIds = $user->getSupervisedTeamIds();
            return in_array($seller->team_id, $allowedTeamIds);
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Seller $seller): bool
    {
        // Apenas admin pode deletar
        return $user->role === 'admin';
    }
}
