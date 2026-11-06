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

    /**
     * Count words accurately across Unicode / Indonesian / English text and Markdown tables.
     */
    public function countWords(string $text): int
    {
        $clean = preg_replace('/\|[\s\-:]+\|/', ' ', $text);
        $clean = str_replace(['|', '<br>', '#'], ' ', (string) $clean);
        $trimmed = trim((string) $clean);
        if ($trimmed === '') {
            return 0;
        }

        $words = preg_split('/\s+/u', $trimmed, -1, PREG_SPLIT_NO_EMPTY);

        return is_array($words) ? count($words) : 0;
    }

    /**
     * Extract text from a document and automatically allocate it into structured assignment sections.
     * Ignores cover/preamble text appearing before the first detected section marker.
     * Falls back to placing all text into the first section if no section markers are detected.
     *
     * @param  array<int, array<string, mixed>>  $sectionsConfig
     * @return array{
     *     section_texts: array<string, string>,
     *     section_stats: array<string, array{title: string, word_count: int, char_count: int}>,
     *     ignored_preamble: bool,
     *     unmatched: bool,
     *     full_text: string,
     *     total_words: int,
     *     total_chars: int
     * }
     */
    public function extractSections(UploadedFile|string $file, array $sectionsConfig): array
    {
        $fullText = $this->extract($file);
        $normalizedSections = $this->buildNormalizedSectionMatchers($sectionsConfig);

        if (empty($normalizedSections)) {
            $words = $this->countWords($fullText);

            return [
                'section_texts' => [],
                'section_stats' => [],
                'ignored_preamble' => false,
                'unmatched' => true,
                'full_text' => $fullText,
                'total_words' => $words,
                'total_chars' => mb_strlen($fullText),
            ];
        }

        $rawBlocks = preg_split("/\n{2,}/", trim($fullText)) ?: [];
        $blocks = $this->splitMultilineBlocksAtSectionHeaders($rawBlocks, $normalizedSections);

        $allocated = [];
        foreach ($normalizedSections as $sec) {
            $allocated[$sec['key']] = [];
        }

        $currentKey = null;
        $visitedKeys = [];
        $preambleBlocks = [];

        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }

            $match = $this->matchBlockToSection($block, $normalizedSections, $visitedKeys);

            if ($match !== null) {
                $currentKey = $match['key'];
                $visitedKeys[$currentKey] = true;

                if ($match['remainder'] !== '') {
                    $allocated[$currentKey][] = $match['remainder'];
                }
            } else {
                if ($currentKey === null) {
                    $preambleBlocks[] = $block;
                } else {
                    $allocated[$currentKey][] = $block;
                }
            }
        }

        $unmatched = empty($visitedKeys);
        $ignoredPreamble = ! $unmatched && ! empty($preambleBlocks);

        $sectionTexts = [];
        $sectionStats = [];
        $totalWords = 0;
        $totalChars = 0;

        if ($unmatched) {
            $firstKey = $normalizedSections[0]['key'];
            foreach ($normalizedSections as $sec) {
                $key = $sec['key'];
                $text = ($key === $firstKey) ? trim($fullText) : '';
                $wCount = $this->countWords($text);
                $cCount = mb_strlen($text);

                $sectionTexts[$key] = $text;
                $sectionStats[$key] = [
                    'title' => $sec['title'],
                    'word_count' => $wCount,
                    'char_count' => $cCount,
                ];
                $totalWords += $wCount;
                $totalChars += $cCount;
            }
        } else {
            foreach ($normalizedSections as $sec) {
                $key = $sec['key'];
                $text = trim(implode("\n\n", $allocated[$key]));
                $wCount = $this->countWords($text);
                $cCount = mb_strlen($text);

                $sectionTexts[$key] = $text;
                $sectionStats[$key] = [
                    'title' => $sec['title'],
                    'word_count' => $wCount,
                    'char_count' => $cCount,
                ];
                $totalWords += $wCount;
                $totalChars += $cCount;
            }
        }

        return [
            'section_texts' => $sectionTexts,
            'section_stats' => $sectionStats,
            'ignored_preamble' => $ignoredPreamble,
            'unmatched' => $unmatched,
            'full_text' => $fullText,
            'total_words' => $totalWords,
            'total_chars' => $totalChars,
        ];
    }

    /**
     * Prepare normalized matcher metadata for each configured assignment section.
     *
     * @param  array<int, array<string, mixed>>  $sectionsConfig
     * @return array<int, array{
     *     key: string,
     *     title: string,
     *     tokens: array<int, string>,
     *     full_title_norm: string,
     *     core_title_norm: string
     * }>
     */
    protected function buildNormalizedSectionMatchers(array $sectionsConfig): array
    {
        $matchers = [];

        foreach ($sectionsConfig as $sec) {
            $key = trim((string) ($sec['key'] ?? ''));
            if ($key === '') {
                continue;
            }

            $pos = count($matchers);
            $title = trim((string) ($sec['title'] ?? 'Bagian '.($pos + 1)));

            $letter = strtolower(chr(65 + min($pos, 25)));
            $number = (string) ($pos + 1);
            $roman = strtolower($this->toRoman($pos + 1));
            $ordinal = $this->toIndonesianOrdinal($pos + 1);

            $tokens = [$letter, $number, $roman];
            if ($ordinal !== '') {
                $tokens[] = $ordinal;
            }

            if (preg_match('/^(?:part|bagian|section|sub[\s\-]*bagian|soal|tugas)\s*(?:ke[\-\s]*)?([a-z0-9]+)\b/iu', $title, $m)) {
                $explicitToken = strtolower($m[1]);
                array_unshift($tokens, $explicitToken);
            }

            $tokens = array_values(array_unique(array_filter($tokens)));

            $fullTitleNorm = $this->normalizeTitleForComparison($title);

            $stripped = preg_replace(
                '/^(?:(?:part|bagian|section|sub[\s\-]*bagian|soal|tugas)\s*(?:ke[\-\s]*)?[a-z0-9]+|[a-z0-9]{1,3})(?:[\s\:\.\-\–\—\)\/]+|$)/iu',
                '',
                $title
            );
            $coreTitleNorm = $this->normalizeTitleForComparison((string) $stripped);

            $matchers[] = [
                'key' => $key,
                'title' => $title,
                'tokens' => $tokens,
                'full_title_norm' => $fullTitleNorm,
                'core_title_norm' => $coreTitleNorm,
            ];
        }

        return $matchers;
    }

    /**
     * Split non-table multiline paragraphs if an internal line starts a new section header.
     *
     * @param  array<int, string>  $rawBlocks
     * @param  array<int, array<string, mixed>>  $normalizedSections
     * @return array<int, string>
     */
    protected function splitMultilineBlocksAtSectionHeaders(array $rawBlocks, array $normalizedSections): array
    {
        $result = [];
        $simulatedVisited = [];

        foreach ($rawBlocks as $block) {
            $trimmed = trim($block);
            if ($trimmed === '') {
                continue;
            }

            if (str_starts_with($trimmed, '|')) {
                $result[] = $trimmed;
                continue;
            }

            if (! str_contains($trimmed, "\n")) {
                $match = $this->matchLineToSection($trimmed, $normalizedSections, $simulatedVisited);
                if ($match !== null) {
                    $simulatedVisited[$match['key']] = true;
                }
                $result[] = $trimmed;
                continue;
            }

            $lines = explode("\n", $trimmed);
            $currentBuffer = [];

            foreach ($lines as $line) {
                $match = $this->matchLineToSection($line, $normalizedSections, $simulatedVisited);
                if ($match !== null) {
                    if (! empty($currentBuffer)) {
                        $result[] = implode("\n", $currentBuffer);
                        $currentBuffer = [];
                    }
                    $simulatedVisited[$match['key']] = true;
                }
                $currentBuffer[] = $line;
            }

            if (! empty($currentBuffer)) {
                $result[] = implode("\n", $currentBuffer);
            }
        }

        return $result;
    }

    /**
     * Check if a block starts with a section boundary header.
     *
     * @param  array<int, array<string, mixed>>  $normalizedSections
     * @param  array<string, bool>  $visitedKeys
     * @return array{key: string, remainder: string}|null
     */
    protected function matchBlockToSection(string $block, array $normalizedSections, array $visitedKeys): ?array
    {
        if (str_starts_with($block, '|')) {
            return null;
        }

        $lines = explode("\n", $block);
        $firstLine = $lines[0];

        $lineMatch = $this->matchLineToSection($firstLine, $normalizedSections, $visitedKeys);
        if ($lineMatch === null) {
            return null;
        }

        $remainingLines = array_slice($lines, 1);
        $parts = [];
        if ($lineMatch['inline_remainder'] !== '') {
            $parts[] = $lineMatch['inline_remainder'];
        }
        if (! empty($remainingLines)) {
            $restBlock = trim(implode("\n", $remainingLines));
            if ($restBlock !== '') {
                $parts[] = $restBlock;
            }
        }

        return [
            'key' => $lineMatch['key'],
            'remainder' => implode("\n", $parts),
        ];
    }

    /**
     * Match a single line against unvisited section headers.
     *
     * @param  array<int, array<string, mixed>>  $normalizedSections
     * @param  array<string, bool>  $visitedKeys
     * @return array{key: string, inline_remainder: string}|null
     */
    protected function matchLineToSection(string $line, array $normalizedSections, array $visitedKeys): ?array
    {
        $trimmedLine = trim($line);
        if ($trimmedLine === '' || str_starts_with($trimmedLine, '|')) {
            return null;
        }

        $isHeading = (bool) preg_match('/^#{1,6}\s+/u', $trimmedLine);
        $cleanLine = trim((string) preg_replace('/^#{1,6}\s+/u', '', $trimmedLine));
        $cleanLine = trim($cleanLine, "*_ \t");

        if ($cleanLine === '') {
            return null;
        }

        foreach ($normalizedSections as $sec) {
            $key = $sec['key'];
            if (isset($visitedKeys[$key])) {
                continue;
            }

            $tokenAlt = implode('|', array_map(fn ($t) => preg_quote((string) $t, '/'), $sec['tokens']));

            // Rule 1: Explicit Part / Bagian / Section + Identifier (e.g. Part A, Bagian 1, Bagian Pertama)
            $pattern = '/^(?:part|bagian|section|sub[\s\-]*bagian)\s*(?:ke[\-\s]*)?(?<token>' . $tokenAlt . ')\b(?<rest>.*)$/iu';
            if (preg_match($pattern, $cleanLine, $m)) {
                $rest = trim($m['rest']);

                // Case 1A: Standalone "Part A" or "Bagian 1:"
                if ($rest === '' || preg_match('/^[\:\.\-\–\—\)\/]+$/u', $rest)) {
                    return ['key' => $key, 'inline_remainder' => ''];
                }

                // Case 1B: Separated by colon, dash, dot, parenthesis, etc. (e.g. "Part A: Analisis Kesalahan")
                if (preg_match('/^[\:\.\-\–\—\(\)\/]/u', $rest)) {
                    $afterSep = trim((string) preg_replace('/^[\s\:\.\-\–\—\(\)\/]+/u', '', $rest), " )");
                    if ($afterSep === '') {
                        return ['key' => $key, 'inline_remainder' => ''];
                    }

                    $afterSepNorm = $this->normalizeTitleForComparison($afterSep);
                    $coreNorm = $sec['core_title_norm'];

                    if ($coreNorm !== '' && ($afterSepNorm === $coreNorm || str_starts_with($coreNorm, $afterSepNorm))) {
                        return ['key' => $key, 'inline_remainder' => ''];
                    }

                    if ($isHeading || (mb_strlen($afterSep) <= 65 && ! str_ends_with($afterSep, '.'))) {
                        return ['key' => $key, 'inline_remainder' => ''];
                    }

                    return ['key' => $key, 'inline_remainder' => $afterSep];
                }

                // Case 1C: No punctuation separator (only match if Heading or matches section core title)
                $restNorm = $this->normalizeTitleForComparison($rest);
                if (($isHeading && mb_strlen($cleanLine) <= 100) || ($sec['core_title_norm'] !== '' && $restNorm === $sec['core_title_norm'])) {
                    return ['key' => $key, 'inline_remainder' => ''];
                }
            }

            // Rule 2: Exact or single-level numbered match of Lecturer's Section Title
            $coreNorm = $sec['core_title_norm'];
            if ($coreNorm !== '' && mb_strlen($coreNorm) >= 4 && mb_strlen($cleanLine) <= 120) {
                $lineNorm = $this->normalizeTitleForComparison($cleanLine);
                $lineWithoutNum = preg_replace('/^(?:[a-z]|\d+)(?!\.\d)[\.\)\:\-]\s+/iu', '', $cleanLine);
                $lineWithoutNumNorm = $this->normalizeTitleForComparison((string) $lineWithoutNum);

                if ($lineNorm === $sec['full_title_norm'] || $lineNorm === $coreNorm || $lineWithoutNumNorm === $coreNorm) {
                    return ['key' => $key, 'inline_remainder' => ''];
                }
            }
        }

        return null;
    }

    protected function normalizeTitleForComparison(string $value): string
    {
        $lower = mb_strtolower(trim($value), 'UTF-8');
        $clean = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $lower);
        $clean = preg_replace('/\s+/u', ' ', (string) $clean);

        return trim((string) $clean);
    }

    protected function toRoman(int $number): string
    {
        $map = [
            10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I',
        ];
        $result = '';
        foreach ($map as $val => $roman) {
            while ($number >= $val) {
                $result .= $roman;
                $number -= $val;
            }
        }

        return $result ?: 'I';
    }

    protected function toIndonesianOrdinal(int $number): string
    {
        return match ($number) {
            1 => 'pertama',
            2 => 'kedua',
            3 => 'ketiga',
            4 => 'keempat',
            5 => 'kelima',
            6 => 'keenam',
            7 => 'ketujuh',
            8 => 'kedelapan',
            9 => 'kesembilan',
            10 => 'kesepuluh',
            11 => 'kesebelas',
            12 => 'keduabelas',
            default => '',
        };
    }
}
