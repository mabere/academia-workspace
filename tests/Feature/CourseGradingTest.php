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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseGradingTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;

    private User $student1;

    private User $student2;

    private AcademicPeriod $period;

    private StudyProgram $studyProgram;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $faculty = Faculty::create(['name' => 'Fakultas Keguruan dan Ilmu Pendidikan', 'code' => 'FKIP']);
        $this->studyProgram = StudyProgram::create([
            'faculty_id' => $faculty->id,
            'name' => 'Pendidikan Bahasa Inggris',
            'code' => 'PBI-S1',
            'level' => 'S1',
        ]);

        $this->lecturer = User::factory()->create(['name' => 'Dr. Budi Santoso', 'role' => 'lecturer']);
        LecturerProfile::create([
            'user_id' => $this->lecturer->id,
            'study_program_id' => $this->studyProgram->id,
            'nidn' => '0012058001',
            'target_sks' => 12,
        ]);

        $this->period = AcademicPeriod::create([
            'name' => '2026/2027 Ganjil',
            'academic_year' => '2026/2027',
            'semester' => 'odd',
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-31',
            'is_active' => true,
        ]);

        $this->course = Course::create([
            'academic_period_id' => $this->period->id,
            'study_program_id' => $this->studyProgram->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG201',
            'name' => 'Academic Writing',
            'credit_hours' => 3,
            'class_group' => 'Kelas A',
            'semester_level' => 3,
            'room' => 'Lab Bahasa 2',
        ]);

        $this->student1 = User::factory()->create(['name' => 'Ahmad Fauzi', 'role' => 'student']);
        $this->student2 = User::factory()->create(['name' => 'Siti Nurhaliza', 'role' => 'student']);

        CourseStudent::create([
            'course_id' => $this->course->id,
            'student_id' => $this->student1->id,
            'nim' => '230101001',
            'attendance_rate' => 93.75,
        ]);

        CourseStudent::create([
            'course_id' => $this->course->id,
            'student_id' => $this->student2->id,
            'nim' => '230101002',
            'attendance_rate' => 100.0,
        ]);
    }

    public function test_lecturer_can_create_assignment_for_course_with_automatic_enrollment_and_rubric(): void
    {
        $response = $this->actingAs($this->lecturer)->post(route('courses.assignments.store', $this->course), [
            'title' => 'Tugas 1: Argumentative Essay Outline',
            'instructions' => 'Susun kerangka esai argumentatif 5 paragraf dengan menyertakan claim, data, dan counterclaim.',
            'due_at' => now()->addDays(14)->format('Y-m-d H:i:s'),
            'rubric_name' => 'Rubrik Analitik Esai Kritis',
            'feedback_language' => 'id',
            'feedback_level' => 'indirect',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('writing_assignments', [
            'course_id' => $this->course->id,
            'title' => 'Tugas 1: Argumentative Essay Outline',
            'feedback_language' => 'id',
            'feedback_level' => 'indirect',
            'status' => 'published',
        ]);

        $assignment = WritingAssignment::where('course_id', $this->course->id)->first();
        $this->assertNotNull($assignment);
        $this->assertNotNull($assignment->project_id);

        // Pastikan seluruh mahasiswa terdaftar di kelas otomatis masuk ke penugasan ini
        $this->assertDatabaseHas('assignment_students', [
            'assignment_id' => $assignment->id,
            'student_id' => $this->student1->id,
        ]);
        $this->assertDatabaseHas('assignment_students', [
            'assignment_id' => $assignment->id,
            'student_id' => $this->student2->id,
        ]);

        // Pastikan rubrik kriteria otomatis terbuat
        $this->assertDatabaseHas('writing_rubrics', [
            'assignment_id' => $assignment->id,
            'name' => 'Rubrik Analitik Esai Kritis',
        ]);
    }

    public function test_student_can_submit_and_lecturer_can_grade_submission_with_score_and_feedback(): void
    {
        // 1. Dosen membuat tugas
        $this->actingAs($this->lecturer)->post(route('courses.assignments.store', $this->course), [
            'title' => 'Tugas Analisis Genre Artikel Jurnal',
            'instructions' => 'Lakukan dekonstruksi bagian Introduksi artikel Scopus menggunakan model CARS karya Swales.',
            'feedback_language' => 'id',
            'feedback_level' => 'indirect',
        ]);

        $assignment = WritingAssignment::where('course_id', $this->course->id)->first();

        // 2. Mahasiswa mengumpulkan draf esai
        $submitResponse = $this->actingAs($this->student1)->post(route('assignments.revisions.store', $assignment), [
            'body_text' => 'Berdasarkan model CARS (Create a Research Space), peneliti membagi move menjadi 3 tahap utama yang sistematis.',
        ]);
        $submitResponse->assertRedirect();

        $submission = WritingSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $this->student1->id)
            ->first();

        $this->assertNotNull($submission);
        $this->assertEquals('submitted', $submission->status);

        // 3. Dosen memberikan nilai (score) dan catatan umpan balik
        $gradeResponse = $this->actingAs($this->lecturer)->post(route('courses.assignments.grade', [$this->course, $assignment, $submission]), [
            'score' => 88.5,
            'feedback_notes' => 'Analisis move sangat komprehensif, kutipan literatur relevan, tata bahasa akademik sangat baik.',
        ]);

        $gradeResponse->assertRedirect();
        $gradeResponse->assertSessionHas('status');

        $submission->refresh();
        $this->assertEquals('graded', $submission->status);
        $this->assertEquals(88.5, $submission->score);
        $this->assertEquals('A', $submission->letterGrade());
        $this->assertEquals($this->lecturer->id, $submission->graded_by);

        // 4. Pastikan feedback_report dibuat dengan summary dosen
        $latestRevision = $submission->revisions()->first();
        $this->assertNotNull($latestRevision);
        $report = $latestRevision->feedbackReports()->first();
        $this->assertNotNull($report);
        $this->assertEquals(88.5, $report->feedback_json['score']);

        // 5. Pastikan nilai akhir mahasiswa (course_students) terakumulasi otomatis
        $courseStudent = CourseStudent::where('course_id', $this->course->id)
            ->where('student_id', $this->student1->id)
            ->first();

        $this->assertEquals(88.5, $courseStudent->final_grade);
        $this->assertEquals('A', $courseStudent->letter_grade);
    }

    public function test_multiple_assignment_scores_average_correctly_into_course_final_grade(): void
    {
        // Buat 2 tugas
        $this->actingAs($this->lecturer)->post(route('courses.assignments.store', $this->course), [
            'title' => 'Tugas 1',
            'instructions' => 'Instruksi tugas pertama mata kuliah.',
        ]);
        $this->actingAs($this->lecturer)->post(route('courses.assignments.store', $this->course), [
            'title' => 'Tugas 2',
            'instructions' => 'Instruksi tugas kedua mata kuliah.',
        ]);

        $assignments = WritingAssignment::where('course_id', $this->course->id)->get();
        $this->assertCount(2, $assignments);

        // Mahasiswa kumpul tugas 1
        $this->actingAs($this->student2)->post(route('assignments.revisions.store', $assignments[0]), [
            'body_text' => 'Teks pengerjaan tugas 1 mahasiswa kedua yang cukup panjang untuk memenuhi batas minimal.',
        ]);
        $sub1 = WritingSubmission::where('assignment_id', $assignments[0]->id)->where('student_id', $this->student2->id)->first();

        // Mahasiswa kumpul tugas 2
        $this->actingAs($this->student2)->post(route('assignments.revisions.store', $assignments[1]), [
            'body_text' => 'Teks pengerjaan tugas 2 mahasiswa kedua yang cukup panjang untuk memenuhi batas minimal.',
        ]);
        $sub2 = WritingSubmission::where('assignment_id', $assignments[1]->id)->where('student_id', $this->student2->id)->first();

        // Dosen beri nilai 76.0 pada Tugas 1 dan 84.0 pada Tugas 2
        $this->actingAs($this->lecturer)->post(route('courses.assignments.grade', [$this->course, $assignments[0], $sub1]), [
            'score' => 76.0,
            'feedback_notes' => 'Cukup baik.',
        ]);

        $this->actingAs($this->lecturer)->post(route('courses.assignments.grade', [$this->course, $assignments[1], $sub2]), [
            'score' => 84.0,
            'feedback_notes' => 'Peningkatan yang signifikan.',
        ]);

        // Rata-rata: (76 + 84) / 2 = 80.0 -> A-
        $courseStudent = CourseStudent::where('course_id', $this->course->id)
            ->where('student_id', $this->student2->id)
            ->first();

        $this->assertEquals(80.0, $courseStudent->final_grade);
        $this->assertEquals('A-', $courseStudent->letter_grade);
    }

    public function test_lecturer_and_students_can_access_official_printable_class_gradebook(): void
    {
        // Dosen akses cetak rekap nilai
        $response = $this->actingAs($this->lecturer)->get(route('courses.grades.print', $this->course));

        $response->assertOk();
        $response->assertSee('DAFTAR NILAI AKHIR MAHASISWA & REKAPITULASI PENILAIAN KELAS', false);
        $response->assertSee('ENG201');
        $response->assertSee('Academic Writing');
        $response->assertSee('Ahmad Fauzi');
        $response->assertSee('230101001');
        $response->assertSee('Dr. Budi Santoso');
        $response->assertSee('0012058001');

        // Mahasiswa yang terdaftar di kelas juga dapat mengakses
        $studentResponse = $this->actingAs($this->student1)->get(route('courses.grades.print', $this->course));
        $studentResponse->assertOk();
    }

    public function test_non_enrolled_student_cannot_access_class_gradebook(): void
    {
        $outsider = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($outsider)->get(route('courses.grades.print', $this->course));
        $response->assertForbidden();
    }

    public function test_student_cannot_grade_submission_or_create_course_assignment(): void
    {
        // 1. Dosen membuat tugas
        $this->actingAs($this->lecturer)->post(route('courses.assignments.store', $this->course), [
            'title' => 'Tugas Resmi',
            'instructions' => 'Instruksi resmi penugasan.',
        ]);
        $assignment = WritingAssignment::where('course_id', $this->course->id)->first();

        $sub = WritingSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student1->id,
            'status' => 'submitted',
        ]);

        // Mahasiswa mencoba memberi nilai pada submisi
        $gradeResponse = $this->actingAs($this->student1)->post(route('courses.assignments.grade', [$this->course, $assignment, $sub]), [
            'score' => 99.0,
        ]);
        $gradeResponse->assertForbidden();

        // Mahasiswa mencoba membuat tugas di mata kuliah
        $createAssignmentResponse = $this->actingAs($this->student1)->post(route('courses.assignments.store', $this->course), [
            'title' => 'Tugas Ilegal Mahasiswa',
            'instructions' => 'Percobaan pembuatan tugas.',
        ]);
        $createAssignmentResponse->assertForbidden();
    }
}
