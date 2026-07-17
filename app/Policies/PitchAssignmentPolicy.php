<?php

namespace App\Policies;

use App\Enums\PitchAssignmentStatus;
use App\Enums\UserRole;
use App\Models\PitchAssignment;
use App\Models\User;

class PitchAssignmentPolicy
{
    /**
     * Business Unit PIC can only accept/decline assignments for their own BU
     */
    public function acceptAssignment(User $user, PitchAssignment $pitchAssignment): bool
    {
        // Only Business Unit PIC can accept/decline
        if (! $user->hasRole(UserRole::BusinessUnitPic)) {
            return false;
        }

        // BU PIC can only accept assignments for their assigned BU
        return $pitchAssignment->business_unit_id === $user->business_unit_id
            && $pitchAssignment->status === PitchAssignmentStatus::Pending;
    }

    /**
     * Same as acceptAssignment - BU PIC can decline their own assignments
     */
    public function declineAssignment(User $user, PitchAssignment $pitchAssignment): bool
    {
        if (!$user->hasRole(UserRole::BusinessUnitPic)) {
            return false;
        }

        return $pitchAssignment->business_unit_id === $user->business_unit_id
            && $pitchAssignment->status === PitchAssignmentStatus::Pending;
    }
}
