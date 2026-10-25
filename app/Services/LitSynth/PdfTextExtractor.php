<?php

namespace App\Services\LitSynth;

use Illuminate\Support\Facades\Process;
use RuntimeException;

class PdfTextExtractor
{
    /** @return array<int, string> */
    public function extractPages(string $absolutePath): array
    {
        $binary = (string) config('litsynth.pdftotext_binary', 'pdftotext');
        $result = Process::timeout(120)->run([$binary, '-layout', '-enc', 'UTF-8', $absolutePath, '-']);

        if (! $result->successful()) {
            $message = trim($result->errorOutput()) ?: trim($result->output());
            throw new RuntimeException($message !== '' ? $message : 'pdftotext tidak dapat mengekstrak dokumen.');
        }

        $text = str_replace(["\r\n", "\r"], "\n", $result->output());
        $pages = array_map('trim', explode("\f", $text));

        while ($pages !== [] && end($pages) === '') {
            array_pop($pages);
        }

        if ($pages === [] || ! collect($pages)->contains(fn (string $page) => $page !== '')) {
            throw new RuntimeException('PDF tidak berisi teks yang dapat diekstrak. Dokumen mungkin hasil scan dan memerlukan OCR.');
        }

        return array_values($pages);
    }
}
