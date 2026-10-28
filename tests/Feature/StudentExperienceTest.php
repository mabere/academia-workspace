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

class StudentExperienceTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;

    private User $student;

    private User $classmate;

    private AcademicPeriod $period;

    private StudyProgram $studyProgram;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $faculty = Faculty::create([
            'name' => 'Fakultas Keguruan dan Ilmu Pendidikan',
            'code' => 'FKIP',
        ]);

        $this->studyProgram = StudyProgram::create([
            'faculty_id' => $faculty->id,
            'name' => 'Pendidikan Bahasa Inggris',
            'code' => 'PBI-S1',
            'level' => 'S1',
        ]);

        $this->lecturer = User::factory()->create([
            'name' => 'Dr. Budi Santoso',
            'role' => 'lecturer',
        ]);

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
            'lecturer_id' => $this->lecturer->id,
            'academic_period_id' => $this->period->id,
            'study_program_id' => $this->studyProgram->id,
            'code' => 'ENG201',
            'name' => 'Academic Writing for ELT',
            'credits' => 3,
            'class_section' => 'A',
            'schedule_day' => 'Senin',
            'schedule_time_start' => '08:00',
            'schedule_time_end' => '10:30',
            'room' => 'Lab Bahasa 2',
        ]);

        $this->student = User::factory()->create([
            'name' => 'Ahmad Dahlan',
            'email' => 'ahmad@student.ac.id',
            'role' => 'student',
            'nim' => '240105001',
            'study_program_id' => $this->studyProgram->id,
            'cohort_year' => 2024,
        ]);

        $this->classmate = User::factory()->create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@student.ac.id',
            'role' => 'student',
            'nim' => '240105002',
            'study_program_id' => $this->studyProgram->id,
            'cohort_year' => 2024,
        ]);

        // Enroll students
        CourseStudent::create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'nim' => $this->student->nim,
            'attendance_rate' => 93.3,
            'final_grade' => 88.5,
            'letter_grade' => 'A',
        ]);

        CourseStudent::create([
            'course_id' => $this->course->id,
            'student_id' => $this->classmate->id,
            'nim' => $this->classmate->nim,
            'attendance_rate' => 75.0,
            'final_grade' => 62.0,
            'letter_grade' => 'C+',
        ]);
    }

    public function test_student_dashboard_displays_learning_portal_and_hides_lecturer_modules(): void
    {
        $response = $this->actingAs($this->student)->get(route('dashboard'));

        $response->assertOk();
        // Displays student banner & academic identity
        $response->assertSee('Portal Belajar Mahasiswa');
        $response->assertSee($this->student->name);
        $response->assertSee('240105001');
        $response->assertSee('Pendidikan Bahasa Inggris');

        // Displays student learning KPIs
        $response->assertSee('Mata Kuliah Diikuti');
        $response->assertSee('Total Beban SKS');
        $response->assertSee('Tugas Akademik');

        // Displays enrolled courses
        $response->assertSee('Academic Writing for ELT');
        $response->assertSee('Dr. Budi Santoso');

        // Does NOT display staff-only modules or BKD
        $response->assertDontSee('id="modules-heading"', false);
        $response->assertDontSee('LitSynth');
        $response->assertDontSee('GuruBahasa');
        $response->assertDontSee('CorpusLab');
        $response->assertDontSee('Target Beban Kerja Dosen (BKD)');
    }

    public function test_student_courses_index_displays_student_metrics_and_hides_management_controls(): void
    {
        $response = $this->actingAs($this->student)->get(route('courses.index'));

        $response->assertOk();
        // Displays student titles & metrics
        $response->assertSee('Portal Pembelajaran Mahasiswa');
        $response->assertSee('Mata Kuliah Saya');
        $response->assertSee('Mata Kuliah Diikuti');
        $response->assertSee('Total SKS Diambil');
        $response->assertSee('Academic Writing for ELT');
        $response->assertSee('Dr. Budi Santoso');
        $response->assertSee('Masuk Kelas');

        // Does NOT display lecturer management actions
        $response->assertDontSee('+ Tambah Mata Kuliah');
        $response->assertDontSee('BKD Target');
        $response->assertDontSee('Pilar Pendidikan &amp; Pengajaran', false);
    }

    public function test_student_course_show_preserves_privacy_and_hides_administrative_buttons(): void
    {
        // Lecturer creates an assignment
        $this->actingAs($this->lecturer)->post(route('courses.assignments.store', $this->course), [
            'title' => 'Critical Essay on SLA',
            'instructions' => 'Write a 1500-word critical essay comparing Krashen and Swain.',
            'due_at' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'rubric_name' => 'Rubrik Analitik Esai Kritis',
            'feedback_language' => 'id',
            'feedback_level' => 'indirect',
        ]);

        $assignment = WritingAssignment::where('course_id', $this->course->id)->firstOrFail();

        // Create submissions
        WritingSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'status' => 'graded',
            'score' => 88.5,
        ]);

        WritingSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->classmate->id,
            'status' => 'graded',
            'score' => 62.0,
        ]);

        $response = $this->actingAs($this->student)->get(route('courses.show', $this->course));

        $response->assertOk();

        // 1. Administrative buttons hidden
        $response->assertDontSee('Cetak Jurnal Perkuliahan');
        $response->assertDontSee('Cetak Rekap Nilai Sah A4');
        $response->assertDontSee('Edit MK');

        // 2. Tab names adjusted
        $response->assertSee('Tugas Saya');
        $response->assertSee('Teman Sekelas');

        // 3. Tab 3: Shows personal submission status & grade
        $response->assertSee('Status Pengumpulan Saya');
        $response->assertSee('Sudah Dinilai Dosen');
        $response->assertSee('Nilai: 88.5');
        $response->assertSee('(Mutu: A)');
        $response->assertSee('Buka Draf & Umpan Balik di WriteWise', false);

        // Does NOT show class-wide grading form or table
        $response->assertDontSee('Submisi Mahasiswa Kelas');
        $response->assertDontSee('Form Penilaian Dosen');

        // 4. Tab 4: Shows personal summary card
        $response->assertSee('Rekap Capaian Akademik Saya');
        $response->assertSee('93%'); // attendance
        $response->assertSee('88.5'); // personal final grade

        // Classmate list is visible with privacy protection (classmate's score 62.0 is NOT visible)
        $response->assertSee('Siti Nurhaliza');
        $response->assertSee('240105002');
        $response->assertDontSee('62.0'); // classmate's private score is not leaked
    }

    public function test_lecturer_still_sees_full_administrative_controls(): void
    {
        $response = $this->actingAs($this->lecturer)->get(route('courses.show', $this->course));

        $response->assertOk();
        $response->assertSee('Cetak Jurnal Perkuliahan');
        $response->assertSee('Edit MK');
        $response->assertSee('Tugas &amp; Penilaian', false);
        $response->assertSee('Peserta Kelas');
        $response->assertSee('Cetak Rekap Nilai Sah A4');
        $response->assertSee('Assign / Tambah Mahasiswa ke Kelas Ini');
    }
}
