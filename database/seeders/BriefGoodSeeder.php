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
        $this->seedServices(); // Create standalone services first
        $this->seedBusinessUnits(); // Then create BUs and attach services
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

    protected function seedServices(): void
    {
        $services = [
            // Brand Strategy
            ['name' => 'Brand positioning', 'keywords' => ['brand', 'positioning', 'strategy']],
            ['name' => 'Brand architecture', 'keywords' => ['brand', 'architecture', 'structure']],
            ['name' => 'Brand identity development', 'keywords' => ['brand', 'identity', 'visual', 'logo', 'design']],
            ['name' => 'Rebranding strategy', 'keywords' => ['rebrand', 'brand', 'refresh', 'strategy']],
            ['name' => 'Brand messaging', 'keywords' => ['brand', 'messaging', 'tone', 'voice', 'copy']],
            ['name' => 'Communication strategy', 'keywords' => ['communication', 'strategy', 'messaging']],

            // Social Media Management
            ['name' => 'Content planning', 'keywords' => ['content', 'planning', 'strategy', 'social']],
            ['name' => 'Monthly content calendar', 'keywords' => ['content', 'calendar', 'schedule', 'monthly']],
            ['name' => 'Copywriting', 'keywords' => ['copy', 'copywriting', 'content', 'writing']],
            ['name' => 'Feed & story design', 'keywords' => ['feed', 'story', 'design', 'instagram', 'social']],
            ['name' => 'Community management', 'keywords' => ['community', 'management', 'engagement', 'social']],
            ['name' => 'Social media reporting', 'keywords' => ['social', 'media', 'report', 'analytics', 'metrics']],

            // KOL / Influencer Management
            ['name' => 'KOL sourcing', 'keywords' => ['kol', 'influencer', 'sourcing', 'find', 'talent']],
            ['name' => 'Negotiation', 'keywords' => ['negotiation', 'kol', 'contract', 'deal']],
            ['name' => 'Campaign coordination', 'keywords' => ['campaign', 'coordination', 'kol', 'influencer']],
            ['name' => 'Content monitoring', 'keywords' => ['content', 'monitoring', 'tracking', 'kol']],
            ['name' => 'Performance reporting', 'keywords' => ['performance', 'report', 'metrics', 'roi', 'kol']],

            // Campaign Strategy
            ['name' => 'Campaign ideation', 'keywords' => ['campaign', 'ideation', 'concept', 'idea', 'creative']],
            ['name' => 'Creative campaign concept', 'keywords' => ['creative', 'campaign', 'concept', 'idea']],
            ['name' => 'Launch strategy', 'keywords' => ['launch', 'strategy', 'product', 'campaign']],
            ['name' => 'Integrated marketing campaign', 'keywords' => ['integrated', 'marketing', 'campaign', 'multi-channel']],
            ['name' => 'Seasonal campaign', 'keywords' => ['seasonal', 'campaign', 'holiday', 'event']],
            ['name' => 'Product launch planning', 'keywords' => ['product', 'launch', 'planning', 'release']],

            // Content Production
            ['name' => 'Photography', 'keywords' => ['photo', 'photography', 'shoot', 'product']],
            ['name' => 'Videography', 'keywords' => ['video', 'videography', 'shoot', 'production']],
            ['name' => 'Reels/TikTok production', 'keywords' => ['reels', 'tiktok', 'short', 'video', 'social']],
            ['name' => 'Commercial video', 'keywords' => ['commercial', 'video', 'ad', 'advertising']],
            ['name' => 'Product shoot', 'keywords' => ['product', 'shoot', 'photography', 'catalog']],
            ['name' => 'Podcast production', 'keywords' => ['podcast', 'production', 'audio', 'recording']],
            ['name' => 'Livestream production', 'keywords' => ['livestream', 'live', 'stream', 'broadcast']],

            // Website & Digital Development
            ['name' => 'Website design', 'keywords' => ['website', 'design', 'web', 'landing']],
            ['name' => 'UI/UX design', 'keywords' => ['ui', 'ux', 'design', 'interface', 'user']],
            ['name' => 'Interactive experience', 'keywords' => ['interactive', 'experience', 'digital', 'web']],
            ['name' => 'AR/VR activation', 'keywords' => ['ar', 'vr', 'augmented', 'virtual', 'reality']],
            ['name' => 'Gamification', 'keywords' => ['gamification', 'game', 'interactive', 'engagement']],
            ['name' => 'Experiential marketing', 'keywords' => ['experiential', 'marketing', 'event', 'activation']],

            // SEO & Performance
            ['name' => 'SEO optimization', 'keywords' => ['seo', 'search', 'optimization', 'ranking']],
            ['name' => 'Website migration', 'keywords' => ['migration', 'website', 'transfer', 'seo']],
            ['name' => 'Website maintenance', 'keywords' => ['maintenance', 'website', 'support', 'update']],

            // Analytics & Data
            ['name' => 'Analytics setup', 'keywords' => ['analytics', 'setup', 'tracking', 'data']],
            ['name' => 'Data visualization', 'keywords' => ['data', 'visualization', 'dashboard', 'report']],
        ];

        foreach ($services as $serviceData) {
            Service::query()->firstOrCreate(
                ['slug' => Str::slug($serviceData['name'])],
                [
                    'name' => $serviceData['name'],
                    'keywords' => $serviceData['keywords'],
                    'is_active' => true,
                ],
            );
        }
    }

    protected function seedBusinessUnits(): void
    {
        // Define BUs with their services and specialization scores
        // Score 1-100: 80+ = Expert, 60-79 = Advanced, 40-59 = Intermediate, <40 = Basic
        $units = [
            [
                'name' => '8ricks',
                'category' => 'Brand Strategy',
                'description' => 'Strategic brand consultancy specializing in brand positioning, architecture, and identity development for emerging and established brands.',
                'services' => [
                    'Brand positioning' => 95,      // Core expertise
                    'Brand architecture' => 90,    // Core expertise
                    'Brand identity development' => 85, // Strong capability
                    'Rebranding strategy' => 88,    // Very experienced
                    'Brand messaging' => 92,       // Core expertise
                    'Communication strategy' => 85,
                ],
            ],
            [
                'name' => 'Magia',
                'category' => 'Social Media Management',
                'description' => 'Full-service social media management agency with expertise in content creation, community engagement, and social media strategy.',
                'services' => [
                    'Content planning' => 90,       // Core expertise
                    'Monthly content calendar' => 88, // Core expertise
                    'Copywriting' => 85,            // Strong capability
                    'Feed & story design' => 92,   // Core expertise
                    'Community management' => 90,   // Core expertise
                    'Social media reporting' => 85,
                ],
            ],
            [
                'name' => '8ait',
                'category' => 'KOL / Influencer Management',
                'description' => 'KOL and influencer marketing agency with extensive network across Southeast Asia markets.',
                'services' => [
                    'KOL sourcing' => 95,          // Core expertise - wide network
                    'Negotiation' => 88,            // Very experienced
                    'Campaign coordination' => 90,   // Core expertise
                    'Content monitoring' => 82,      // Strong capability
                    'Performance reporting' => 85,
                ],
            ],
            [
                'name' => 'Tales Asia',
                'category' => 'Campaign Strategy',
                'description' => 'Award-winning campaign strategy agency specializing in integrated marketing campaigns and product launches across Asia.',
                'services' => [
                    'Campaign ideation' => 95,      // Award-winning
                    'Creative campaign concept' => 93, // Award-winning
                    'Launch strategy' => 90,        // Very experienced
                    'Integrated marketing campaign' => 92, // Core expertise
                    'Seasonal campaign' => 85,      // Strong capability
                    'Product launch planning' => 88,
                ],
            ],
            [
                'name' => 'Inner Circle',
                'category' => 'Campaign Strategy',
                'description' => 'Boutique campaign agency focused on targeted campaigns and niche market strategies with strong regional expertise.',
                'services' => [
                    'Campaign ideation' => 75,      // Good but not award-winning
                    'Creative campaign concept' => 72, // Good capability
                    'Launch strategy' => 70,
                    'Integrated marketing campaign' => 68, // Less integrated capability
                    'Seasonal campaign' => 80,      // Strong in seasonal
                    'Product launch planning' => 65,
                ],
            ],
            [
                'name' => '8aroka',
                'category' => 'Content Production',
                'description' => 'Professional content production house specializing in photography, videography, and social media content creation.',
                'services' => [
                    'Photography' => 95,             // Core expertise
                    'Videography' => 92,          // Core expertise
                    'Reels/TikTok production' => 88, // Strong social content
                    'Commercial video' => 90,       // Core expertise
                    'Product shoot' => 93,         // Core expertise
                    'Podcast production' => 75,       // Good capability
                    'Livestream production' => 80,    // Strong in live events
                ],
            ],
            [
                'name' => 'Lipat',
                'category' => 'Website & Digital Development',
                'description' => 'Digital innovation agency specializing in website development, interactive experiences, and emerging technologies.',
                'services' => [
                    'Website design' => 90,         // Core expertise
                    'UI/UX design' => 92,          // Core expertise
                    'Interactive experience' => 88,  // Strong capability
                    'AR/VR activation' => 85,       // Early adopter/expert
                    'Gamification' => 82,           // Strong capability
                    'Experiential marketing' => 80,  // Good at digital experiences
                    'Website migration' => 78,      // Experienced
                    'Website maintenance' => 72,
                ],
            ],
        ];

        foreach ($units as $unitData) {
            // Create or get category
            $category = Category::query()->firstOrCreate(
                ['slug' => Str::slug($unitData['category'])],
                [
                    'name' => $unitData['category'],
                    'is_active' => true,
                ],
            );

            // Create or get BU
            $unit = BusinessUnit::query()->firstOrCreate(
                ['slug' => Str::slug($unitData['name'])],
                [
                    'category_id' => $category->id,
                    'name' => $unitData['name'],
                    'description' => $unitData['description'],
                    'is_active' => true,
                ],
            );

            // Attach services with specialization scores
            foreach ($unitData['services'] as $serviceName => $score) {
                $service = Service::query()->where('slug', Str::slug($serviceName))->first();

                if ($service) {
                    // Detach first (in case of re-seeding), then attach with new score
                    $unit->services()->detach($service->id);
                    $unit->services()->attach($service->id, [
                        'specialization_score' => $score,
                        'notes' => null,
                    ]);
                }
            }
        }
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
