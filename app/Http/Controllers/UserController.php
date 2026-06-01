<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Repositories\UserRepository;
use App\Http\Requests\User\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\BusinessUnit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        protected UserRepository $users,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $items = $this->users->paginate(20, $request->only(['search', 'role']));

        return Inertia::render('users/Index', [
            'users' => UserResource::collection($items),
        ]);
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        $user->load(['businessUnit', 'roles']);

        return Inertia::render('users/Form', [
            'user' => UserResource::make($user)->resolve(),
            'roles' => UserRole::cases(),
            'businessUnits' => BusinessUnit::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted.');
    }
}
