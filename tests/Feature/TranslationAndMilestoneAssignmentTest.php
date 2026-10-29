<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\Faculty;
use App\Models\LecturerProfile;
use App\Models\Project;
use App\Models\StudyProgram;
use App\Models\User;
use App\Models\WritingAssignment;
use App\Models\WritingSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationAndMilestoneAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;

    private User $student;

    private Course $translationCourse;

    private Course $seminarCourse;

    private Project $project;

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

        $this->lecturer = User::factory()->create(['role' => 'lecturer', 'name' => 'Dr. Ratna Sari']);
        LecturerProfile::create([
            'user_id' => $this->lecturer->id,
            'study_program_id' => $studyProgram->id,
            'nidn' => '0055443322',
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

        // Mata Kuliah 1: Introduction to Translating-Interpretation (ENG6545)
        $this->translationCourse = Course::create([
            'academic_period_id' => $period->id,
            'study_program_id' => $studyProgram->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG6545',
            'name' => 'Introduction to Translating-Interpretation',
            'credit_hours' => 3,
            'class_group' => 'A - 2024',
        ]);

        // Mata Kuliah 2: Seminar in English Language Teaching (ENG6549)
        $this->seminarCourse = Course::create([
            'academic_period_id' => $period->id,
            'study_program_id' => $studyProgram->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG6549',
            'name' => 'Seminar in English Language Teaching',
            'credit_hours' => 3,
            'class_group' => 'A - 2023',
        ]);

        $this->student = User::factory()->create([
            'role' => 'student',
            'name' => 'Dewi Lestari',
            'nim' => '2101010055',
            'study_program_id' => $studyProgram->id,
        ]);

        CourseStudent::create([
            'course_id' => $this->translationCourse->id,
            'student_id' => $this->student->id,
            'nim' => '2101010055',
            'attendance_rate' => 100.0,
        ]);

        CourseStudent::create([
            'course_id' => $this->seminarCourse->id,
            'student_id' => $this->student->id,
            'nim' => '2101010055',
            'attendance_rate' => 100.0,
        ]);

        $this->project = Project::create([
            'owner_id' => $this->lecturer->id,
            'name' => 'Tri Dharma Pengajaran - '.$this->translationCourse->code,
            'status' => 'active',
            'pillars' => ['teaching'],
        ]);
    }

    public function test_lecturer_can_create_translation_assignment_with_source_text(): void
    {
        $sourceText = 'Pendidikan karakter memegang peranan krusial dalam membentuk integritas generasi muda di era disrupsi digital saat ini.';

        $response = $this->actingAs($this->lecturer)->post(route('courses.assignments.store', $this->translationCourse), [
            'title' => 'Tugas Penerjemahan Teks Editorial Opini',
            'instructions' => 'Terjemahkan teks editorial berikut ke dalam bahasa Inggris akademik dengan memperhatikan padanan istilah kebudayaan.',
            'assignment_type' => 'translation',
            'source_language' => 'Bahasa Indonesia',
            'target_language' => 'English',
            'source_text' => $sourceText,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $assignment = WritingAssignment::where('course_id', $this->translationCourse->id)->firstOrFail();
        $this->assertTrue($assignment->isTranslation());
        $this->assertEquals('Penerjemahan Teks', $assignment->typeLabel());
        $this->assertEquals('Bahasa Indonesia', $assignment->config_json['source_language']);
        $this->assertEquals('English', $assignment->config_json['target_language']);
        $this->assertEquals($sourceText, $assignment->config_json['source_text']);

        // Verifikasi bahwa rubrik otomatis yang terpasang adalah Rubrik Kualitas Penerjemahan (TQA)
        $rubric = $assignment->rubric()->latest('version')->firstOrFail();
        $this->assertEquals('Rubrik Kualitas Penerjemahan (Translation Quality Assessment)', $rubric->name);
        $criterionNames = collect($rubric->criteria)->pluck('name')->all();
        $this->assertContains('Accuracy & Faithfulness', $criterionNames);
        $this->assertContains('Acceptability & Naturalness', $criterionNames);
        $this->assertContains('Terminology & Register', $criterionNames);
        $this->assertContains('Translation Strategies & Notes', $criterionNames);
        $this->assertContains('Target Mechanics & Style', $criterionNames);
    }

    public function test_student_can_submit_translation_with_translators_notes(): void
    {
        $assignment = WritingAssignment::create([
            'course_id' => $this->translationCourse->id,
            'project_id' => $this->project->id,
            'created_by' => $this->lecturer->id,
            'title' => 'Legal Contract Translation Task',
            'instructions' => 'Terjemahkan klausul perjanjian bisnis.',
            'assignment_type' => 'translation',
            'config_json' => [
                'source_language' => 'Bahasa Indonesia',
                'target_language' => 'English',
                'source_text' => 'Perjanjian ini tunduk dan ditafsirkan sesuai hukum Republik Indonesia.',
            ],
            'status' => 'published',
        ]);
        $assignment->students()->sync([$this->student->id]);
        $assignment->students()->sync([$this->student->id]);

        $targetTranslation = 'This Agreement shall be governed by and construed in accordance with the laws of the Republic of Indonesia.';
        $notes = "Modulasi diterapkan pada frasa 'ditafsirkan sesuai hukum' menjadi 'construed in accordance with' mengikuti konvensi common law drafting.";

        $response = $this->actingAs($this->student)
            ->post(route('assignments.revisions.store', $assignment), [
                'body_text' => $targetTranslation,
                'notes' => $notes,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $submission = WritingSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $this->student->id)
            ->firstOrFail();

        $revision = $submission->revisions()->firstOrFail();
        $this->assertEquals($targetTranslation, $revision->body_text);
        $this->assertEquals($notes, $revision->notes);
        $this->assertEquals($notes, $submission->notes);
    }

    public function test_lecturer_can_create_milestone_proposal_assignment_with_automatic_stages(): void
    {
        $response = $this->actingAs($this->lecturer)->post(route('courses.assignments.store', $this->seminarCourse), [
            'title' => 'Proposal Skripsi & Seminar ELT 2026',
            'instructions' => 'Kumpulkan draf proposal penelitian skripsi Anda secara bertahap dari Bab 1 hingga Bab 3 disertai slide presentasi seminar.',
            'assignment_type' => 'milestone_artifact',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $assignment = WritingAssignment::where('course_id', $this->seminarCourse->id)->firstOrFail();
        $this->assertTrue($assignment->isMilestone());
        $this->assertEquals('Proposal & Proyek Bertahap', $assignment->typeLabel());
        $this->assertNotEmpty($assignment->config_json['milestones']);
        $this->assertCount(4, $assignment->config_json['milestones']);
        $this->assertStringContainsString('Bab 1', $assignment->config_json['milestones'][0]['title']);

        // Verifikasi bahwa rubrik otomatis yang terpasang adalah Rubrik Evaluasi Proposal Penelitian & Presentasi
        $rubric = $assignment->rubric()->latest('version')->firstOrFail();
        $this->assertEquals('Rubrik Evaluasi Proposal Penelitian & Presentasi', $rubric->name);
        $criterionNames = collect($rubric->criteria)->pluck('name')->all();
        $this->assertContains('Research Problem & Urgency', $criterionNames);
        $this->assertContains('Literature Synthesis & Theory', $criterionNames);
        $this->assertContains('Methodology & Design', $criterionNames);
        $this->assertContains('Academic Presentation & Delivery', $criterionNames);
    }

    public function test_student_can_submit_milestone_revision_with_digital_artifact_link(): void
    {
        $assignment = WritingAssignment::create([
            'course_id' => $this->seminarCourse->id,
            'project_id' => $this->project->id,
            'created_by' => $this->lecturer->id,
            'title' => 'Seminar in ELT: Phased Proposal Task',
            'instructions' => 'Kumpulkan proposal bertahap.',
            'assignment_type' => 'milestone_artifact',
            'config_json' => [
                'milestones' => [
                    ['id' => 1, 'title' => 'Bab 1: Pendahuluan & Research Questions'],
                    ['id' => 2, 'title' => 'Bab 2: Kajian Teoretis'],
                    ['id' => 3, 'title' => 'Bab 3: Metodologi Penelitian'],
                    ['id' => 4, 'title' => 'Naskah Final & Slide Seminar'],
                ],
                'allow_media_url' => true,
            ],
            'status' => 'published',
        ]);
        $assignment->students()->sync([$this->student->id]);

        // Student submits Milestone 1
        $response = $this->actingAs($this->student)
            ->post(route('assignments.revisions.store', $assignment), [
                'milestone_id' => 1,
                'body_text' => 'Bab 1 Pendahuluan berfokus pada analisis tantangan communicative language teaching di sekolah pedesaan.',
                'artifact_url' => 'https://drive.google.com/file/d/12345/view?usp=sharing',
                'notes' => 'Telah direvisi sesuai masukan pembimbing terkait penajaman rumusan masalah nomor 2.',
            ]);

        $response->assertRedirect();

        $submission = WritingSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $this->student->id)
            ->firstOrFail();

        $this->assertEquals(1, $submission->current_milestone_id);
        $this->assertEquals('Bab 1: Pendahuluan & Research Questions', $submission->currentMilestoneTitle());
        $this->assertEquals('https://drive.google.com/file/d/12345/view?usp=sharing', $submission->artifact_url);

        $revision1 = $submission->revisions()->firstOrFail();
        $this->assertEquals(1, $revision1->milestone_id);
        $this->assertEquals('Bab 1: Pendahuluan & Research Questions', $revision1->milestoneTitle());
        $this->assertStringContainsString('communicative language teaching', $revision1->body_text);

        // Student submits Milestone 2 with video presentation link
        $response2 = $this->actingAs($this->student)
            ->post(route('assignments.revisions.store', $assignment), [
                'milestone_id' => 2,
                'body_text' => 'Bab 2 memuat sintesis 25 artikel internasional bereputasi terkait task-based language teaching.',
                'artifact_url' => 'https://youtube.com/watch?v=mockVideoElt2026',
                'notes' => 'Menambahkan matriks sintesis literatur dari LitSynth.',
            ]);

        $response2->assertRedirect();
        $submission->refresh();

        $this->assertEquals(2, $submission->current_milestone_id);
        $this->assertEquals('Bab 2: Kajian Teoretis', $submission->currentMilestoneTitle());
        $this->assertEquals(2, $submission->revisions()->count());
    }
}
