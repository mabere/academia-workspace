<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use ZipArchive;

class DocumentTextExtractor
{
    /**
     * Extract plain text from an uploaded file or file path.
     * Supported formats: docx, pdf, txt, md.
     */
    public function extract(UploadedFile|string $file): string
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $extension = strtolower($file instanceof UploadedFile ? $file->getClientOriginalExtension() : pathinfo($path, PATHINFO_EXTENSION));

        if (! file_exists($path) || ! is_readable($path)) {
            throw new RuntimeException('Berkas tidak ditemukan atau tidak dapat dibaca.');
        }

        return match ($extension) {
            'docx' => $this->extractFromDocx($path),
            'pdf' => $this->extractFromPdf($path),
            'txt', 'text', 'md' => $this->extractFromPlainText($path),
            default => throw new RuntimeException("Format berkas .{$extension} tidak didukung. Harap gunakan format .docx, .pdf, atau .txt."),
        };
    }

    /**
     * Extract text from Microsoft Word (.docx) file using native ZipArchive.
     */
    public function extractFromDocx(string $filePath): string
    {
        if (! class_exists('ZipArchive')) {
            throw new RuntimeException('Ekstensi PHP ZipArchive tidak aktif pada server.');
        }

        $zip = new ZipArchive;
        $opened = $zip->open($filePath);

        if ($opened !== true) {
            throw new RuntimeException('Berkas Word (.docx) tidak valid atau rusak.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false || trim($xml) === '') {
            throw new RuntimeException('Dokumen Word tidak memiliki teks isi (word/document.xml kosong).');
        }

        // Preserve paragraph breaks
        $textWithBreaks = preg_replace('/<w:p[^>]*>/i', "\n\n", $xml);
        // Preserve line breaks within paragraphs
        $textWithBreaks = preg_replace('/<w:br[^>]*>/i', "\n", $textWithBreaks);
        // Strip XML tags
        $cleanText = strip_tags($textWithBreaks);
        // Decode HTML/XML entities
        $cleanText = html_entity_decode($cleanText, ENT_QUOTES | ENT_XML1, 'UTF-8');

        // Normalize excess line breaks
        $cleanText = preg_replace("/\n{3,}/", "\n\n", $cleanText);

        $result = trim($cleanText);

        if ($result === '') {
            throw new RuntimeException('Dokumen Word tidak berisi teks yang dapat diekstrak.');
        }

        return $result;
    }

    /**
     * Extract text from PDF using pdftotext binary or pure-PHP stream decoder fallback.
     */
    public function extractFromPdf(string $filePath): string
    {
        // 1. Try pdftotext CLI binary first (standard on Linux VPS with poppler-utils)
        $binary = (string) config('litsynth.pdftotext_binary', 'pdftotext');
        try {
            $process = Process::timeout(30)->run([$binary, '-layout', '-enc', 'UTF-8', $filePath, '-']);
            if ($process->successful() && trim($process->output()) !== '') {
                $text = str_replace(["\r\n", "\r"], "\n", $process->output());
                $text = preg_replace("/\n{3,}/", "\n\n", $text);

                return trim($text);
            }
        } catch (\Throwable) {
            // Fallback to pure PHP extraction
        }

        // 2. Pure PHP fallback parser for digital PDF streams
        $content = @file_get_contents($filePath);
        if ($content === false || $content === '') {
            throw new RuntimeException('Berkas PDF tidak dapat dibaca.');
        }

        $extracted = '';

        if (preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $content, $matches)) {
            foreach ($matches[1] as $stream) {
                $data = @gzuncompress($stream);
                if ($data === false) {
                    $data = $stream;
                }

                if (preg_match_all('/BT[\r\n]+(.*?)[\r\n]+ET/s', $data, $blocks)) {
                    foreach ($blocks[1] as $block) {
                        if (preg_match_all('/\((.*?)\)\s*Tj/s', $block, $tjMatches)) {
                            $extracted .= implode(' ', $tjMatches[1])."\n";
                        }
                        if (preg_match_all('/\[(.*?)\]\s*TJ/s', $block, $tjArrayMatches)) {
                            foreach ($tjArrayMatches[1] as $arr) {
                                if (preg_match_all('/\((.*?)\)/s', $arr, $partMatches)) {
                                    $extracted .= implode('', $partMatches[1]).' ';
                                }
                            }
                            $extracted .= "\n";
                        }
                    }
                }
            }
        }

        if (trim($extracted) === '') {
            if (preg_match_all('/\(([^\(\)]{4,})\)\s*Tj/', $content, $simpleMatches)) {
                $extracted = implode("\n", $simpleMatches[1]);
            }
        }

        $clean = trim($extracted);
        if ($clean === '') {
            throw new RuntimeException('PDF tidak berisi teks digital yang dapat diekstrak. Dokumen mungkin hasil pindaian gambar (scan) yang memerlukan OCR.');
        }

        return $clean;
    }

    /**
     * Extract text from plain text or markdown file.
     */
    public function extractFromPlainText(string $filePath): string
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new RuntimeException('Gagal membaca berkas teks.');
        }

        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        }

        return trim($content);
    }
}
