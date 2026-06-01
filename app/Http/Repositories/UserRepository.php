<?php

namespace App\Http\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return User::query()
            ->with(['businessUnit', 'roles'])
            ->filter($filters)
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(string $id): ?User
    {
        return User::query()
            ->with(['businessUnit', 'roles'])
            ->find($id);
    }
}
