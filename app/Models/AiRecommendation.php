<?php

namespace App\Models;

use App\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRecommendation extends Model
{
    use HasUuid;

    protected $fillable = [
        'ai_analysis_result_id',
        'business_unit_id',
        'business_unit_name',
        'confidence',
        'matched_services',
        'reasoning',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'decimal:2',
            'matched_services' => 'array',
        ];
    }

    public function analysisResult(): BelongsTo
    {
        return $this->belongsTo(AiAnalysisResult::class, 'ai_analysis_result_id');
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }
}
