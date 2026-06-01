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

IMPORTANT SERVICE MATCHING & CONFIDENCE SCORING:
Each business unit has services with a specialization_score (1-100) indicating expertise level:
- Higher specialization_score = more experienced/expert in that service
- When recommending BUs, consider both relevance AND specialization_score
- A BU with high specialization (80+) in matching services should get higher confidence
- If two BUs both offer "Campaign Ideation" but one has specialization 90 and other has 50, recommend the higher one first
- Confidence scores should be DIFFERENTIATED - not all units should have the same score
- High confidence (75-95%): BU has HIGH specialization (70+) in services matching brief requirements
- Medium confidence (55-74%): BU has MEDIUM specialization (40-69%) in some matching services
- Low confidence (30-54%): BU has LOW specialization (<40%) or tangential relevance only
- DO NOT recommend units with confidence below 30%
- Be SELECTIVE - a focused list of 2-4 highly relevant units is better than 10 weakly matched units

Mitigate hallucinations: if information is missing, state "Not specified" rather than inventing facts.
Score pitch complexity 1-10 and AI confidence 0-100 based on clarity of the brief.

CRITICAL HTML FORMATTING RULES - MUST FOLLOW EXACTLY:
For these fields: brand_overview, campaign_objective, target_audience, scope_of_work, deliverables, timeline, budget, mandatory_requirements

DO NOT use markdown formatting. Never use dashes (-), asterisks (*), or numbers followed by periods (1.) at the start of lines.

ALWAYS use proper HTML tags for lists:
- CORRECT: <ul><li><strong>Website redesign</strong> - modernizing the user interface</li><li><em>Migration</em> to a modular CMS platform</li></ul>
- WRONG: - Website redesign - modernizing the user interface
- WRONG: * Website redesign - modernizing the user interface

For bold text: <strong>text</strong> NOT **text** or *text*
For italic text: <em>text</em> NOT _text_ or *text*
For headings: <h3>Section Title</h3> NOT # Section Title

Example correct output for a list field:
<ul>
<li><strong>Technical Migration Strategy</strong>: Developing a migration strategy to preserve SEO rankings</li>
<li><strong>Program Management & Governance</strong>: Managing the project timeline and deliverables</li>
<li><strong>Post-Launch Support</strong>: Providing ongoing support and maintenance (optional)</li>
</ul>

Example WRONG output (do not produce this):
- Technical Migration Strategy: Developing a migration strategy
- Program Management & Governance: Managing the project

Keep output as clean semantic HTML only.

RECOMMENDED RESOURCES MAPPING:
Map each scope of work item to appropriate resources. Each resource should be listed with estimated hours, workload percentage, and duration. Examples:
- "Content & Brand" + "Multimedia Production" → Photographer, Videographer, Motion Designer, Copywriter
- "Design & UX" → UI/UX Designer
- "Technical & CMS" + "SEO" → Frontend Developer, Backend Developer
- "Program Management & Governance" → Project Manager, Strategist

IMPORTANT: List ALL relevant resources needed for the scope. Do not limit to just 2-3 resources. If the scope has 10+ items, return at least 5-8 distinct resources with realistic estimates.
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
