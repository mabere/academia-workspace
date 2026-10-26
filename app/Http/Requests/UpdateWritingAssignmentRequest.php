<?php

namespace App\Http\Requests;

use App\Models\WritingAssignment;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWritingAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var WritingAssignment|null $assignment */
        $assignment = $this->route('assignment');

        return $assignment !== null && ($this->user()?->can('update', $assignment) ?? false);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'instructions' => ['required', 'string', 'max:10000'],
            'feedback_language' => ['required', 'in:id,en'],
            'feedback_level' => ['required', 'in:indirect,direct,socratic'],
            'status' => ['required', 'in:draft,published'],
            'student_emails' => ['nullable', 'string', 'max:5000'],
            'due_at' => ['nullable', 'date'],
            'rubric_name' => ['required', 'string', 'max:120'],
            'scale_min' => ['required', 'integer', 'min:0', 'max:100'],
            'scale_max' => ['required', 'integer', 'gt:scale_min', 'max:100'],
            'criteria' => ['required', 'array', 'min:1', 'max:12'],
            'criteria.*.name' => ['required', 'string', 'max:120'],
            'criteria.*.description' => ['nullable', 'string', 'max:1000'],
            'criteria.*.weight' => ['required', 'numeric', 'gt:0', 'max:100'],
        ];
    }
}
