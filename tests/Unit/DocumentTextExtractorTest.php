<?php

namespace Tests\Unit;

use App\Services\DocumentTextExtractor;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class DocumentTextExtractorTest extends TestCase
{
    private DocumentTextExtractor $extractor;
    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->extractor = new DocumentTextExtractor;
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
        parent::tearDown();
    }

    private function createDocx(string $documentXml): string
    {
        $path = tempnam(sys_get_temp_dir(), 'test_docx_') . '.docx';
        $this->tempFiles[] = $path;

        $zip = new ZipArchive;
        $res = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($res !== true) {
            $this->fail('Failed to create mock docx zip archive.');
        }

        $zip->addFromString('word/document.xml', $documentXml);
        $zip->close();

        return $path;
    }

    public function test_extracts_paragraphs_with_runs_and_tabs(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
            <w:body>
                <w:p>
                    <w:r><w:t>Halo</w:t></w:r>
                    <w:r><w:t xml:space="preserve"> dunia </w:t></w:r>
                    <w:r><w:t>akademik.</w:t></w:r>
                </w:p>
                <w:p>
                    <w:r><w:t>Baris kedua</w:t></w:r>
                </w:p>
            </w:body>
        </w:document>';

        $file = $this->createDocx($xml);
        $text = $this->extractor->extractFromDocx($file);

        $this->assertStringContainsString('Halo dunia akademik.', $text);
        $this->assertStringContainsString('Baris kedua', $text);
    }

    public function test_extracts_headings_and_lists(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
            <w:body>
                <w:p>
                    <w:pPr><w:pStyle w:val="Heading1"/></w:pPr>
                    <w:r><w:t>BAB I PENDAHULUAN</w:t></w:r>
                </w:p>
                <w:p>
                    <w:pPr><w:pStyle w:val="Heading2"/></w:pPr>
                    <w:r><w:t>1.1 Latar Belakang</w:t></w:r>
                </w:p>
                <w:p>
                    <w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr></w:pPr>
                    <w:r><w:t>Poin pertama evaluasi</w:t></w:r>
                </w:p>
            </w:body>
        </w:document>';

        $file = $this->createDocx($xml);
        $text = $this->extractor->extractFromDocx($file);

        $this->assertStringContainsString('# BAB I PENDAHULUAN', $text);
        $this->assertStringContainsString('## 1.1 Latar Belakang', $text);
        $this->assertStringContainsString('- Poin pertama evaluasi', $text);
    }

    public function test_extracts_tables_into_markdown_format(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
            <w:body>
                <w:p><w:r><w:t>Berikut adalah tabel capaian:</w:t></w:r></w:p>
                <w:tbl>
                    <w:tr>
                        <w:tc><w:p><w:r><w:t>No</w:t></w:r></w:p></w:tc>
                        <w:tc><w:p><w:r><w:t>Indikator</w:t></w:r></w:p></w:tc>
                        <w:tc><w:p><w:r><w:t>Nilai | Target</w:t></w:r></w:p></w:tc>
                    </w:tr>
                    <w:tr>
                        <w:tc><w:p><w:r><w:t>1</w:t></w:r></w:p></w:tc>
                        <w:tc><w:p><w:r><w:t>Publikasi Ilmiah</w:t></w:r></w:p></w:tc>
                        <w:tc>
                            <w:p><w:r><w:t>100%</w:t></w:r></w:p>
                            <w:p><w:r><w:t>(Tercapai)</w:t></w:r></w:p>
                        </w:tc>
                    </w:tr>
                </w:tbl>
                <w:p><w:r><w:t>Penjelasan penutup setelah tabel.</w:t></w:r></w:p>
            </w:body>
        </w:document>';

        $file = $this->createDocx($xml);
        $text = $this->extractor->extractFromDocx($file);

        // Verify paragraphs surrounding table
        $this->assertStringContainsString('Berikut adalah tabel capaian:', $text);
        $this->assertStringContainsString('Penjelasan penutup setelah tabel.', $text);

        // Verify Markdown table syntax
        $this->assertStringContainsString('| No | Indikator | Nilai \| Target |', $text);
        $this->assertStringContainsString('| --- | --- | --- |', $text);
        $this->assertStringContainsString('| 1 | Publikasi Ilmiah | 100%<br>(Tercapai) |', $text);
    }

    public function test_handles_table_colspan(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
            <w:body>
                <w:tbl>
                    <w:tr>
                        <w:tc>
                            <w:tcPr><w:gridSpan w:val="2"/></w:tcPr>
                            <w:p><w:r><w:t>Judul Gabungan</w:t></w:r></w:p>
                        </w:tc>
                    </w:tr>
                    <w:tr>
                        <w:tc><w:p><w:r><w:t>Kolom A</w:t></w:r></w:p></w:tc>
                        <w:tc><w:p><w:r><w:t>Kolom B</w:t></w:r></w:p></w:tc>
                    </w:tr>
                </w:tbl>
            </w:body>
        </w:document>';

        $file = $this->createDocx($xml);
        $text = $this->extractor->extractFromDocx($file);

        $this->assertStringContainsString('| Judul Gabungan |  |', $text);
        $this->assertStringContainsString('| --- | --- |', $text);
        $this->assertStringContainsString('| Kolom A | Kolom B |', $text);
    }

    public function test_extracts_content_inside_sdt_containers(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
            <w:body>
                <w:sdt>
                    <w:sdtContent>
                        <w:p><w:r><w:t>Teks di dalam Content Control</w:t></w:r></w:p>
                    </w:sdtContent>
                </w:sdt>
            </w:body>
        </w:document>';

        $file = $this->createDocx($xml);
        $text = $this->extractor->extractFromDocx($file);

        $this->assertStringContainsString('Teks di dalam Content Control', $text);
    }

    public function test_falls_back_to_regex_when_xml_is_malformed(): void
    {
        // Malformed XML (e.g. unclosed tag) but contains w:p text
        $xml = '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Teks Darurat</w:t></w:r></w:p>';

        $file = $this->createDocx($xml);
        $text = $this->extractor->extractFromDocx($file);

        $this->assertStringContainsString('Teks Darurat', $text);
    }
}
