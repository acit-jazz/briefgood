<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, SoftDeletes, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'business_unit_id',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'role' => UserRole::class,
        ];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function briefs(): HasMany
    {
        return $this->hasMany(Brief::class, 'created_by');
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [UserRole::SuperAdmin, UserRole::GroupAdmin], true);
    }

    public function canManageBriefs(): bool
    {
        return in_array($this->role, [UserRole::SuperAdmin, UserRole::GroupAdmin], true);
    }

    public function canManageUsers(): bool
    {
        return in_array($this->role, [UserRole::SuperAdmin, UserRole::GroupAdmin], true);
    }

    public function scopeFilter(\Illuminate\Database\Eloquent\Builder $query, array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (\Illuminate\Database\Eloquent\Builder $q, string $search): void {
                $q->where(function (\Illuminate\Database\Eloquent\Builder $inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, fn (\Illuminate\Database\Eloquent\Builder $q, string $role) => $q->where('role', $role));
    }
}
