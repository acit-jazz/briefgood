<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    /**
     * Super Admin: can view all users
     * Group Admin: can view all users
     * Business Unit PIC: cannot view user list
     * Viewer: cannot view user list
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::SuperAdmin) || $user->hasRole(UserRole::GroupAdmin);
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole(UserRole::SuperAdmin) || $user->hasRole(UserRole::GroupAdmin);
    }

    /**
     * Only Super Admin can create users
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::SuperAdmin);
    }

    /**
     * Super Admin can update any user
     * Group Admin can update users (but not their role)
     */
    public function update(User $user, User $model): bool
    {
        // Super Admin can update anyone
        if ($user->hasRole(UserRole::SuperAdmin)) {
            return true;
        }

        // Group Admin can update anyone except changing roles
        if ($user->hasRole(UserRole::GroupAdmin)) {
            // Cannot change user's role
            if ($model->role !== $user->role) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Only Super Admin can delete users (and not themselves)
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasRole(UserRole::SuperAdmin) && $user->id !== $model->id;
    }

    /**
     * Only Super Admin can manage users (full access)
     * Group Admin CANNOT manage users (per requirements)
     */
    public function manageUsers(User $user): bool
    {
        return $user->hasRole(UserRole::SuperAdmin);
    }
}
