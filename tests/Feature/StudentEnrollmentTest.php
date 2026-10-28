<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\Faculty;
use App\Models\Project;
use App\Models\StudyProgram;
use App\Models\User;
use App\Models\WritingAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;

    private StudyProgram $prodi;

    private Course $courseA;

    private Course $courseB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lecturer = User::factory()->create([
            'role' => 'lecturer',
            'name' => 'Dr. Rina Kusuma',
        ]);

        $faculty = Faculty::create([
            'code' => 'FKIP',
            'name' => 'Fakultas Keguruan dan Ilmu Pendidikan',
        ]);

        $this->prodi = StudyProgram::create([
            'faculty_id' => $faculty->id,
            'code' => 'PBI-S1',
            'name' => 'Pendidikan Bahasa Inggris',
            'level' => 'S1',
        ]);

        $period = AcademicPeriod::create([
            'name' => '2026/2027 Ganjil',
            'academic_year' => '2026/2027',
            'semester' => 'odd',
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-31',
            'is_active' => true,
        ]);

        $this->courseA = Course::create([
            'academic_period_id' => $period->id,
            'study_program_id' => $this->prodi->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG201',
            'name' => 'Academic Writing',
            'credit_hours' => 3,
            'class_group' => 'Kelas A',
        ]);

        $this->courseB = Course::create([
            'academic_period_id' => $period->id,
            'study_program_id' => $this->prodi->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG204',
            'name' => 'Applied Linguistics',
            'credit_hours' => 2,
            'class_group' => 'Kelas B',
        ]);
    }

    public function test_lecturer_can_create_single_student_with_auto_email_and_assign_to_course(): void
    {
        // Buat 1 tugas aktif di Course A untuk menguji auto-sync tugas
        $project = Project::create([
            'owner_id' => $this->lecturer->id,
            'name' => 'Pengajaran Academic Writing',
            'pillars' => ['teaching'],
            'status' => 'active',
        ]);

        $assignment = WritingAssignment::create([
            'project_id' => $project->id,
            'course_id' => $this->courseA->id,
            'created_by' => $this->lecturer->id,
            'title' => 'Argumentative Essay Draft',
            'instructions' => 'Write 500 words.',
            'status' => 'published',
        ]);

        $this->actingAs($this->lecturer)
            ->post(route('students.store'), [
                'nim' => '240105001',
                'name' => 'Ahmad Fauzan',
                'email' => '', // Kosong -> digenerate otomatis
                'study_program_id' => $this->prodi->id,
                'cohort_year' => 2024,
                'course_ids' => [$this->courseA->id],
            ])
            ->assertRedirect(route('students.index'));

        $student = User::where('nim', '240105001')->firstOrFail();
        $this->assertSame('Ahmad Fauzan', $student->name);
        $this->assertSame('240105001@student.academia.id', $student->email);
        $this->assertSame($this->prodi->id, $student->study_program_id);
        $this->assertSame(2024, $student->cohort_year);

        // Pastikan masuk ke course_students
        $this->assertDatabaseHas('course_students', [
            'course_id' => $this->courseA->id,
            'student_id' => $student->id,
            'nim' => '240105001',
        ]);

        // Pastikan otomatis ter-enroll ke tugas WriteWise pada mata kuliah tersebut
        $this->assertTrue($assignment->students()->where('users.id', $student->id)->exists());
    }

    public function test_lecturer_can_bulk_import_students_from_pasted_lines_and_assign_to_courses(): void
    {
        $rawList = <<<'TEXT'
No ; NIM ; Nama Lengkap ; Email
1 ; 240105010 ; Siti Aminah
2 ; 240105011 ; Budi Santoso ; budi.santoso@student.ac.id
240105012	Rina Kartika
TEXT;

        $this->actingAs($this->lecturer)
            ->post(route('students.bulk-store'), [
                'raw_list' => $rawList,
                'study_program_id' => $this->prodi->id,
                'cohort_year' => 2024,
                'course_ids' => [$this->courseA->id, $this->courseB->id],
            ])
            ->assertRedirect(route('students.index'));

        $this->assertDatabaseHas('users', [
            'nim' => '240105010',
            'name' => 'Siti Aminah',
            'study_program_id' => $this->prodi->id,
        ]);

        $this->assertDatabaseHas('users', [
            'nim' => '240105011',
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@student.ac.id',
        ]);

        $this->assertDatabaseHas('users', [
            'nim' => '240105012',
            'name' => 'Rina Kartika',
        ]);

        // Ketiga mahasiswa harus terdaftar di Course A dan Course B
        $this->assertSame(3, CourseStudent::where('course_id', $this->courseA->id)->count());
        $this->assertSame(3, CourseStudent::where('course_id', $this->courseB->id)->count());
    }

    public function test_lecturer_can_edit_student_and_sync_enrolled_courses(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'nim' => '230105099',
            'name' => 'Dimas Anggara',
            'email' => 'dimas@student.academia.id',
            'study_program_id' => $this->prodi->id,
            'cohort_year' => 2023,
        ]);

        CourseStudent::create([
            'course_id' => $this->courseA->id,
            'student_id' => $student->id,
            'nim' => '230105099',
            'attendance_rate' => 95,
            'final_grade' => 88.5,
            'letter_grade' => 'A',
        ]);

        // Edit mahasiswa: ganti NIM, pertahankan Course A dan tambahkan Course B
        $this->actingAs($this->lecturer)
            ->put(route('students.update', $student), [
                'nim' => '230105100',
                'name' => 'Dimas Anggara Putra',
                'email' => 'dimas@student.academia.id',
                'study_program_id' => $this->prodi->id,
                'cohort_year' => 2023,
                'is_active' => '1',
                'sync_courses' => '1',
                'course_ids' => [$this->courseA->id, $this->courseB->id],
            ])
            ->assertRedirect(route('students.index'));

        $this->assertSame('230105100', $student->fresh()->nim);

        // Nilai akhir di Course A tetap terjaga dan NIM tersinkronisasi
        $enrollmentA = CourseStudent::where('course_id', $this->courseA->id)->where('student_id', $student->id)->firstOrFail();
        $this->assertSame('230105100', $enrollmentA->nim);
        $this->assertSame(88.5, $enrollmentA->final_grade);

        // Terdaftar juga di Course B
        $this->assertDatabaseHas('course_students', [
            'course_id' => $this->courseB->id,
            'student_id' => $student->id,
            'nim' => '230105100',
        ]);
    }

    public function test_lecturer_can_enroll_update_attendance_and_unenroll_students_directly_inside_course(): void
    {
        $existingStudent = User::factory()->create([
            'role' => 'student',
            'nim' => '240105050',
            'name' => 'Putri Maharani',
        ]);

        // 1. Enroll dari direktori + paste baru sekaligus di dalam ruang mata kuliah
        $this->actingAs($this->lecturer)
            ->post(route('courses.students.enroll', $this->courseA), [
                'student_ids' => [$existingStudent->id],
                'raw_list' => "240105051 ; Hendra Gunawan\n240105052 ; Maya Sari",
                'cohort_year' => 2024,
            ])
            ->assertRedirect(route('courses.show', ['course' => $this->courseA, 'tab' => 'students']));

        $this->assertSame(3, CourseStudent::where('course_id', $this->courseA->id)->count());

        $enrollment = CourseStudent::where('course_id', $this->courseA->id)
            ->where('student_id', $existingStudent->id)
            ->firstOrFail();

        // 2. Update persentase kehadiran inline
        $this->actingAs($this->lecturer)
            ->patch(route('courses.students.update', [$this->courseA, $enrollment]), [
                'nim' => '240105050',
                'attendance_rate' => 87,
            ])
            ->assertRedirect(route('courses.show', ['course' => $this->courseA, 'tab' => 'students']));

        $this->assertSame(87.0, $enrollment->fresh()->attendance_rate);

        // 3. Unenroll (keluarkan dari kelas) tanpa menghapus akun dari direktori utama
        $this->actingAs($this->lecturer)
            ->delete(route('courses.students.unenroll', [$this->courseA, $enrollment]))
            ->assertRedirect(route('courses.show', ['course' => $this->courseA, 'tab' => 'students']));

        $this->assertDatabaseMissing('course_students', ['id' => $enrollment->id]);
        $this->assertDatabaseHas('users', ['id' => $existingStudent->id]);
    }
}
