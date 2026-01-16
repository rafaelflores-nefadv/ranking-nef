<?php

namespace App\Policies;

use App\Models\ScoreRule;
use App\Models\User;

class ScoreRulePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Apenas admin
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ScoreRule $scoreRule): bool
    {
        // Apenas admin
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Apenas admin
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ScoreRule $scoreRule): bool
    {
        // Apenas admin
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ScoreRule $scoreRule): bool
    {
        // Apenas admin
        return $user->role === 'admin';
    }
}
