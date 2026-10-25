<?php

namespace App\Contracts;

interface AiProvider
{
    public function name(): string;

    public function model(): string;

    /** @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function generateWritingFeedback(string $studentText, array $context, int $timeoutSeconds): array;
}
