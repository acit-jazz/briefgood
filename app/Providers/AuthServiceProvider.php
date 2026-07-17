<?php

namespace App\Providers;

use App\Models\Brief;
use App\Models\BusinessUnit;
use App\Models\PitchAssignment;
use App\Models\User;
use App\Policies\BriefPolicy;
use App\Policies\BusinessUnitPolicy;
use App\Policies\PitchAssignmentPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Brief::class => BriefPolicy::class,
        BusinessUnit::class => BusinessUnitPolicy::class,
        User::class => UserPolicy::class,
        PitchAssignment::class => PitchAssignmentPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
