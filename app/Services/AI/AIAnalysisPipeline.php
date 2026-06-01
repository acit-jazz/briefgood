<?php

namespace App\Services\AI;

use App\DTOs\AI\BriefAnalysisInputDto;
use App\DTOs\AI\BriefAnalysisResultDto;
use App\Enums\AiAnalysisStatus;
use App\Enums\BriefStatus;
use App\Enums\PitchAssignmentStatus;
use App\Events\BriefAnalysisCompleted;
use App\Events\BriefAnalysisFailed;
use App\Events\BriefAnalysisStarted;
use App\Mail\PitchAssignmentCreated;
use App\Models\Activity;
use App\Models\AiAnalysisResult;
use App\Models\Brief;
use App\Models\BusinessUnit;
use App\Models\PitchAssignment;
use App\Models\Resource;
use App\Models\ResourceAllocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser as PdfParser;

class AIAnalysisPipeline
{
    public function __construct(
        protected AIOrchestratorService $orchestrator,
        protected BusinessMatchingService $matchingService,
    ) {}

    public function run(Brief $brief, bool $useAdvancedModel = false): AiAnalysisResult
    {
        event(new BriefAnalysisStarted($brief));

        $brief->update([
            'ai_status' => AiAnalysisStatus::Processing,
            'ai_error' => null,
        ]);

        try {
            $input = $this->buildInput($brief);
            $result = $this->orchestrator->analyze($input, $useAdvancedModel);

            return DB::transaction(function () use ($brief, $result): AiAnalysisResult {
                $analysis = $this->persistAnalysis($brief, $result);
                $this->matchingService->syncRecommendations($analysis, $result);
                $analysis->load('recommendations');
                $this->syncResourceAllocations($brief, $result);
                $this->distributePitchAssignments($brief, $analysis);

                $brief->update([
                    'ai_status' => AiAnalysisStatus::Completed,
                    'status' => BriefStatus::Reviewing,
                    'latest_analysis_id' => $analysis->id,
                ]);

                Activity::query()->create([
                    'subject_type' => Brief::class,
                    'subject_id' => $brief->id,
                    'causer_id' => $brief->created_by,
                    'event' => 'ai.analysis.completed',
                    'description' => 'AI analysis completed successfully.',
                    'properties' => [
                        'confidence' => $result->aiConfidenceScore,
                        'complexity' => $result->pitchComplexityScore,
                    ],
                ]);

                event(new BriefAnalysisCompleted($brief, $analysis));

                return $analysis;
            });
        } catch (\Throwable $exception) {
            $brief->update([
                'ai_status' => AiAnalysisStatus::Failed,
                'ai_error' => Str::limit($exception->getMessage(), 500),
            ]);

            event(new BriefAnalysisFailed($brief, $exception));

            throw $exception;
        }
    }

    protected function buildInput(Brief $brief): BriefAnalysisInputDto
    {
        $units = BusinessUnit::query()
            ->with(['category', 'services'])
            ->active()
            ->get()
            ->map(fn (BusinessUnit $unit) => [
                'id' => $unit->id,
                'name' => $unit->name,
                'category' => $unit->category?->name ?? 'General',
                'services' => $unit->services->pluck('name')->all(),
            ])
            ->all();

        return new BriefAnalysisInputDto(
            briefId: $brief->id,
            title: $brief->title,
            clientName: $brief->client_name,
            industry: $brief->industry,
            notes: $brief->notes,
            briefText: $this->extractBriefText($brief),
            businessUnits: $units,
        );
    }

    protected function extractBriefText(Brief $brief): string
    {
        $parts = array_filter([
            $brief->notes,
        ]);

        if ($brief->primary_file_path && Storage::disk('local')->exists($brief->primary_file_path)) {
            $path = Storage::disk('local')->path($brief->primary_file_path);
            $mime = $brief->primary_file_mime ?? '';

            if (str_contains($mime, 'pdf') || str_ends_with($path, '.pdf')) {
                try {
                    $parser = new PdfParser;
                    $parts[] = $parser->parseFile($path)->getText();
                } catch (\Throwable) {
                    $parts[] = 'PDF content could not be extracted. Use metadata and notes only.';
                }
            }
        }

        return trim(implode("\n\n", $parts)) ?: 'No brief content provided.';
    }

    protected function persistAnalysis(Brief $brief, BriefAnalysisResultDto $result): AiAnalysisResult
    {
        return AiAnalysisResult::query()->create([
            'brief_id' => $brief->id,
            'executive_summary' => $result->executiveSummary,
            'brand_overview' => $result->brandOverview,
            'campaign_objective' => $result->campaignObjective,
            'target_audience' => $result->targetAudience,
            'scope_of_work' => $result->scopeOfWork,
            'deliverables' => $result->deliverables,
            'timeline' => $result->timeline,
            'budget' => $result->budget,
            'mandatory_requirements' => $result->mandatoryRequirements,
            'recommended_business_units' => $result->recommendedBusinessUnits,
            'recommended_services' => $result->recommendedServices,
            'recommended_resources' => $result->recommendedResources,
            'pitch_complexity_score' => $result->pitchComplexityScore,
            'ai_confidence_score' => $result->aiConfidenceScore,
            'ai_reasoning' => $result->aiReasoning,
            'model_used' => $result->modelUsed,
            'raw_response' => $result->rawResponse,
        ]);
    }

    protected function syncResourceAllocations(Brief $brief, BriefAnalysisResultDto $result): void
    {
        ResourceAllocation::query()->where('brief_id', $brief->id)->delete();

        foreach ($result->recommendedResources as $resourceData) {
            $resource = Resource::query()
                ->where('slug', Str::slug($resourceData['name']))
                ->orWhere('name', $resourceData['name'])
                ->first();

            if (! $resource) {
                continue;
            }

            ResourceAllocation::query()->create([
                'brief_id' => $brief->id,
                'resource_id' => $resource->id,
                'estimated_hours' => $resourceData['estimated_hours'] ?? null,
                'estimated_workload_percent' => $resourceData['workload_percent'] ?? null,
                'estimated_duration_days' => $resourceData['duration_days'] ?? null,
            ]);
        }
    }

    protected function distributePitchAssignments(Brief $brief, AiAnalysisResult $analysis): void
    {
        foreach ($analysis->recommendations as $recommendation) {
            $unit = BusinessUnit::query()
                ->where('id', $recommendation->business_unit_id)
                ->orWhere('name', $recommendation->business_unit_name)
                ->first();

            if (! $unit) {
                continue;
            }

            $assignment = PitchAssignment::query()->updateOrCreate(
                [
                    'brief_id' => $brief->id,
                    'business_unit_id' => $unit->id,
                ],
                [
                    'assigned_by' => $brief->created_by,
                    'pic_user_id' => $unit->pic_user_id,
                    'confidence' => $recommendation->confidence,
                    'status' => PitchAssignmentStatus::Pending,
                ],
            );

            // Send email notification to PIC if they have an email
            $this->sendAssignmentNotification($assignment, $brief);
        }

        $brief->update(['status' => BriefStatus::Assigned]);
    }

    protected function sendAssignmentNotification(PitchAssignment $assignment, Brief $brief): void
    {
        if (! $assignment->pic || ! $assignment->pic->email) {
            Log::info('PitchAssignment email skipped: no PIC email', [
                'assignment_id' => $assignment->id,
                'brief_id' => $brief->id,
            ]);

            return;
        }

        try {
            Mail::to($assignment->pic->email)->send(new PitchAssignmentCreated($assignment, $brief));

            Log::info('PitchAssignment email sent', [
                'assignment_id' => $assignment->id,
                'pic_email' => $assignment->pic->email,
                'brief_id' => $brief->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send PitchAssignment email', [
                'assignment_id' => $assignment->id,
                'pic_email' => $assignment->pic->email,
                'brief_id' => $brief->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
