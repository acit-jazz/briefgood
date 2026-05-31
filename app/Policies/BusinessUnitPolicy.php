<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\BusinessUnit;
use App\Models\User;

class BusinessUnitPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BusinessUnit $businessUnit): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::SuperAdmin, UserRole::GroupAdmin], true);
    }

    public function update(User $user, BusinessUnit $businessUnit): bool
    {
        return in_array($user->role, [UserRole::SuperAdmin, UserRole::GroupAdmin], true);
    }

    public function delete(User $user, BusinessUnit $businessUnit): bool
    {
        return $user->hasRole(UserRole::SuperAdmin);
    }
}
