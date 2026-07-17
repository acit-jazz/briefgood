<?php

namespace App\Http\Repositories;

use App\Models\Brief;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BriefRepository
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return Brief::query()
            ->with(['creator', 'latestAnalysis', 'pitchAssignments.businessUnit'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(string $id): ?Brief
    {
        return Brief::query()
            ->with([
                'creator',
                'files',
                'latestAnalysis.recommendations',
                'pitchAssignments.businessUnit',
                'resourceAllocations.resource',
                'activities.causer',
            ])
            ->find($id);
    }
}
