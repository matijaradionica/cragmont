<?php

namespace App\Policies;

use App\Models\ConditionReport;
use App\Models\User;

class ConditionReportPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isModerator() || $user->isClubEquipper();
    }

    public function update(User $user, ConditionReport $report): bool
    {
        return $user->isAdmin() || $user->isModerator() || $user->isClubEquipper();
    }

    public function delete(User $user, ConditionReport $report): bool
    {
        return $user->isAdmin() || $user->isModerator();
    }
}

