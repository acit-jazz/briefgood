<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function view(User $user, User $model): bool
    {
        return $user->canManageUsers();
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::SuperAdmin);
    }

    public function update(User $user, User $model): bool
    {
        if ($user->hasRole(UserRole::SuperAdmin)) {
            return true;
        }

        return false;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole(UserRole::SuperAdmin) && $user->id !== $model->id;
    }

    public function manageUsers(User $user): bool
    {
        return $user->hasRole(UserRole::SuperAdmin);
    }
}
