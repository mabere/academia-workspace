<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\Faculty;
use App\Models\LecturerProfile;
use App\Models\StudyProgram;
use App\Models\User;
use App\Models\WritingAssignment;
use App\Models\WritingSubmission;
use App\Services\DocumentTextExtractor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use ZipArchive;

class DocumentExtractionTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;

    private User $student;

    private Course $course;

    private WritingAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        $faculty = Faculty::create(['name' => 'Fakultas Keguruan dan Ilmu Pendidikan', 'code' => 'FKIP']);
        $studyProgram = StudyProgram::create([
            'faculty_id' => $faculty->id,
            'name' => 'Pendidikan Bahasa Inggris',
            'code' => 'PBI-S1',
            'level' => 'S1',
        ]);

        $this->lecturer = User::factory()->create(['name' => 'Dr. Budi Santoso', 'role' => 'lecturer']);
        LecturerProfile::create([
            'user_id' => $this->lecturer->id,
            'study_program_id' => $studyProgram->id,
            'nidn' => '0012058001',
            'target_sks' => 12,
        ]);

        $period = AcademicPeriod::create([
            'name' => '2026/2027 Ganjil',
            'academic_year' => '2026/2027',
            'semester' => 'odd',
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-31',
            'is_active' => true,
        ]);

        $this->course = Course::create([
            'academic_period_id' => $period->id,
            'study_program_id' => $studyProgram->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG201',
            'name' => 'Academic Writing',
            'credit_hours' => 3,
            'class_group' => 'A',
        ]);

        $this->student = User::factory()->create([
            'name' => 'Siti Rahma',
            'role' => 'student',
            'nim' => '2101010045',
            'study_program_id' => $studyProgram->id,
        ]);

        CourseStudent::create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'nim' => '2101010045',
            'attendance_rate' => 100.0,
        ]);

        // Buat tugas penulisan
        $this->actingAs($this->lecturer)->post(route('courses.assignments.store', $this->course), [
            'title' => 'Esai Analisis Wacana Kritis',
            'instructions' => 'Susun analisis wacana kritis artikel berita minimal 500 kata.',
        ]);

        $this->assignment = WritingAssignment::where('course_id', $this->course->id)->firstOrFail();
    }

    private function createFakeDocx(string $paragraph1, string $paragraph2): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'test_docx_').'.docx';
        $zip = new ZipArchive;
        $zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:body>'
            .'<w:p><w:r><w:t>'.htmlspecialchars($paragraph1, ENT_XML1).'</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>'.htmlspecialchars($paragraph2, ENT_XML1).'</w:t></w:r></w:p>'
            .'</w:body>'
            .'</w:document>';

        $zip->addFromString('word/document.xml', $xml);
        $zip->close();

        return new UploadedFile(
            $tempPath,
            'esai_akademik_siti.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true
        );
    }

    public function test_extractor_service_can_extract_text_from_docx_file(): void
    {
        $file = $this->createFakeDocx(
            'Analisis Struktur Argumen dalam Teks Akademik Mahasiswa',
            'Penelitian ini menerapkan model Toulmin untuk mengidentifikasi klaim, data, dan pembenaran.'
        );

        $extractor = new DocumentTextExtractor;
        $extracted = $extractor->extract($file);

        $this->assertStringContainsString('Analisis Struktur Argumen', $extracted);
        $this->assertStringContainsString('model Toulmin', $extracted);
    }

    public function test_student_can_extract_document_via_ajax_endpoint(): void
    {
        $file = $this->createFakeDocx(
            'Pengantar Teori Linguistik Terapan',
            'Kajian ini berfokus pada variasi sintaksis dalam penulisan ilmiah bahasa Inggris.'
        );

        $response = $this->actingAs($this->student)
            ->postJson(route('assignments.extract-document'), [
                'document_file' => $file,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'filename' => 'esai_akademik_siti.docx',
        ]);
        $response->assertJsonPath('success', true);
        $this->assertStringContainsString('Pengantar Teori Linguistik', $response->json('text'));
        $this->assertGreaterThan(5, $response->json('word_count'));
    }

    public function test_student_can_submit_revision_using_uploaded_docx_file_directly(): void
    {
        $file = $this->createFakeDocx(
            'Naskah Lengkap Draf Tugas Analisis Wacana',
            'Bagian ini memaparkan metodologi kualitatif deskriptif yang digunakan untuk menganalisis teks editorial.'
        );

        $response = $this->actingAs($this->student)
            ->post(route('assignments.revisions.store', $this->assignment), [
                'document_file' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $submission = WritingSubmission::where('assignment_id', $this->assignment->id)
            ->where('student_id', $this->student->id)
            ->firstOrFail();

        $revision = $submission->revisions()->firstOrFail();
        $this->assertStringContainsString('Naskah Lengkap Draf Tugas Analisis Wacana', $revision->body_text);
        $this->assertStringContainsString('metodologi kualitatif deskriptif', $revision->body_text);
    }

    public function test_invalid_file_extension_rejected_by_extract_endpoint(): void
    {
        $fakeFile = UploadedFile::fake()->create('foto_catatan.png', 500, 'image/png');

        $response = $this->actingAs($this->student)
            ->postJson(route('assignments.extract-document'), [
                'document_file' => $fakeFile,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('document_file');
    }
}
