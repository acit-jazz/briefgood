<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\BusinessUnit;
use App\Models\User;

class BusinessUnitPolicy
{
    /**
     * Everyone can view business unit list (Super Admin, Group Admin, BU PIC, Viewer)
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Everyone can view business unit details
     */
    public function view(User $user, BusinessUnit $businessUnit): bool
    {
        return true;
    }

    /**
     * Only Super Admin and Group Admin can create business units
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::SuperAdmin) || $user->hasRole(UserRole::GroupAdmin);
    }

    /**
     * Only Super Admin and Group Admin can update business units
     */
    public function update(User $user, BusinessUnit $businessUnit): bool
    {
        return $user->hasRole(UserRole::SuperAdmin) || $user->hasRole(UserRole::GroupAdmin);
    }

    /**
     * Only Super Admin can delete business units
     */
    public function delete(User $user, BusinessUnit $businessUnit): bool
    {
        return $user->hasRole(UserRole::SuperAdmin);
    }
}
