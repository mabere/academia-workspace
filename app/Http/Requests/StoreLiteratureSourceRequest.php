<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreLiteratureSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Project|null $project */
        $project = $this->route('project');

        return $project !== null
            && ($this->user()?->can('manageAssignments', $project) ?? false);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:'.config('litsynth.max_pdf_kilobytes', 25600)],
            'title' => ['nullable', 'string', 'max:500'],
        ];
    }
}
