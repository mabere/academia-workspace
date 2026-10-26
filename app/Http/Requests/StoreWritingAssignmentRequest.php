<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreWritingAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Project|null $project */
        $project = $this->route('project');

        return $project !== null && ($this->user()?->can('manageAssignments', $project) ?? false);
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
            'rubric_name' => ['sometimes', 'required', 'string', 'max:120'],
            'scale_min' => ['sometimes', 'required', 'integer', 'min:0', 'max:100'],
            'scale_max' => ['sometimes', 'required', 'integer', 'gt:scale_min', 'max:100'],
            'criteria' => ['sometimes', 'required', 'array', 'min:1', 'max:12'],
            'criteria.*.name' => ['required_with:criteria', 'string', 'max:120'],
            'criteria.*.description' => ['nullable', 'string', 'max:1000'],
            'criteria.*.weight' => ['required_with:criteria', 'numeric', 'gt:0', 'max:100'],
        ];
    }
}
