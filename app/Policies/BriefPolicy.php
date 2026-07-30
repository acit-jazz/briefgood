<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Brief;
use App\Models\User;

class BriefPolicy
{
    /**
     * Everyone can view the brief list (Super Admin, Group Admin, BU PIC, Viewer)
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * View brief details:
     * - Super Admin: all briefs
     * - Group Admin: all briefs
     * - Business Unit PIC: only briefs assigned to their BU
     * - Viewer: all briefs (read only)
     */
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

        // Super Admin and Group Admin can view all
        return $user->hasRole(UserRole::SuperAdmin) || $user->hasRole(UserRole::GroupAdmin);
    }

    /**
     * Only Super Admin and Group Admin can create briefs
     * Business Unit PIC and Viewer cannot create briefs
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::SuperAdmin) || $user->hasRole(UserRole::GroupAdmin);
    }

    /**
     * Only Super Admin and Group Admin can update briefs
     */
    public function update(User $user, Brief $brief): bool
    {
        return $user->hasRole(UserRole::SuperAdmin) || $user->hasRole(UserRole::GroupAdmin);
    }

    /**
     * Only Super Admin can delete briefs
     */
    public function delete(User $user, Brief $brief): bool
    {
        return $user->hasRole(UserRole::SuperAdmin);
    }

    /**
     * Only Super Admin can restore briefs
     */
    public function restore(User $user, Brief $brief): bool
    {
        return $user->hasRole(UserRole::SuperAdmin);
    }

    /**
     * Only Super Admin can view trash
     */
    public function viewTrash(User $user): bool
    {
        return $user->hasRole(UserRole::SuperAdmin);
    }

    /**
     * Only Super Admin and Group Admin can run AI analysis
     */
    public function analyze(User $user, Brief $brief): bool
    {
        return $user->hasRole(UserRole::SuperAdmin) || $user->hasRole(UserRole::GroupAdmin);
    }

    /**
     * Business Unit PIC can accept/manage assignments for their BU
     */
    public function acceptAssignment(User $user, Brief $brief): bool
    {
        return $user->hasRole(UserRole::BusinessUnitPic) &&
            $brief->pitchAssignments()
                ->where('business_unit_id', $user->business_unit_id)
                ->exists();
    }
}
