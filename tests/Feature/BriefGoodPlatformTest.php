<?php

use App\Enums\UserRole;
use App\Models\AiAnalysisResult;
use App\Models\Brief;
use App\Models\BusinessUnit;
use App\Models\User;
use Database\Seeders\BriefGoodSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows group admin to view briefs index', function () {
    $user = User::factory()->create(['role' => UserRole::GroupAdmin]);

    $this->actingAs($user)
        ->get(route('briefs.index'))
        ->assertSuccessful();
});

it('allows group admin to view business units', function () {
    $user = User::factory()->create(['role' => UserRole::GroupAdmin]);

    $this->actingAs($user)
        ->get(route('business-units.index'))
        ->assertSuccessful();
});

it('shows dashboard stats for authenticated users', function () {
    $user = User::factory()->create(['role' => UserRole::GroupAdmin]);
    Brief::query()->create([
        'created_by' => $user->id,
        'client_name' => 'Acme Corp',
        'title' => 'Summer Campaign',
        'status' => 'new',
        'ai_status' => 'pending',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('stats.active_briefs')
        );
});

it('seeds seven business units', function () {
    $this->seed(BriefGoodSeeder::class);

    expect(BusinessUnit::query()->count())->toBe(7);
});

it('loads latest analysis without max on uuid', function () {
    $user = User::factory()->create(['role' => UserRole::GroupAdmin]);

    $brief = Brief::query()->create([
        'created_by' => $user->id,
        'client_name' => 'Client',
        'title' => 'Campaign',
        'status' => 'new',
        'ai_status' => 'pending',
    ]);

    AiAnalysisResult::query()->create([
        'brief_id' => $brief->id,
        'executive_summary' => 'First',
        'created_at' => now()->subHour(),
    ]);

    $latest = AiAnalysisResult::query()->create([
        'brief_id' => $brief->id,
        'executive_summary' => 'Latest',
        'created_at' => now(),
    ]);

    $brief->update(['latest_analysis_id' => $latest->id]);

    $loaded = Brief::query()->with('latestAnalysis')->find($brief->id);

    expect($loaded->latestAnalysis?->id)->toBe($latest->id);

    Brief::query()->with(['creator', 'latestAnalysis'])->get();
});
