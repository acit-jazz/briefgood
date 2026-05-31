<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\BusinessUnit;
use App\Models\Category;
use App\Models\Permission;
use App\Models\PromptTemplate;
use App\Models\Resource;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BriefGoodSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRolesAndPermissions();
        $this->seedResources();
        $this->seedBusinessUnits();
        $this->seedPromptTemplates();
        $this->seedAdminUser();
    }

    protected function seedRolesAndPermissions(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => UserRole::SuperAdmin->value],
            ['name' => 'Group Admin', 'slug' => UserRole::GroupAdmin->value],
            ['name' => 'Business Unit PIC', 'slug' => UserRole::BusinessUnitPic->value],
            ['name' => 'Viewer', 'slug' => UserRole::Viewer->value],
        ];

        foreach ($roles as $role) {
            Role::query()->firstOrCreate(['slug' => $role['slug']], $role);
        }

        $permissions = [
            ['name' => 'Manage Briefs', 'slug' => 'briefs.manage', 'group' => 'briefs'],
            ['name' => 'View Briefs', 'slug' => 'briefs.view', 'group' => 'briefs'],
            ['name' => 'Manage Business Units', 'slug' => 'business_units.manage', 'group' => 'cms'],
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'group' => 'users'],
            ['name' => 'Manage AI Settings', 'slug' => 'ai.manage', 'group' => 'ai'],
            ['name' => 'Respond to Pitches', 'slug' => 'pitches.respond', 'group' => 'pitches'],
            ['name' => 'View Analytics', 'slug' => 'analytics.view', 'group' => 'analytics'],
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate(['slug' => $permission['slug']], $permission);
        }
    }

    protected function seedResources(): void
    {
        $resources = [
            'UI/UX Designer',
            'Frontend Developer',
            'Backend Developer',
            'Photographer',
            'Videographer',
            'Motion Designer',
            'Copywriter',
            'Social Media Specialist',
            'Project Manager',
            'Strategist',
        ];

        foreach ($resources as $name) {
            Resource::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'role_type' => Str::slug($name),
                    'is_active' => true,
                ],
            );
        }
    }

    protected function seedBusinessUnits(): void
    {
        $units = [
            [
                'name' => '8ricks',
                'category' => 'Brand Strategy',
                'services' => [
                    'Brand positioning',
                    'Brand architecture',
                    'Brand identity development',
                    'Rebranding strategy',
                    'Brand messaging',
                    'Communication strategy',
                ],
            ],
            [
                'name' => 'Magia',
                'category' => 'Social Media Management',
                'services' => [
                    'Content planning',
                    'Monthly content calendar',
                    'Copywriting',
                    'Feed & story design',
                    'Community management',
                    'Engagement handling',
                    'Social media reporting',
                ],
            ],
            [
                'name' => '8ait',
                'category' => 'KOL / Influencer Management',
                'services' => [
                    'KOL sourcing',
                    'Negotiation',
                    'Campaign coordination',
                    'Content monitoring',
                    'Performance reporting',
                ],
            ],
            [
                'name' => 'Tales Asia',
                'category' => 'Campaign Strategy',
                'services' => [
                    'Campaign ideation',
                    'Creative campaign concept',
                    'Launch strategy',
                    'Integrated marketing campaign',
                    'Seasonal campaign',
                    'Product launch planning',
                ],
            ],
            [
                'name' => 'Inner Circle',
                'category' => 'Campaign Strategy',
                'services' => [
                    'Campaign ideation',
                    'Creative campaign concept',
                    'Launch strategy',
                    'Integrated marketing campaign',
                    'Seasonal campaign',
                    'Product launch planning',
                ],
            ],
            [
                'name' => '8aroka',
                'category' => 'Content Production',
                'services' => [
                    'Photography',
                    'Videography',
                    'Reels/TikTok production',
                    'Commercial video',
                    'Product shoot',
                    'Podcast production',
                    'Livestream production',
                ],
            ],
            [
                'name' => 'Lipat',
                'category' => 'Website & Digital Development',
                'services' => [
                    'Website design',
                    'UI/UX design',
                    'Interactive experience',
                    'AR/VR activation',
                    'Gamification',
                    'Experiential marketing',
                ],
            ],
        ];

        foreach ($units as $unitData) {
            $category = Category::query()->firstOrCreate(
                ['slug' => Str::slug($unitData['category'])],
                [
                    'name' => $unitData['category'],
                    'is_active' => true,
                ],
            );

            $unit = BusinessUnit::query()->firstOrCreate(
                ['slug' => Str::slug($unitData['name'])],
                [
                    'category_id' => $category->id,
                    'name' => $unitData['name'],
                    'description' => "{$unitData['name']} — {$unitData['category']}",
                    'is_active' => true,
                ],
            );

            foreach ($unitData['services'] as $serviceName) {
                Service::query()->firstOrCreate(
                    [
                        'business_unit_id' => $unit->id,
                        'slug' => Str::slug($serviceName),
                    ],
                    [
                        'name' => $serviceName,
                        'keywords' => $this->serviceKeywords($serviceName),
                        'is_active' => true,
                    ],
                );
            }
        }
    }

    /**
     * @return list<string>
     */
    protected function serviceKeywords(string $serviceName): array
    {
        return array_values(array_filter(
            explode(' ', Str::lower($serviceName)),
            fn (string $word) => strlen($word) > 3,
        ));
    }

    protected function seedPromptTemplates(): void
    {
        PromptTemplate::query()->firstOrCreate(
            ['slug' => 'brief-analysis'],
            [
                'name' => 'Brief Analysis',
                'type' => 'brief_analysis',
                'content' => 'Analyze the client brief and return structured JSON with executive summary, scope, deliverables, recommended business units with confidence scores, resource estimates, and complexity scoring.',
                'is_active' => true,
            ],
        );
    }

    protected function seedAdminUser(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'admin@briefgood.test'],
            [
                'name' => 'BriefGood Admin',
                'password' => Hash::make('password'),
                'role' => UserRole::SuperAdmin,
                'email_verified_at' => now(),
            ],
        );

        $role = Role::query()->where('slug', UserRole::SuperAdmin->value)->first();
        if ($role) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }
    }
}
