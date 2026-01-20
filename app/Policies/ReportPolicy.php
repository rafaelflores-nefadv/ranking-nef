<?php

namespace App\Policies;

use App\Models\User;
use App\Services\PermissionService;

class ReportPolicy
{
    /**
     * Determine whether the user can view any reports.
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
            return PermissionService::can($user, 'reports', 'view');
        }

        return false;
    }

    /**
     * Determine whether the user can view general ranking.
     */
    public function viewGeneralRanking(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can view team ranking.
     */
    public function viewTeamRanking(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can view score evolution.
     */
    public function viewScoreEvolution(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can view occurrences.
     */
    public function viewOccurrences(User $user): bool
    {
        // Apenas admin e supervisor podem ver ocorrências
        return in_array($user->role, ['admin', 'supervisor']);
    }

    /**
     * Determine whether the user can view gamification reports.
     */
    public function viewGamification(User $user): bool
    {
        return $this->viewAny($user);
    }
}
