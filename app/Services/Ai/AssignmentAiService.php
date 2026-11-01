<?php

namespace App\Services\Ai;

use App\Models\Course;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AssignmentAiService
{
    /**
     * Generate rancangan tugas perkuliahan berbasis AI (DeepSeek, Gemini, atau OpenAI).
     * Menerapkan prinsip Strict Input Adherence, domain-awareness prodi, dan validasi dua lapis.
     *
     * @param  array{
     *     session_id?: int|null,
     *     session_number?: int|null,
     *     meeting_topic?: string|null,
     *     meeting_material?: string|null,
     *     materials_summary?: string|null,
     *     topic?: string|null,
     *     assignment_focus?: string|null,
     *     assignment_type?: string,
     *     bloom_level?: string,
     *     include_rubric?: bool,
     *     notes?: string|null,
     *     provider?: string
     * }  $params
     * @return array{
     *     title: string,
     *     assignment_type: string,
     *     summary: string,
     *     instructions: string,
     *     domain: string,
     *     rubric: array<int, array{criterion: string, weight_percentage: int, description: string}>
     * }
     */
    public function generateAssignmentDraft(Course $course, array $params): array
    {
        $providerKey = strtolower(trim($params['provider'] ?? config('ai.assignment_provider', 'deepseek')));
        $providerConfig = config("ai.providers.{$providerKey}");

        if (! $providerConfig) {
            throw new RuntimeException("AI Provider [{$providerKey}] tidak dikenali dalam sistem.");
        }

        $apiKey = $providerConfig['api_key'] ?? null;
        if (empty($apiKey)) {
            $providerName = $providerConfig['name'] ?? strtoupper($providerKey);
            $envKeyName = strtoupper($providerKey).'_API_KEY';
            throw new RuntimeException("API Key untuk provider {$providerName} belum dikonfigurasi di file .env ({$envKeyName}).");
        }

        // Resolusi Domain Mata Kuliah Pendidikan Bahasa Inggris
        $domain = $this->resolveCourseDomain($course);

        // Susun prompt 3-lapis: Core Strict Rules + Domain Rules + Context Payload
        $promptContext = $this->buildPromptContext($course, $params, $domain);

        $result = match ($providerKey) {
            'deepseek', 'openai' => $this->callOpenAiCompatibleApi($providerKey, $providerConfig, $promptContext),
            'gemini' => $this->callGeminiApi($providerConfig, $promptContext),
            default => throw new RuntimeException("Driver untuk provider [{$providerKey}] belum diimplementasikan."),
        };

        // Validasi dan normalisasi output di sisi PHP
        $validatedOutput = $this->validateAndNormalizeOutput($result, $params);
        $validatedOutput['domain'] = $domain;

        return $validatedOutput;
    }

    /**
     * Mengidentifikasi domain keilmuan mata kuliah (khususnya Prodi Pendidikan Bahasa Inggris).
     * Memprioritaskan nama mata kuliah terlebih dahulu, kemudian deskripsi dan silabus.
     */
    public function resolveCourseDomain(Course $course): string
    {
        $name = strtolower($course->name.' '.$course->code);
        $fullText = strtolower($name.' '.($course->description ?? '').' '.($course->syllabus ?? ''));

        // Tahap 1: Deteksi berbasis Judul/Nama Mata Kuliah (Prioritas Tertinggi)
        if (preg_match('/(listening|speaking|reading|writing|translat|interpret|academic writing|public speaking|essay|paragraph|conversation|menyimak|berbicara|membaca|menulis|penerjemahan)/i', $name)) {
            return 'language_skills';
        }

        if (preg_match('/(linguistic|grammar|syntax|phonol|phonet|morphol|semantic|pragmatic|sociolinguistic|psycholinguistic|discourse|tata bahasa|fonologi|morfologi|sintaksis|semantik)/i', $name)) {
            return 'language';
        }

        if (preg_match('/(literature|literary|prose|poetry|poem|drama|play|novel|short story|cultural|cross-cultural|ccu|budaya|sastra|apresiasi sastra)/i', $name)) {
            return 'literature';
        }

        if (preg_match('/(pedagog|methodolog|teaching|teyl|young learners|curriculum|kurikulum|microteaching|instructional|classroom|pembelajaran|evaluasi pembelajaran|seminar)/i', $name)) {
            return 'pedagogy';
        }

        // Tahap 2: Deteksi berbasis Deskripsi & Silabus Lengkap (Fallback)
        if (preg_match('/(listening|speaking|reading|writing|translat|interpret|academic writing|public speaking|essay|paragraph|conversation|menyimak|berbicara|membaca|menulis|penerjemahan)/i', $fullText)) {
            return 'language_skills';
        }

        if (preg_match('/(linguistic|grammar|syntax|phonol|phonet|morphol|semantic|pragmatic|sociolinguistic|psycholinguistic|discourse|tata bahasa|fonologi|morfologi|sintaksis|semantik)/i', $fullText)) {
            return 'language';
        }

        if (preg_match('/(literature|literary|prose|poetry|poem|drama|play|novel|short story|cultural|cross-cultural|ccu|budaya|sastra|apresiasi sastra)/i', $fullText)) {
            return 'literature';
        }

        if (preg_match('/(pedagog|methodolog|teaching|teyl|young learners|curriculum|kurikulum|microteaching|instructional|classroom|pembelajaran|evaluasi pembelajaran|seminar)/i', $fullText)) {
            return 'pedagogy';
        }

        return 'general';
    }

    /**
     * Label representasi manusiawi untuk domain keilmuan mata kuliah.
     */
    public function domainLabel(string $domain): string
    {
        return match ($domain) {
            'pedagogy' => 'ELT Pedagogy & Methodology',
            'language' => 'English Linguistics & Language Systems',
            'language_skills' => 'English Language Skills & Discourse',
            'literature' => 'Literature & Cultural Studies',
            default => 'General English Education',
        };
    }

    /**
     * Membangun aturan pembatasan spesifik domain untuk meminimalisir halusinasi.
     */
    protected function buildCourseDomainRules(string $domain): string
    {
        return match ($domain) {
            'pedagogy' => <<<'RULE'
DOMAIN: TEACHING, PEDAGOGY & ELT METHODOLOGY
- Gunakan konteks pendekatan/metode pengajaran hanya jika relevan dengan materi pertemuan yang diberikan.
- DILARANG OTOMATIS mewajibkan pembuatan RPP/Lesson Plan, simulasi microteaching, atau modul ajar lengkap KECUALI dosen memintanya secara eksplisit dalam instruksi/catatan.
- Fokuskan penugasan pada analisis kritis materi ajar, evaluasi pendekatan, atau telaah strategi pembelajaran sesuai topik pertemuan.
RULE,

            'language' => <<<'RULE'
DOMAIN: ENGLISH LINGUISTICS & LANGUAGE STRUCTURE
- Fokuskan penugasan pada materi sistem bahasa, kaidah linguistik, atau fenomena kebahasaan yang diajarkan pada pertemuan tersebut.
- DILARANG mengarang data korpus fiktif, transkripsi fonetis palsu, atau mengklaim contoh bahasa berasal dari sumber nyata yang belum disediakan.
- Gunakan data bahasa atau teks yang disediakan dalam input atau minta mahasiswa menganalisis contoh bahasa otentik secara terarah.
RULE,

            'language_skills' => <<<'RULE'
DOMAIN: ENGLISH LANGUAGE SKILLS (LSRW & TRANSLATION)
- Pusatkan penugasan pada keterampilan bahasa yang secara eksplisit menjadi fokus pertemuan.
- DILARANG mengarang keberadaan audio rekaman, artikel jurnal, atau naskah sumber bacaan panjang yang tidak disediakan oleh dosen.
- Jika tugas membutuhkan teks atau stimulus, berikan petunjuk bagi mahasiswa untuk menggunakan teks materi pertemuan atau topik yang telah dipelajari di kelas.
RULE,

            'literature' => <<<'RULE'
DOMAIN: LITERATURE & CULTURAL STUDIES
- Gunakan karya sastra, teks puisi/prosa, atau topik fenomena budaya yang menjadi materi pertemuan.
- DILARANG memilih karya sastra lain di luar silabus/materi, menambahkan kutipan teks fiktif, atau mengarang konteks historis yang tidak diajarkan.
- Arahkan tugas pada analisis tematik, stilistika, atau apresiasi kritis terhadap elemen karya yang relevan.
RULE,

            default => <<<'RULE'
DOMAIN: GENERAL ENGLISH EDUCATION
- Gunakan hanya topik materi pertemuan dan arahan eksplisit dosen.
- Jangan mengasumsikan fokus pembelajaran baru di luar data silabus dan catatan yang diberikan.
RULE,
        };
    }

    /**
     * Membangun konteks prompt terstruktur (System Prompt + User Prompt).
     *
     * @param  array<string, mixed>  $params
     * @return array{system: string, user: string}
     */
    protected function buildPromptContext(Course $course, array $params, string $domain): array
    {
        $domainRules = $this->buildCourseDomainRules($domain);
        $domainTitle = $this->domainLabel($domain);

        $includeRubric = (bool) ($params['include_rubric'] ?? true);
        $rubricGuidance = $includeRubric
            ? 'Buatlah rubrik penilaian berkriteria jelas (3-4 kriteria) dengan total bobot persentase tepat 100%.'
            : 'JANGAN membuat kriteria rubrik penilaian. Kembalikan array kosong [] pada key "rubric".';

        $systemPrompt = <<<PROMPT
Anda adalah Assignment Instruction Executor untuk sistem perkuliahan perguruan tinggi.
Tugas Anda HANYA merumuskan rancangan instruksi penugasan perkuliahan yang terstruktur, jelas, dan operasional berdasarkan parameter eksplisit yang diinputkan oleh dosen.

Anda BUKAN pengambil keputusan akademik. Anda tidak memiliki kewenangan untuk menambah, mengubah, menafsirkan bebas, atau menetapkan syarat beban studi yang tidak diperintahkan oleh dosen.

PRINSIP UTAMA (STRICT EXECUTION & NO INVENTION):
1. STRICT INPUT ADHERENCE:
   Gunakan HANYA topik materi pertemuan, data mata kuliah, CPMK, dan catatan instruksi yang ada dalam input.
   Jangan menambahkan persyaratan berdasarkan kebiasaan umum atau asumsi Anda sendiri.

2. NO INVENTION (DILARANG MENGARANG):
   - DILARANG mengarang data statistik, fakta studi kasus, atau nama institusi fiktif.
   - DILARANG mengarang daftar pustaka, jurnal ilmiah, nama pengarang, artikel, buku, atau DOI fiktif.
   - DILARANG mengarang capaian pembelajaran (CPMK) baru di luar daftar yang diberikan.
   - DILARANG mengarang keberadaan berkas materi/audio yang tidak disediakan dosen.

3. NO UNSOLICITED ADDITIONS:
   Jangan menambahkan komponen berikut KECUALI dosen memintanya secara eksplisit dalam instruksi/catatan:
   - Batasan jumlah halaman / jumlah kata yang mengikat.
   - Kewajiban minimal referensi atau gaya sitasi (APA, MLA, IEEE, dsb.).
   - Kewajiban presentasi di depan kelas atau kerja kelompok jika jenis tugas adalah individu.
   - Batas waktu / tenggat pengerjaan spesifik (deadline ditentukan langsung oleh dosen pada formulir).

4. DOMAIN-SPECIFIC CONSTRAINTS:
{$domainRules}

5. OPERATIONAL LEARNING GUIDANCE:
   Susun instruksi pengerjaan secara terstruktur dan bertahap (langkah 1, langkah 2, dst.) agar mahasiswa memahami apa yang harus dianalisis, dikerjakan, dan diserahkan sesuai target Taksonomi Bloom yang dipilih.

6. RUBRIC CONTROL:
   {$rubricGuidance}

7. FORMATTING RULES (SANGAT KRUSIAL):
   - Nilai pada key "instructions" WAJIB berupa teks langkah-langkah bernomor (1., 2., 3., dst.) di mana SETIAP BUTIR NOMOR DIPISAHKAN OLEH DUA KALI BARIS BARU (\n\n).
   - DILARANG KERAS menggabungkan butir-butir nomor langkah ke dalam satu baris atau satu paragraf bersambung.
   - Contoh format yang wajib dipatuhi:
     1. [Instruksi langkah pertama]

     2. [Instruksi langkah kedua]

     3. [Instruksi langkah ketiga]

KEMBALIKAN RESPON HANYA BERUPA RAW JSON VALID (tanpa pembungkus markdown, tanpa code block ```, tanpa teks tambahan pembuka/penutup) dengan skema persis:
{
  "title": "string (Judul tugas akademik spesifik, formal, dan relevan dengan materi)",
  "assignment_type": "string (salah satu: writing, stimulus_exam, hybrid, translation, milestone_artifact)",
  "summary": "string (Ringkasan esensi tugas dalam 1-2 kalimat padat)",
  "instructions": "string (Instruksi pengerjaan langkah demi langkah yang operasional bagi mahasiswa)",
  "rubric": [
    {
      "criterion": "string (Nama kriteria, misal 'Akurasi Analisis Gramatikal')",
      "weight_percentage": 30,
      "description": "string (Deskripsi standar ekspektasi pencapaian skor maksimal)"
    }
  ]
}
PROMPT;

        // Data Konteks Pertemuan / Sesi
        $sessionNumber = $params['session_number'] ?? null;
        $meetingTopic = $params['meeting_topic'] ?? ($params['topic'] ?? 'Penugasan Materi Perkuliahan');
        $meetingMaterial = $params['meeting_material'] ?? null;
        $materialsSummary = $params['materials_summary'] ?? null;

        $assignmentType = $params['assignment_type'] ?? 'writing';
        $bloomLevel = $params['bloom_level'] ?? 'Analisis (C4)';
        $assignmentFocus = $params['assignment_focus'] ?? null;
        $notes = $params['notes'] ?? '-';

        $cpmkList = $course->cpmkList();
        $cpmkString = ! empty($cpmkList)
            ? implode("\n", array_map(fn ($idx, $item) => ($idx + 1).'. '.$item, array_keys($cpmkList), $cpmkList))
            : ($course->syllabus ?: 'Mengacu pada capaian umum mata kuliah.');

        $sessionHeader = $sessionNumber ? "PERTEMUAN KE-{$sessionNumber}" : 'PERTEMUAN TERKAIT';

        $userPrompt = <<<PROMPT
RANCANG DRAF PENUGASAN BERDASARKAN PARAMETER BERIKUT:

[DATA MATA KULIAH]
- Nama: {$course->name} ({$course->code})
- Bobot: {$course->credit_hours} SKS · Semester: {$course->semester_level}
- Program Studi: {$course->studyProgram->name} ({$course->studyProgram->faculty->name})
- Domain Keilmuan Terdeteksi: {$domainTitle}

[CAPAIAN PEMBELAJARAN MATA KULIAH (CPMK - HANYA SEBAGAI KONTEKS)]
{$cpmkString}

[KONTEKS MATERI {$sessionHeader}]
- Pokok Bahasan Pertemuan: {$meetingTopic}
PROMPT;

        if ($meetingMaterial) {
            $userPrompt .= "\n- Ringkasan Materi & Metode Ajar: {$meetingMaterial}";
        }

        if ($materialsSummary) {
            $userPrompt .= "\n- Bahan Ajar Terkait Sesi: {$materialsSummary}";
        }

        $userPrompt .= <<<PROMPT


[ARAHAN & PARAMETER TUGAS DARI DOSEN]
- Format Model Tugas: {$assignmentType}
- Target Level Kognitif (Taksonomi Bloom): {$bloomLevel}
PROMPT;

        if ($assignmentFocus) {
            $userPrompt .= "\n- Fokus Khusus Penugasan: {$assignmentFocus}";
        }

        $userPrompt .= "\n- Catatan Tambahan Dosen: {$notes}";
        $userPrompt .= "\n- Sertakan Rubrik Penilaian: ".($includeRubric ? 'YA (Pastikan total bobot 100%)' : 'TIDAK (Rubrik array kosong [])');
        $userPrompt .= "\n\nKembalikan HANYA JSON valid sesuai skema yang telah ditentukan.";

        return [
            'system' => $systemPrompt,
            'user' => $userPrompt,
        ];
    }

    /**
     * Memanggil API OpenAI-compatible (DeepSeek / ChatGPT) dengan temperature rendah (deterministik).
     *
     * @param  array<string, mixed>  $config
     * @param  array{system: string, user: string}  $prompts
     * @return array<string, mixed>
     */
    protected function callOpenAiCompatibleApi(string $providerKey, array $config, array $prompts): array
    {
        $baseUrl = rtrim($config['base_url'] ?? 'https://api.deepseek.com', '/');
        $endpoint = $baseUrl.'/chat/completions';
        $apiKey = $config['api_key'];
        $model = $config['model'] ?? ($providerKey === 'deepseek' ? 'deepseek-chat' : 'gpt-4o-mini');
        $timeout = (int) ($config['timeout'] ?? 35);
        $temperature = (float) ($config['temperature'] ?? config('ai.assignment_temperature', 0.2));

        try {
            $response = Http::withToken($apiKey)
                ->timeout($timeout)
                ->post($endpoint, [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $prompts['system']],
                        ['role' => 'user', 'content' => $prompts['user']],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => $temperature,
                ]);

            if (! $response->successful()) {
                $errBody = $response->json();
                $errMsg = $errBody['error']['message'] ?? $response->body();
                Log::error("AI Provider [{$providerKey}] error: {$errMsg}", ['status' => $response->status()]);
                throw new RuntimeException("Gagal menghubungi {$config['name']}: {$errMsg}");
            }

            $rawContent = $response->json('choices.0.message.content');

            return $this->cleanAndParseJson($rawContent);
        } catch (\Throwable $e) {
            Log::error("Exception calling {$providerKey}: ".$e->getMessage());
            throw new RuntimeException($e->getMessage(), previous: $e);
        }
    }

    /**
     * Memanggil Google Gemini REST API dengan pemisahan systemInstruction & temperature rendah.
     *
     * @param  array<string, mixed>  $config
     * @param  array{system: string, user: string}  $prompts
     * @return array<string, mixed>
     */
    protected function callGeminiApi(array $config, array $prompts): array
    {
        $baseUrl = rtrim($config['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta', '/');
        $model = $config['model'] ?? 'gemini-1.5-flash';
        $apiKey = $config['api_key'];
        $endpoint = "{$baseUrl}/models/{$model}:generateContent?key={$apiKey}";
        $timeout = (int) ($config['timeout'] ?? 30);
        $temperature = (float) ($config['temperature'] ?? config('ai.assignment_temperature', 0.2));

        try {
            $payload = [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $prompts['system']],
                    ],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompts['user']],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature' => $temperature,
                ],
            ];

            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout($timeout)
                ->post($endpoint, $payload);

            if (! $response->successful()) {
                $errBody = $response->json();
                $errMsg = $errBody['error']['message'] ?? $response->body();
                Log::error("Gemini API error: {$errMsg}", ['status' => $response->status()]);
                throw new RuntimeException("Gagal menghubungi Google Gemini: {$errMsg}");
            }

            $rawContent = $response->json('candidates.0.content.parts.0.text');

            return $this->cleanAndParseJson($rawContent);
        } catch (\Throwable $e) {
            Log::error('Exception calling Gemini: '.$e->getMessage());
            throw new RuntimeException($e->getMessage(), previous: $e);
        }
    }

    /**
     * Membersihkan pembungkus markdown dan mem-parsing JSON menjadi array PHP mentah.
     *
     * @return array<string, mixed>
     */
    protected function cleanAndParseJson(?string $raw): array
    {
        if (empty($raw)) {
            throw new RuntimeException('AI Provider tidak mengembalikan teks konten yang valid.');
        }

        $cleaned = trim($raw);
        if (str_starts_with($cleaned, '```')) {
            $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $cleaned);
            $cleaned = preg_replace('/\s*```$/', '', $cleaned);
            $cleaned = trim($cleaned);
        }

        $decoded = json_decode($cleaned, true);
        if (! is_array($decoded) || empty($decoded['title']) || empty($decoded['instructions'])) {
            throw new RuntimeException('Format respon JSON dari AI tidak sesuai dengan skema penugasan yang diharapkan.');
        }

        return $decoded;
    }

    /**
     * Memvalidasi dan menormalisasi output AI terhadap parameter permintaan dosen.
     * Menjamin konsistensi assignment_type, kebersihan teks, dan total bobot rubrik.
     *
     * @param  array<string, mixed>  $rawResult
     * @param  array<string, mixed>  $params
     * @return array{
     *     title: string,
     *     assignment_type: string,
     *     summary: string,
     *     instructions: string,
     *     rubric: array<int, array{criterion: string, weight_percentage: int, description: string}>
     * }
     */
    protected function validateAndNormalizeOutput(array $rawResult, array $params): array
    {
        $requestedType = $params['assignment_type'] ?? 'writing';
        $validTypes = ['writing', 'stimulus_exam', 'hybrid', 'translation', 'milestone_artifact'];

        // Enforce assignment_type agar konsisten dengan permintaan dosen
        $outputType = (string) ($rawResult['assignment_type'] ?? $requestedType);
        if (! in_array($outputType, $validTypes, true) || $outputType !== $requestedType) {
            $outputType = $requestedType;
        }

        $title = trim((string) ($rawResult['title'] ?? 'Tugas Perkuliahan'));
        $rawInstructions = trim((string) ($rawResult['instructions'] ?? ''));

        // Normalisasi format penomoran: pastikan setiap nomor (1., 2., 3., dst) dipisahkan oleh baris baru ganda
        $instructions = preg_replace('/(?<!^)(?<!\n)\s+(\d+\.\s+)/', "\n\n$1", $rawInstructions);
        $instructions = preg_replace('/(?<!\n)\s+([a-z]\.\s+)/', "\n   $1", $instructions);
        $instructions = preg_replace("/\n{3,}/", "\n\n", trim($instructions));

        $summary = trim((string) ($rawResult['summary'] ?? ''));

        if (empty($summary)) {
            // Ambil paragraf pertama instruksi sebagai ringkasan darurat jika kosong
            $summary = mb_substr(strip_tags($instructions), 0, 180).'...';
        }

        // Penanganan Rubrik Penilaian
        $includeRubric = (bool) ($params['include_rubric'] ?? true);
        $normalizedRubric = [];

        if ($includeRubric && ! empty($rawResult['rubric']) && is_array($rawResult['rubric'])) {
            $totalWeight = 0;
            foreach ($rawResult['rubric'] as $item) {
                if (! is_array($item) || empty($item['criterion'])) {
                    continue;
                }
                $weight = isset($item['weight_percentage']) ? (int) $item['weight_percentage'] : 25;
                $normalizedRubric[] = [
                    'criterion' => trim((string) $item['criterion']),
                    'weight_percentage' => $weight,
                    'description' => trim((string) ($item['description'] ?? 'Pencapaian standar kriteria penilaian')),
                ];
                $totalWeight += $weight;
            }

            // Normalisasi agar tepat 100% jika ada selisih pembulatan
            if (count($normalizedRubric) > 0 && $totalWeight !== 100) {
                $diff = 100 - $totalWeight;
                $lastIndex = count($normalizedRubric) - 1;
                $normalizedRubric[$lastIndex]['weight_percentage'] += $diff;
            }
        }

        return [
            'title' => $title,
            'assignment_type' => $outputType,
            'summary' => $summary,
            'instructions' => $instructions,
            'rubric' => $normalizedRubric,
        ];
    }
}
