<?php

namespace App\Http\Controllers;

use App\Enums\AiAnalysisStatus;
use App\Enums\BriefStatus;
use App\Http\Requests\Brief\StoreBriefRequest;
use App\Http\Requests\UpdateAnalysisRequest;
use App\Http\Repositories\BriefRepository;
use App\Http\Resources\AiAnalysisResource;
use App\Http\Resources\BriefResource;
use App\Jobs\AnalyzeBriefJob;
use App\Models\Activity;
use App\Models\BusinessUnit;
use App\Models\Brief;
use App\Services\AI\AIAnalysisPipeline;
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

    public function trash(Request $request): Response
    {
        $this->authorize('viewTrash', Brief::class);

        $items = $this->briefs->paginateTrashed(15, $request->only(['search']));

        return Inertia::render('briefs/Trash', [
            'briefs' => BriefResource::collection($items),
            'filters' => $request->only(['search']),
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
            'ai_model' => $validated['ai_model'] ?? config('ai.provider', 'gemini'),
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

        if (config('briefgood.use_queue', true)) {
            AnalyzeBriefJob::dispatch($brief, false, $brief->ai_model);
        } else {
            try {
                $pipeline = app(AIAnalysisPipeline::class);
                $pipeline->run($brief, false, $brief->ai_model);
            } catch (\Throwable $e) {
                // Error is already saved to brief->ai_error by the pipeline
                // Just log it here
                report($e);
            }
        }

        return redirect()->route('briefs.show', $brief);
    }

    public function show(Brief $brief): Response
    {
        $this->authorize('view', $brief);

        $brief = $this->briefs->find($brief->id) ?? $brief;
        $brief->load(['latestAnalysis.recommendations', 'resourceAllocations.resource', 'pitchAssignments.businessUnit', 'pitchAssignments.pic']);

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
                // Also try stripping category suffix like " (Brand Strategy)" from recommendation names
                if (! $recommendation && $assignment->businessUnit?->name) {
                    $cleanName = preg_replace('/\s*\(.*\)$/', '', $assignment->businessUnit->name);
                    $recommendation = $brief->latestAnalysis?->recommendations
                        ->filter(fn ($rec) => preg_replace('/\s*\(.*\)$/', '', $rec->business_unit_name ?? '') === $cleanName)
                        ->first();
                }

                return [
                    'id' => $assignment->id,
                    'business_unit_id' => $assignment->business_unit_id,
                    'status' => $assignment->status?->value,
                    'confidence' => $assignment->confidence,
                    'business_unit' => $assignment->businessUnit?->name,
                    'matched_services' => $recommendation?->matched_services ?? [],
                    'recommendation_confidence' => $recommendation?->confidence,
                    'notified_at' => $assignment->notified_at?->toIso8601String(),
                ];
            }),
            'resourceAllocations' => $brief->resourceAllocations->map(fn ($allocation) => [
                'id' => $allocation->id,
                'resource_name' => $allocation->resource?->name,
                'estimated_hours' => $allocation->estimated_hours,
                'estimated_workload_percent' => $allocation->estimated_workload_percent,
                'estimated_duration_days' => $allocation->estimated_duration_days,
            ]),
            'businessUnits' => BusinessUnit::all()->map(fn ($bu) => [
                'id' => $bu->id,
                'name' => $bu->name,
            ]),
        ]);
    }

    public function preview(Brief $brief): Response
    {
        $this->authorize('view', $brief);

        $brief = $this->briefs->find($brief->id) ?? $brief;
        $brief->load(['latestAnalysis.recommendations', 'resourceAllocations.resource', 'pitchAssignments.businessUnit', 'pitchAssignments.pic']);

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
                    $cleanName = preg_replace('/\s*\(.*\)$/', '', $assignment->businessUnit->name);
                    $recommendation = $brief->latestAnalysis?->recommendations
                        ->filter(fn ($rec) => preg_replace('/\s*\(.*\)$/', '', $rec->business_unit_name ?? '') === $cleanName)
                        ->first();
                }

                return [
                    'id' => $assignment->id,
                    'status' => $assignment->status?->value,
                    'confidence' => $assignment->confidence,
                    'business_unit' => $assignment->businessUnit?->name,
                    'matched_services' => $recommendation?->matched_services ?? [],
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

    public function analyze(Request $request, Brief $brief): RedirectResponse
    {
        $this->authorize('analyze', $brief);

        if (config('briefgood.use_queue', true)) {
            AnalyzeBriefJob::dispatch($brief, $request->boolean('advanced'), $brief->ai_model);
            return back()->with('success', 'AI analysis has been queued.');
        }

        // Run synchronously without queue
        $pipeline = app(AIAnalysisPipeline::class);
        $pipeline->run($brief, $request->boolean('advanced'), $brief->ai_model);

        return back()->with('success', 'AI analysis completed.');
    }

    public function updateAnalysis(UpdateAnalysisRequest $request, Brief $brief): RedirectResponse
    {
        $this->authorize('update', $brief);

        $validated = $request->validated();
        $brief->load(['latestAnalysis']);

        if (! $brief->latestAnalysis) {
            abort(404, 'No analysis found');
        }

        $brief->latestAnalysis->update([
            $validated['field'] => $validated['value'],
        ]);

        return redirect()->back()->with('success', 'Analysis updated');
    }

    public function destroy(Request $request, Brief $brief): RedirectResponse
    {
        $this->authorize('delete', $brief);

        Activity::query()->create([
            'subject_type' => Brief::class,
            'subject_id' => $brief->id,
            'causer_id' => $request->user()->id,
            'event' => 'brief.deleted',
            'description' => "Brief \"{$brief->title}\" moved to trash.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $brief->delete();

        return redirect()->route('briefs.index')->with('success', 'Brief moved to trash.');
    }

    public function restore(Request $request, string $id): RedirectResponse
    {
        $brief = Brief::withTrashed()->findOrFail($id);
        $this->authorize('restore', $brief);

        $brief->restore();

        Activity::query()->create([
            'subject_type' => Brief::class,
            'subject_id' => $brief->id,
            'causer_id' => $request->user()->id,
            'event' => 'brief.restored',
            'description' => "Brief \"{$brief->title}\" restored from trash.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('briefs.trash')->with('success', 'Brief restored successfully.');
    }
}
