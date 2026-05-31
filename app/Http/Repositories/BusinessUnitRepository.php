<?php

namespace App\Http\Repositories;

use App\Models\BusinessUnit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BusinessUnitRepository
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return BusinessUnit::query()
            ->with(['category', 'pic', 'services'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function allActive(): Collection
    {
        return BusinessUnit::query()
            ->with(['category', 'services'])
            ->active()
            ->orderBy('name')
            ->get();
    }
}
