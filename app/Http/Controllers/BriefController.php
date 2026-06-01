<?php

namespace App\Http\Controllers;

use App\Enums\AiAnalysisStatus;
use App\Enums\BriefStatus;
use App\Http\Repositories\BriefRepository;
use App\Http\Requests\Brief\StoreBriefRequest;
use App\Http\Resources\AiAnalysisResource;
use App\Http\Resources\BriefResource;
use App\Jobs\AnalyzeBriefJob;
use App\Models\Activity;
use App\Models\Brief;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BriefController extends Controller
{
    public function __construct(
        protected BriefRepository $briefs,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Brief::class);

        $items = $this->briefs->paginate(15, $request->only(['search', 'status', 'ai_status']));

        return Inertia::render('briefs/Index', [
            'briefs' => BriefResource::collection($items),
            'filters' => $request->only(['search', 'status', 'ai_status']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Brief::class);

        return Inertia::render('briefs/Create');
    }

    public function store(StoreBriefRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $file = $request->file('brief_file');
        $disk = config('briefgood.uploads.disk', 'local');
        $path = $file->store('briefs/'.now()->format('Y/m'), $disk);

        $brief = Brief::query()->create([
            'created_by' => $request->user()->id,
            'client_name' => $validated['client_name'],
            'industry' => $validated['industry'] ?? null,
            'title' => $validated['title'],
            'brief_date' => $validated['brief_date'] ?? null,
            'budget' => $validated['budget'] ?? null,
            'deadline' => $validated['deadline'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'primary_file_name' => $file->getClientOriginalName(),
            'primary_file_path' => $path,
            'primary_file_mime' => $file->getMimeType(),
            'primary_file_size' => $file->getSize(),
            'status' => BriefStatus::New,
            'ai_status' => AiAnalysisStatus::Pending,
        ]);

        Activity::query()->create([
            'subject_type' => Brief::class,
            'subject_id' => $brief->id,
            'causer_id' => $request->user()->id,
            'event' => 'brief.created',
            'description' => "Brief \"{$brief->title}\" uploaded.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        AnalyzeBriefJob::dispatch($brief);

        return redirect()->route('briefs.show', $brief);
    }

    public function show(Brief $brief): Response
    {
        $this->authorize('view', $brief);

        $brief = $this->briefs->find($brief->id) ?? $brief;
        $brief->load(['latestAnalysis.recommendations', 'resourceAllocations.resource', 'pitchAssignments.businessUnit']);

        return Inertia::render('briefs/Show', [
            'brief' => BriefResource::make($brief)->resolve(),
            'analysis' => $brief->latestAnalysis
                ? AiAnalysisResource::make($brief->latestAnalysis)->resolve()
                : null,
            'pitchAssignments' => $brief->pitchAssignments->map(function ($assignment) use ($brief) {
                // Try to find matching recommendation by business_unit_id first, then by name
                $recommendation = $brief->latestAnalysis?->recommendations
                    ->where('business_unit_id', $assignment->business_unit_id)
                    ->first();

                // Fallback: match by business unit name if not found by ID
                if (! $recommendation && $assignment->businessUnit?->name) {
                    $recommendation = $brief->latestAnalysis?->recommendations
                        ->firstWhere('business_unit_name', $assignment->businessUnit->name);
                }

                return [
                    'id' => $assignment->id,
                    'status' => $assignment->status?->value,
                    'confidence' => $assignment->confidence,
                    'business_unit' => $assignment->businessUnit?->name,
                    'matched_services' => $recommendation?->matched_services ?? [],
                    'recommendation_confidence' => $recommendation?->confidence,
                ];
            }),
            'resourceAllocations' => $brief->resourceAllocations->map(fn ($allocation) => [
                'id' => $allocation->id,
                'resource_name' => $allocation->resource?->name,
                'estimated_hours' => $allocation->estimated_hours,
                'estimated_workload_percent' => $allocation->estimated_workload_percent,
                'estimated_duration_days' => $allocation->estimated_duration_days,
            ]),
        ]);
    }

    public function preview(Brief $brief): Response
    {
        $this->authorize('view', $brief);

        $brief = $this->briefs->find($brief->id) ?? $brief;
        $brief->load(['latestAnalysis.recommendations', 'resourceAllocations.resource', 'pitchAssignments.businessUnit']);

        return Inertia::render('briefs/Preview', [
            'brief' => BriefResource::make($brief)->resolve(),
            'analysis' => $brief->latestAnalysis
                ? AiAnalysisResource::make($brief->latestAnalysis)->resolve()
                : null,
            'pitchAssignments' => $brief->pitchAssignments->map(function ($assignment) use ($brief) {
                $recommendation = $brief->latestAnalysis?->recommendations
                    ->where('business_unit_id', $assignment->business_unit_id)
                    ->first();

                if (! $recommendation && $assignment->businessUnit?->name) {
                    $recommendation = $brief->latestAnalysis?->recommendations
                        ->firstWhere('business_unit_name', $assignment->businessUnit->name);
                }

                return [
                    'id' => $assignment->id,
                    'status' => $assignment->status?->value,
                    'confidence' => $assignment->confidence,
                    'business_unit' => $assignment->businessUnit?->name,
                    'matched_services' => $recommendation?->matched_services ?? [],
                ];
            }),
        ]);
    }

    public function analyze(Request $request, Brief $brief): RedirectResponse
    {
        $this->authorize('analyze', $brief);

        AnalyzeBriefJob::dispatch($brief, $request->boolean('advanced'));

        return back()->with('success', 'AI analysis has been queued.');
    }
}
