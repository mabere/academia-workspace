<?php

namespace App\Services\Ai;

use App\Contracts\AiProvider;
use App\Exceptions\AiUnavailableException;
use App\Models\WritingRevision;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AiGateway
{
    /** @return array<string, mixed> */
    public function generateWritingFeedback(WritingRevision $revision): array
    {
        $this->assertGateOpen();

        $provider = app(AiProvider::class);
        if ($provider->name() !== config('ai.provider')) {
            throw new AiUnavailableException('provider_adapter_mismatch');
        }

        $assignment = $revision->submission->assignment;
        $rubric = $revision->rubricVersion;
        $context = [
            'assignment_title' => $assignment->title,
            'instructions' => $assignment->instructions,
            'feedback_language' => $assignment->feedback_language,
            'feedback_level' => $assignment->feedback_level,
            'rubric' => $rubric?->criteria ?? [],
            'rubric_scale' => $rubric ? ['min' => $rubric->scale_min, 'max' => $rubric->scale_max] : null,
            'prompt_version' => config('ai.prompt_version'),
        ];

        try {
            $output = $provider->generateWritingFeedback($revision->body_text, $context, (int) config('ai.timeout_seconds'));
        } catch (\Throwable) {
            throw new AiUnavailableException('provider_request_failed');
        }

        try {
            return Validator::make($output, [
                'overall_summary' => ['required', 'string', 'max:5000'],
                'criteria_feedback' => ['required', 'array', 'max:12'],
                'criteria_feedback.*.criterion' => ['required', 'string', 'max:120'],
                'criteria_feedback.*.feedback' => ['required', 'string', 'max:1500'],
                'language_issues' => ['required', 'array', 'max:30'],
                'language_issues.*.excerpt' => ['required', 'string', 'max:500'],
                'language_issues.*.explanation' => ['required', 'string', 'max:1000'],
                'language_issues.*.suggestion' => ['required', 'string', 'max:1000'],
                'socratic_questions' => ['required', 'array', 'max:10'],
                'socratic_questions.*' => ['string', 'max:500'],
                'limitations' => ['required', 'array', 'max:10'],
                'limitations.*' => ['string', 'max:500'],
            ])->validate();
        } catch (ValidationException) {
            throw new AiUnavailableException('invalid_provider_output');
        }
    }

    public function isAvailable(): bool
    {
        return $this->unavailableReason() === null;
    }

    private function assertGateOpen(): void
    {
        $reason = $this->unavailableReason();
        if ($reason !== null) {
            throw new AiUnavailableException($reason);
        }
    }

    private function unavailableReason(): ?string
    {
        if (! config('ai.enabled')) {
            return 'disabled';
        }
        if (! config('ai.data_processing_approved')) {
            return 'data_processing_not_approved';
        }
        if (! config('ai.student_text_allowed')) {
            return 'student_text_not_allowed';
        }
        foreach (['ai.provider', 'ai.api_key', 'ai.model'] as $key) {
            if (! filled(config($key))) {
                return 'provider_configuration_incomplete';
            }
        }
        if ((int) config('ai.timeout_seconds') < 1 || (int) config('ai.timeout_seconds') > 60) {
            return 'timeout_configuration_invalid';
        }
        if (! app()->bound(AiProvider::class)) {
            return 'provider_adapter_missing';
        }
        try {
            if (app(AiProvider::class)->name() !== config('ai.provider')) {
                return 'provider_adapter_mismatch';
            }
        } catch (\Throwable) {
            return 'provider_adapter_missing';
        }

        return null;
    }
}
