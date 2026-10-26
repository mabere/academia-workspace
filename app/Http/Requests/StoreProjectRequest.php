<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Project::class) ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'academic_period_id' => ['nullable', 'integer', 'exists:academic_periods,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'pillars' => ['required', 'array', 'min:1'],
            'pillars.*' => ['required', 'in:teaching,research,community'],
        ];
    }
}
