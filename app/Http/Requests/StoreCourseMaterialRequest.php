<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourseMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'session_number' => ['nullable', 'integer', 'min:1', 'max:16'],
            'material_type' => ['required', 'in:document,audio,video,link,text,archive'],
            'file' => [
                'nullable',
                'file',
                'max:102400', // 100 MB
            ],
            'external_url' => ['nullable', 'url', 'max:2000'],
            'content_text' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
