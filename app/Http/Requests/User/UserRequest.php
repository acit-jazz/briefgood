<?php

namespace App\Http\Requests\User;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user
            ? ($this->user()?->can('update', $user) ?? false)
            : ($this->user()?->can('create', User::class) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'role' => ['required', Rule::enum(UserRole::class)],
            'business_unit_id' => ['nullable', 'uuid', 'exists:business_units,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        // Password is required on create
        if (! $userId) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        return $rules;
    }
}
