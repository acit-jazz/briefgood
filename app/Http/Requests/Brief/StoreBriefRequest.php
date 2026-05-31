<?php

namespace App\Http\Requests\Brief;

use App\Models\Brief;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreBriefRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Brief::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxKb = (int) config('briefgood.uploads.max_size_kb', 20480);

        return [
            'client_name' => ['required', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'brief_date' => ['nullable', 'date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'deadline' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'brief_file' => [
                'required',
                File::types(['pdf'])
                    ->max($maxKb),
            ],
        ];
    }
}
