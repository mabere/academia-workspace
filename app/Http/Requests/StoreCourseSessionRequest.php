<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourseSessionRequest extends FormRequest
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
            'session_number' => ['required', 'integer', 'min:1', 'max:16'],
            'date' => ['required', 'date'],
            'topic' => ['required', 'string', 'max:255'],
            'teaching_method' => ['nullable', 'string', 'max:80'],
            'attendance_count' => ['required', 'integer', 'min:0'],
            'total_students' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
