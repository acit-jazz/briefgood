<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Brief;
use App\Models\User;

class BriefPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Brief $brief): bool
    {
        if ($user->hasRole(UserRole::Viewer)) {
            return true;
        }

        if ($user->hasRole(UserRole::BusinessUnitPic)) {
            return $brief->pitchAssignments()
                ->where('business_unit_id', $user->business_unit_id)
                ->exists();
        }

        return $user->canManageBriefs();
    }

    public function create(User $user): bool
    {
        return $user->canManageBriefs();
    }

    public function update(User $user, Brief $brief): bool
    {
        return $user->canManageBriefs();
    }

    public function delete(User $user, Brief $brief): bool
    {
        return $user->hasRole(UserRole::SuperAdmin) || $user->hasRole(UserRole::GroupAdmin);
    }

    public function analyze(User $user, Brief $brief): bool
    {
        return $user->canManageBriefs();
    }
}
