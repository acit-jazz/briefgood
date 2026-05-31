<?php

namespace App\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class BriefAnalysisAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
You are BriefGood's enterprise brief analyst for a multi-business-unit agency holding group.

Analyze client RFP/brief content and produce accurate structured output. Map needs to the provided business unit catalog only.
Mitigate hallucinations: if information is missing, state "Not specified" rather than inventing facts.
Score pitch complexity 1-10 and AI confidence 0-100 based on clarity of the brief.
INSTRUCTIONS;
    }

    public function schema(JsonSchema $schema): array
    {
        $recommendation = $schema->object([
            'name' => $schema->string()->required(),
            'confidence' => $schema->number()->required(),
            'services' => $schema->array()->items($schema->string())->required(),
            'reasoning' => $schema->string()->required(),
        ]);

        $resource = $schema->object([
            'name' => $schema->string()->required(),
            'estimated_hours' => $schema->number()->required(),
            'workload_percent' => $schema->integer()->required(),
            'duration_days' => $schema->integer()->required(),
        ]);

        return [
            'executive_summary' => $schema->string()->required(),
            'brand_overview' => $schema->string()->required(),
            'campaign_objective' => $schema->string()->required(),
            'target_audience' => $schema->string()->required(),
            'scope_of_work' => $schema->string()->required(),
            'deliverables' => $schema->string()->required(),
            'timeline' => $schema->string()->required(),
            'budget' => $schema->string()->required(),
            'mandatory_requirements' => $schema->string()->required(),
            'recommended_business_units' => $schema->array()->items($recommendation)->required(),
            'recommended_services' => $schema->array()->items($schema->string())->required(),
            'recommended_resources' => $schema->array()->items($resource)->required(),
            'pitch_complexity_score' => $schema->integer()->min(1)->max(10)->required(),
            'ai_confidence_score' => $schema->number()->min(0)->max(100)->required(),
            'ai_reasoning' => $schema->string()->required(),
        ];
    }
}
