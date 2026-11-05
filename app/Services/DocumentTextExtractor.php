<?php

namespace App\Services;

use DOMDocument;
use DOMNode;
use DOMXPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Throwable;
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

        if (! file_exists($path) || (! is_readable($path) && @file_get_contents($path, false, null, 0, 1) === false)) {
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
     * Extract text and structured tables from Microsoft Word (.docx) file.
     * Uses native ZipArchive + DOMDocument to preserve paragraphs, headings,
     * lists, and formats tables as standard Markdown tables (| ... |).
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

        try {
            $extracted = $this->parseDocxXml($xml);
            if (trim($extracted) !== '') {
                return trim($extracted);
            }
        } catch (Throwable) {
            // Fallback to regex-based extraction if DOM parsing fails
        }

        return $this->extractFromDocxWithRegexFallback($xml);
    }

    /**
     * Parse Word XML document structure using DOMDocument & DOMXPath.
     * Retains document order of paragraphs (<w:p>), tables (<w:tbl>), and controls (<w:sdt>).
     */
    protected function parseDocxXml(string $xml): string
    {
        $doc = new DOMDocument;
        $doc->preserveWhiteSpace = false;

        $loaded = @$doc->loadXML($xml, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        if (! $loaded) {
            return '';
        }

        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $body = $xpath->query('//w:body')->item(0)
            ?? $xpath->query('//*[local-name()="body"]')->item(0);

        if (! $body) {
            return '';
        }

        $blocks = [];
        $this->extractBlocksFromContainer($body, $xpath, $blocks);

        return implode("\n\n", array_filter($blocks, fn($b) => trim((string) $b) !== ''));
    }

    /**
     * Recursively extract block-level elements (paragraphs, tables, sdt wrappers) from a container.
     */
    protected function extractBlocksFromContainer(DOMNode $container, DOMXPath $xpath, array &$blocks): void
    {
        foreach ($container->childNodes as $child) {
            $localName = $child->localName ?? '';

            if ($localName === 'p') {
                $pText = $this->parseParagraphNode($child, $xpath);
                if ($pText !== null && $pText !== '') {
                    $blocks[] = $pText;
                }
            } elseif ($localName === 'tbl') {
                $tblText = $this->parseTableNode($child, $xpath);
                if ($tblText !== null && $tblText !== '') {
                    $blocks[] = $tblText;
                }
            } elseif ($localName === 'sdt') {
                $contentNodes = $xpath->query('.//w:sdtContent | .//*[local-name()="sdtContent"]', $child);
                foreach ($contentNodes as $sdtContent) {
                    $this->extractBlocksFromContainer($sdtContent, $xpath, $blocks);
                }
            }
        }
    }

    /**
     * Parse a single <w:p> paragraph node.
     * Extracts text runs, tabs, line breaks, detects headings, and bullet/numbered lists.
     */
    protected function parseParagraphNode(DOMNode $pNode, DOMXPath $xpath): ?string
    {
        $styleVal = $xpath->query('w:pPr/w:pStyle/@w:val | *[local-name()="pPr"]/*[local-name()="pStyle"]/@*[local-name()="val"]', $pNode)->item(0)?->nodeValue;
        $headingLevel = 0;
        if ($styleVal) {
            if (preg_match('/heading\s*([1-6])/i', $styleVal, $m)) {
                $headingLevel = (int) $m[1];
            } elseif (preg_match('/^(judul|title)\s*([1-6])?$/i', $styleVal, $m)) {
                $headingLevel = isset($m[2]) && $m[2] !== '' ? (int) $m[2] : 1;
            } elseif (strcasecmp($styleVal, 'subtitle') === 0 || strcasecmp($styleVal, 'subjudul') === 0) {
                $headingLevel = 2;
            }
        }

        $isList = $xpath->query('w:pPr/w:numPr | *[local-name()="pPr"]/*[local-name()="numPr"]', $pNode)->length > 0;

        $text = $this->extractTextFromInlineNodes($pNode, $xpath);
        $trimmed = trim($text);

        if ($trimmed === '') {
            return null;
        }

        if ($headingLevel > 0) {
            return str_repeat('#', $headingLevel) . ' ' . $trimmed;
        }

        if ($isList && ! preg_match('/^(\*|-|\d+[\.\)])\s+/u', $trimmed)) {
            return '- ' . $trimmed;
        }

        return $trimmed;
    }

    /**
     * Parse a <w:tbl> table node and format as a standard Markdown table (| ... |).
     * Handles column spans (gridSpan) and joins multi-line cell paragraphs with <br>.
     */
    protected function parseTableNode(DOMNode $tblNode, DOMXPath $xpath): ?string
    {
        $trNodes = $xpath->query('w:tr | *[local-name()="tr"]', $tblNode);
        if ($trNodes->length === 0) {
            return null;
        }

        $tableRows = [];
        $maxCols = 0;

        foreach ($trNodes as $tr) {
            $rowCells = [];
            $tcNodes = $xpath->query('w:tc | *[local-name()="tc"]', $tr);

            foreach ($tcNodes as $tc) {
                $spanVal = $xpath->query('w:tcPr/w:gridSpan/@w:val | *[local-name()="tcPr"]/*[local-name()="gridSpan"]/@*[local-name()="val"]', $tc)->item(0)?->nodeValue;
                $colSpan = max(1, (int) ($spanVal ?: 1));

                $pNodes = $xpath->query('.//w:p | .//*[local-name()="p"]', $tc);
                $cellParagraphs = [];

                foreach ($pNodes as $p) {
                    $pContent = trim($this->extractTextFromInlineNodes($p, $xpath));
                    if ($pContent !== '') {
                        $cellParagraphs[] = $pContent;
                    }
                }

                if (empty($cellParagraphs)) {
                    $rawText = trim($this->extractTextFromInlineNodes($tc, $xpath));
                    if ($rawText !== '') {
                        $cellParagraphs[] = $rawText;
                    }
                }

                $cellText = implode('<br>', $cellParagraphs);
                $cellText = str_replace('|', '\|', $cellText);
                $cellText = preg_replace('/[\r\n]+/', '<br>', $cellText);

                $rowCells[] = $cellText;

                for ($s = 1; $s < $colSpan; $s++) {
                    $rowCells[] = '';
                }
            }

            if (! empty($rowCells)) {
                $maxCols = max($maxCols, count($rowCells));
                $tableRows[] = $rowCells;
            }
        }

        if (empty($tableRows) || $maxCols === 0) {
            return null;
        }

        $markdownRows = [];

        foreach ($tableRows as $rowIndex => $cells) {
            while (count($cells) < $maxCols) {
                $cells[] = '';
            }

            $markdownRows[] = '| ' . implode(' | ', $cells) . ' |';

            if ($rowIndex === 0) {
                $markdownRows[] = '| ' . implode(' | ', array_fill(0, $maxCols, '---')) . ' |';
            }
        }

        return implode("\n", $markdownRows);
    }

    /**
     * Extract text from inline nodes (runs, tabs, breaks) within a paragraph or cell.
     */
    protected function extractTextFromInlineNodes(DOMNode $node, DOMXPath $xpath): string
    {
        $text = '';
        $query = './/w:t | .//w:tab | .//w:br | .//w:cr | .//w:noBreakHyphen'
            . ' | .//*[local-name()="t"] | .//*[local-name()="tab"] | .//*[local-name()="br"] | .//*[local-name()="cr"] | .//*[local-name()="noBreakHyphen"]';

        $inlineNodes = $xpath->query($query, $node);

        foreach ($inlineNodes as $item) {
            if ($xpath->query('ancestor::w:del | ancestor::*[local-name()="del"]', $item)->length > 0) {
                continue;
            }

            $localName = $item->localName ?? '';

            if ($localName === 't') {
                $text .= $item->nodeValue;
            } elseif ($localName === 'tab') {
                $text .= "\t";
            } elseif ($localName === 'br' || $localName === 'cr') {
                $text .= "\n";
            } elseif ($localName === 'noBreakHyphen') {
                $text .= '-';
            }
        }

        return $text;
    }

    /**
     * Fallback regex-based extraction if DOM XML parsing fails.
     */
    protected function extractFromDocxWithRegexFallback(string $xml): string
    {
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
