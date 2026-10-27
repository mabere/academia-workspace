<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\Faculty;
use App\Models\LecturerProfile;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;

    private AcademicPeriod $period;

    private StudyProgram $studyProgram;

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
    }

    public function test_lecturer_can_see_courses_index_with_sks_summary(): void
    {
        Course::create([
            'academic_period_id' => $this->period->id,
            'study_program_id' => $this->studyProgram->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG201',
            'name' => 'Academic Writing',
            'credit_hours' => 3,
            'class_group' => 'Kelas A',
        ]);

        Course::create([
            'academic_period_id' => $this->period->id,
            'study_program_id' => $this->studyProgram->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG204',
            'name' => 'Applied Linguistics',
            'credit_hours' => 2,
            'class_group' => 'Kelas B',
        ]);

        $response = $this->actingAs($this->lecturer)->get(route('courses.index'));

        $response->assertOk();
        $response->assertSee('Academic Writing');
        $response->assertSee('Applied Linguistics');
        $response->assertSee('5 SKS'); // 3 + 2
        $response->assertSee('MEMENUHI (≥ 3.0 SKS)');
    }

    public function test_lecturer_can_view_course_room_with_materials_and_sessions(): void
    {
        $course = Course::create([
            'academic_period_id' => $this->period->id,
            'study_program_id' => $this->studyProgram->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG201',
            'name' => 'Academic Writing',
            'credit_hours' => 3,
            'class_group' => 'Kelas A',
            'schedule_day' => 'Senin',
            'schedule_time' => '08:00 - 10:30 WIB',
        ]);

        $course->materials()->create([
            'uploaded_by' => $this->lecturer->id,
            'title' => 'Silabus & Kontrak Kuliah',
            'material_type' => 'document',
        ]);

        $course->sessions()->create([
            'session_number' => 1,
            'date' => '2026-09-07',
            'topic' => 'Pengantar Academic Writing',
            'attendance_count' => 25,
            'total_students' => 25,
        ]);

        $response = $this->actingAs($this->lecturer)->get(route('courses.show', $course));

        $response->assertOk();
        $response->assertSee('Academic Writing');
        $response->assertSee('ENG201 · Kelas A');
        $response->assertSee('Silabus & Kontrak Kuliah');
        $response->assertSee('Pengantar Academic Writing');
    }

    public function test_lecturer_can_upload_multi_format_material(): void
    {
        Storage::fake('local');

        $course = Course::create([
            'academic_period_id' => $this->period->id,
            'study_program_id' => $this->studyProgram->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG201',
            'name' => 'Academic Writing',
            'credit_hours' => 3,
        ]);

        $file = UploadedFile::fake()->create('slide_sesi_1.pptx', 2048);

        $response = $this->actingAs($this->lecturer)->post(route('courses.materials.store', $course), [
            'title' => 'Slide Presentasi Sesi 1',
            'session_number' => 1,
            'material_type' => 'document',
            'file' => $file,
            'description' => 'Materi pengantar essay organization',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('course_materials', [
            'course_id' => $course->id,
            'title' => 'Slide Presentasi Sesi 1',
            'material_type' => 'document',
            'file_name' => 'slide_sesi_1.pptx',
        ]);

        $material = CourseMaterial::first();
        Storage::disk('local')->assertExists($material->file_path);

        // Test download
        $dlResponse = $this->actingAs($this->lecturer)->get(route('courses.materials.download', [$course, $material]));
        $dlResponse->assertOk();
    }

    public function test_lecturer_can_log_course_session(): void
    {
        $course = Course::create([
            'academic_period_id' => $this->period->id,
            'study_program_id' => $this->studyProgram->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG201',
            'name' => 'Academic Writing',
            'credit_hours' => 3,
        ]);

        $response = $this->actingAs($this->lecturer)->post(route('courses.sessions.store', $course), [
            'session_number' => 1,
            'date' => '2026-09-08',
            'topic' => 'Karakteristik Teks Ilmiah & Thesis Statement',
            'teaching_method' => 'Ceramah & Diskusi Kelompok',
            'attendance_count' => 28,
            'total_students' => 30,
            'notes' => 'Diskusi aktif, mahasiswa memahami esensi argumen ilmiah.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('course_sessions', [
            'course_id' => $course->id,
            'session_number' => 1,
            'topic' => 'Karakteristik Teks Ilmiah & Thesis Statement',
            'attendance_count' => 28,
            'total_students' => 30,
        ]);
    }

    public function test_lecturer_can_view_printable_lecture_log(): void
    {
        $course = Course::create([
            'academic_period_id' => $this->period->id,
            'study_program_id' => $this->studyProgram->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG201',
            'name' => 'Academic Writing',
            'credit_hours' => 3,
            'class_group' => 'Kelas A',
        ]);

        $course->sessions()->create([
            'session_number' => 1,
            'date' => '2026-09-08',
            'topic' => 'Perkenalan & Kontrak Belajar',
            'attendance_count' => 20,
            'total_students' => 20,
        ]);

        $response = $this->actingAs($this->lecturer)->get(route('courses.sessions.print', $course));

        $response->assertOk();
        $response->assertSee('LAPORAN JURNAL PERKULIAHAN', false);
        $response->assertSee('Academic Writing');
        $response->assertSee('Dr. Budi Santoso');
        $response->assertSee('Perkenalan & Kontrak Belajar');
    }

    public function test_student_can_only_access_enrolled_courses(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $courseEnrolled = Course::create([
            'academic_period_id' => $this->period->id,
            'study_program_id' => $this->studyProgram->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG201',
            'name' => 'Academic Writing',
            'credit_hours' => 3,
        ]);

        $courseNotEnrolled = Course::create([
            'academic_period_id' => $this->period->id,
            'study_program_id' => $this->studyProgram->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG204',
            'name' => 'Applied Linguistics',
            'credit_hours' => 2,
        ]);

        $courseEnrolled->students()->create([
            'student_id' => $student->id,
            'nim' => '230105001',
        ]);

        // Enrolled -> 200 OK
        $this->actingAs($student)->get(route('courses.show', $courseEnrolled))->assertOk();

        // Not enrolled -> 403 Forbidden
        $this->actingAs($student)->get(route('courses.show', $courseNotEnrolled))->assertStatus(403);
    }

    public function test_bkd_submission_and_export_includes_courses_teaching_sks(): void
    {
        // Dosen mengampu 2 MK: 3 SKS + 2 SKS = 5 SKS
        $course1 = Course::create([
            'academic_period_id' => $this->period->id,
            'study_program_id' => $this->studyProgram->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG201',
            'name' => 'Academic Writing',
            'credit_hours' => 3,
        ]);
        $course1->sessions()->create([
            'session_number' => 1,
            'date' => '2026-09-08',
            'topic' => 'Pertemuan 1',
            'attendance_count' => 10,
            'total_students' => 10,
        ]);

        $course2 = Course::create([
            'academic_period_id' => $this->period->id,
            'study_program_id' => $this->studyProgram->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG204',
            'name' => 'Applied Linguistics',
            'credit_hours' => 2,
        ]);
        $course2->sessions()->create([
            'session_number' => 1,
            'date' => '2026-09-09',
            'topic' => 'Pertemuan 1',
            'attendance_count' => 10,
            'total_students' => 10,
        ]);

        // Dosen mengajukan BKD
        $response = $this->actingAs($this->lecturer)->post(route('bkd.submit', $this->period));
        $response->assertRedirect();

        $this->assertDatabaseHas('bkd_assessments', [
            'academic_period_id' => $this->period->id,
            'lecturer_id' => $this->lecturer->id,
            'teaching_sks' => 5.00, // 3 + 2 SKS dari MK
            'status' => 'submitted',
        ]);

        // Ekspor Lembar BKD Resmi memuat MK tersebut
        $exportResponse = $this->actingAs($this->lecturer)->get(route('bkd.export', $this->period));
        $exportResponse->assertOk();
        $exportResponse->assertSee('Mengampu Mata Kuliah Academic Writing');
        $exportResponse->assertSee('Mengampu Mata Kuliah Applied Linguistics');
        $exportResponse->assertSee('+3.0');
        $exportResponse->assertSee('+2.0');
    }
}
