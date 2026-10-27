<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
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
            'academic_period_id' => ['required', 'exists:academic_periods,id'],
            'study_program_id' => ['required', 'exists:study_programs,id'],
            'code' => ['required', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:160'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:8'],
            'class_group' => ['required', 'string', 'max:32'],
            'schedule_day' => ['nullable', 'string', 'max:20'],
            'schedule_time' => ['nullable', 'string', 'max:32'],
            'room' => ['nullable', 'string', 'max:80'],
            'semester_level' => ['nullable', 'integer', 'min:1', 'max:8'],
            'description' => ['nullable', 'string', 'max:3000'],
            'syllabus' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
