<?php

namespace App\Http\Requests\BusinessUnit;

use App\Models\BusinessUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        $unit = $this->route('business_unit');

        return $unit
            ? ($this->user()?->can('update', $unit) ?? false)
            : ($this->user()?->can('create', BusinessUnit::class) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $unitId = $this->route('business_unit')?->id;

        return [
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('business_units', 'slug')->ignore($unitId),
            ],
            'description' => ['nullable', 'string'],
            'pic_user_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['sometimes', 'boolean'],
            'services' => ['nullable', 'array'],
            'services.*.service_id' => ['nullable', 'uuid', 'exists:services,id'],
            'services.*.specialization_score' => ['nullable', 'integer', 'min:1', 'max:100'],
            'services.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
