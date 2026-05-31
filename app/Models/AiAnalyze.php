<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['brief_id', 'brand_overview', 'project_background','campaign_objective','target_audience',
'product_information','scope_of_work','deliverables','visual_direction','competitor_reference','timeline','budget_estimate',
'mandatory_requirements','success_metrics','approval_flow','recommended_business_units','recommended_services','recommended_resources',
'pitch_complexity','ai_confidence','ai_reasoning','model_used'])]
class AiAnalyze extends Model
{
    //
}
