<?php

namespace App\Http\Requests;

use App\Models\AiAnalysisResult;
use App\Models\Brief;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        $brief = $this->route('brief');
        return $this->user()?->can('update', $brief);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'field' => ['required', 'string', 'in:executive_summary,brand_overview,campaign_objective,target_audience,scope_of_work,deliverables,timeline,budget,mandatory_requirements'],
            'value' => ['required', 'string'],
        ];
    }
}