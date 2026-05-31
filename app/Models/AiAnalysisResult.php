<?php

namespace App\Models;

use App\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiAnalysisResult extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'brief_id',
        'executive_summary',
        'brand_overview',
        'campaign_objective',
        'target_audience',
        'scope_of_work',
        'deliverables',
        'timeline',
        'budget',
        'mandatory_requirements',
        'recommended_business_units',
        'recommended_services',
        'recommended_resources',
        'pitch_complexity_score',
        'ai_confidence_score',
        'ai_reasoning',
        'model_used',
        'raw_response',
        'extracted_data',
    ];

    protected function casts(): array
    {
        return [
            'recommended_business_units' => 'array',
            'recommended_services' => 'array',
            'recommended_resources' => 'array',
            'raw_response' => 'array',
            'extracted_data' => 'array',
            'ai_confidence_score' => 'decimal:2',
        ];
    }

    public function brief(): BelongsTo
    {
        return $this->belongsTo(Brief::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(AiRecommendation::class);
    }
}
