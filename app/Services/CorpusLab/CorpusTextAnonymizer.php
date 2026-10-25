<?php

namespace App\Services\CorpusLab;

class CorpusTextAnonymizer
{
    public function redact(string $text): string
    {
        $text = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/iu', '[EMAIL]', $text) ?? $text;
        $text = preg_replace('/\bhttps?:\/\/[^\s<]+/iu', '[URL]', $text) ?? $text;

        return preg_replace_callback(
            '/(?<![\p{L}\p{N}])\+?[\d][\d\s().-]{7,}\d(?![\p{L}\p{N}])/u',
            fn (array $match) => preg_match_all('/\d/u', $match[0]) >= 8 ? '[NUMBER REDACTED]' : $match[0],
            $text
        ) ?? $text;
    }
}
