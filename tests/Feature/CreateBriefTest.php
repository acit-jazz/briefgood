<?php

use App\Enums\AiAnalysisStatus;
use App\Enums\BriefStatus;
use App\Enums\UserRole;
use App\Jobs\AnalyzeBriefJob;
use App\Models\Activity;
use App\Models\Brief;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createBriefPayload(array $overrides = []): array
{
    return array_merge([
        'client_name' => 'Amartha',
        'industry' => 'Fintech',
        'title' => 'Amartha Revamp',
        'brief_date' => now()->toDateString(),
        'budget' => 150000,
        'deadline' => now()->addMonth()->toDateString(),
        'notes' => 'Priority client brief for Q3 campaign.',
        'brief_file' => UploadedFile::fake()->create('amartha-rfp.pdf', 256, 'application/pdf'),
    ], $overrides);
}

function actingAsGroupAdmin(): User
{
    $user = User::factory()->create(['role' => UserRole::GroupAdmin]);

    test()->actingAs($user);

    return $user;
}

it('creates a new brief with pdf upload', function () {
    Queue::fake();
    Storage::fake('local');

    $user = actingAsGroupAdmin();

    $response = test()->post(route('briefs.store'), createBriefPayload());

    $brief = Brief::query()->first();

    $response
        ->assertRedirect(route('briefs.show', $brief))
        ->assertSessionHas('success');

    expect($brief)->not->toBeNull()
        ->and($brief->created_by)->toBe($user->id)
        ->and($brief->client_name)->toBe('Amartha')
        ->and($brief->title)->toBe('Amartha Revamp')
        ->and($brief->industry)->toBe('Fintech')
        ->and($brief->status)->toBe(BriefStatus::New)
        ->and($brief->ai_status)->toBe(AiAnalysisStatus::Pending)
        ->and($brief->primary_file_name)->toBe('amartha-rfp.pdf')
        ->and($brief->primary_file_mime)->toBe('application/pdf');

    Storage::disk('local')->assertExists($brief->primary_file_path);

    Queue::assertPushed(AnalyzeBriefJob::class, fn (AnalyzeBriefJob $job) => $job->brief->is($brief));
});

it('records activity and shows brief page after create', function () {
    Queue::fake();
    Storage::fake('local');

    actingAsGroupAdmin();

    $response = test()->post(route('briefs.store'), createBriefPayload());

    $brief = Brief::query()->firstOrFail();

    $response->assertRedirect(route('briefs.show', $brief));

    expect(Activity::query()->where('subject_id', $brief->id)->exists())->toBeTrue();

    test()->get(route('briefs.show', $brief))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('briefs/Show')
            ->where('brief.id', $brief->id)
            ->where('brief.title', 'Amartha Revamp')
            ->where('analysis', null)
        );
});

it('lists created brief on index with latest analysis relation', function () {
    Queue::fake();
    Storage::fake('local');

    actingAsGroupAdmin();

    test()->post(route('briefs.store'), createBriefPayload());

    test()->get(route('briefs.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('briefs/Index')
            ->has('briefs.data', 1)
            ->where('briefs.data.0.title', 'Amartha Revamp')
        );
});

it('forbids viewers from creating a brief', function () {
    $user = User::factory()->create(['role' => UserRole::Viewer]);

    test()->actingAs($user)
        ->post(route('briefs.store'), createBriefPayload())
        ->assertForbidden();
});

it('validates required fields when creating a brief', function () {
    actingAsGroupAdmin();

    test()->post(route('briefs.store'), [])
        ->assertSessionHasErrors(['client_name', 'title', 'brief_file']);
});

it('rejects non-pdf uploads', function () {
    actingAsGroupAdmin();

    test()->post(route('briefs.store'), createBriefPayload([
        'brief_file' => UploadedFile::fake()->create('brief.docx', 100, 'application/msword'),
    ]))->assertSessionHasErrors('brief_file');
});
