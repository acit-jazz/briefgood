<?php

namespace App\Providers;

use App\Models\Brief;
use App\Models\BusinessUnit;
use App\Policies\BriefPolicy;
use App\Policies\BusinessUnitPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Brief::class => BriefPolicy::class,
        BusinessUnit::class => BusinessUnitPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
