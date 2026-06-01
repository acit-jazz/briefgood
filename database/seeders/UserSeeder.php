<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\BusinessUnit;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $businessUnits = BusinessUnit::query()->active()->get();

        $users = [
            // Super Admin
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@briefgood.test',
                'password' => 'password',
                'role' => UserRole::SuperAdmin,
                'business_unit_id' => null,
            ],
            // Group Admin
            [
                'name' => 'Group Administrator',
                'email' => 'groupadmin@briefgood.test',
                'password' => 'password',
                'role' => UserRole::GroupAdmin,
                'business_unit_id' => null,
            ],
        ];

        // Business Unit PICs - one for each BU
        foreach ($businessUnits as $index => $bu) {
            $users[] = [
                'name' => $bu->name . ' PIC',
                'email' => 'pic-' . strtolower(str_replace(' ', '', $bu->name)) . '@briefgood.test',
                'password' => 'password',
                'role' => UserRole::BusinessUnitPic,
                'business_unit_id' => $bu->id,
            ];
        }

        // Viewers
        $users[] = [
            'name' => 'Viewer User',
            'email' => 'viewer@briefgood.test',
            'password' => 'password',
            'role' => UserRole::Viewer,
            'business_unit_id' => null,
        ];

        foreach ($users as $userData) {
            $role = Role::query()->where('slug', $userData['role']->value)->first();

            $user = User::query()->firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'role' => $userData['role'],
                    'business_unit_id' => $userData['business_unit_id'],
                    'email_verified_at' => now(),
                ],
            );

            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        }
    }
}
