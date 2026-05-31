<?php

namespace App\Http\Requests\BussinessUnit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BussinessUnitRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
        ];
    }
}
