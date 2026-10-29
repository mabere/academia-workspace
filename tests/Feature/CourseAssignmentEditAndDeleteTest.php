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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseAssignmentEditAndDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;

    private User $otherLecturer;

    private User $student;

    private User $admin;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

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

        $this->otherLecturer = User::factory()->create(['name' => 'Dr. Maya Arisandi', 'role' => 'lecturer']);
        $this->student = User::factory()->create(['name' => 'Andi Wijaya', 'role' => 'student', 'nim' => '20240101']);
        $this->admin = User::factory()->create(['name' => 'Administrator', 'role' => 'admin']);

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
            'code' => 'ENG6334',
            'name' => 'Writing for Academic Assignment',
            'class_group' => 'A',
            'credit_hours' => 3,
            'semester' => 3,
        ]);

        CourseStudent::create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'active',
        ]);
    }

    public function test_lecturer_can_view_assignment_edit_page(): void
    {
        $this->actingAs($this->lecturer)->post(route('courses.assignments.store', $this->course), [
            'title' => 'Tugas Esai 1',
            'instructions' => 'Instruksi awal tugas esai.',
            'assignment_type' => 'writing',
            'due_at' => now()->addDays(7)->format('Y-m-d H:i:s'),
        ]);

        $assignment = WritingAssignment::where('course_id', $this->course->id)->first();
        $this->assertNotNull($assignment);

        $response = $this->actingAs($this->lecturer)->get(route('courses.assignments.edit', [$this->course, $assignment]));
        $response->assertOk();
        $response->assertSee('Sunting Tugas Perkuliahan');
        $response->assertSee('Tugas Esai 1');
        $response->assertSee('Instruksi awal tugas esai.');

        // Mahasiswa tidak berhak membuka form sunting
        $this->actingAs($this->student)
            ->get(route('courses.assignments.edit', [$this->course, $assignment]))
            ->assertForbidden();

        // Dosen lain tidak berhak membuka form sunting
        $this->actingAs($this->otherLecturer)
            ->get(route('courses.assignments.edit', [$this->course, $assignment]))
            ->assertForbidden();
    }

    public function test_lecturer_can_update_assignment_details_and_rubric(): void
    {
        $this->actingAs($this->lecturer)->post(route('courses.assignments.store', $this->course), [
            'title' => 'Tugas Awal',
            'instructions' => 'Instruksi lama.',
            'assignment_type' => 'writing',
            'rubric_name' => 'Rubrik Lama',
        ]);

        $assignment = WritingAssignment::where('course_id', $this->course->id)->first();

        $response = $this->actingAs($this->lecturer)->put(route('courses.assignments.update', [$this->course, $assignment]), [
            'title' => 'Tugas Diperbarui',
            'instructions' => 'Instruksi baru yang lebih komprehensif.',
            'assignment_type' => 'writing',
            'status' => 'published',
            'due_at' => now()->addDays(14)->format('Y-m-d H:i:s'),
            'feedback_language' => 'en',
            'feedback_level' => 'direct',
            'rubric_name' => 'Rubrik Menulis Akademik Lanjutan',
        ]);

        $response->assertRedirect(route('courses.show', ['course' => $this->course, 'tab' => 'assignments']));
        $response->assertSessionHas('status');

        $assignment->refresh();
        $this->assertEquals('Tugas Diperbarui', $assignment->title);
        $this->assertEquals('Instruksi baru yang lebih komprehensif.', $assignment->instructions);
        $this->assertEquals('en', $assignment->feedback_language);
        $this->assertEquals('direct', $assignment->feedback_level);

        // Snapshot versi bertambah
        $this->assertDatabaseHas('writing_assignment_versions', [
            'assignment_id' => $assignment->id,
            'version' => 2,
            'title' => 'Tugas Diperbarui',
        ]);

        // Rubrik diperbarui
        $this->assertDatabaseHas('writing_rubrics', [
            'assignment_id' => $assignment->id,
            'name' => 'Rubrik Menulis Akademik Lanjutan',
        ]);
    }

    public function test_lecturer_can_update_multimodal_audio_and_translation_configs(): void
    {
        $audio = UploadedFile::fake()->create('lecture.mp3', 1024, 'audio/mpeg');

        $this->actingAs($this->lecturer)->post(route('courses.assignments.store', $this->course), [
            'title' => 'Listening Midterm',
            'instructions' => 'Dengarkan rekaman audio.',
            'assignment_type' => 'stimulus_exam',
            'audio_file' => $audio,
            'audio_max_plays' => 2,
            'time_limit_minutes' => 45,
            'reading_passage' => 'Teks bacaan awal.',
        ]);

        $assignment = WritingAssignment::where('course_id', $this->course->id)->first();
        $oldPath = $assignment->config_json['audio_file_path'];
        Storage::disk('public')->assertExists($oldPath);

        // Ganti file audio baru dan ubah batas putar
        $newAudio = UploadedFile::fake()->create('lecture_v2.mp3', 2048, 'audio/mpeg');

        $this->actingAs($this->lecturer)->put(route('courses.assignments.update', [$this->course, $assignment]), [
            'title' => 'Listening Midterm Re-Recorded',
            'instructions' => 'Dengarkan rekaman audio versi 2.',
            'assignment_type' => 'stimulus_exam',
            'status' => 'published',
            'audio_file' => $newAudio,
            'audio_max_plays' => 1,
            'time_limit_minutes' => 50,
            'reading_passage' => 'Teks transkrip baru.',
        ]);

        $assignment->refresh();
        $this->assertEquals('Listening Midterm Re-Recorded', $assignment->title);
        $this->assertEquals(1, $assignment->config_json['audio_max_plays']);
        $this->assertEquals(50, $assignment->time_limit_minutes);
        $this->assertEquals('Teks transkrip baru.', $assignment->config_json['reading_passage']);

        // File audio lama terhapus dan file baru tersimpan
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($assignment->config_json['audio_file_path']);

        // Update dengan delete_audio = 1
        $currentPath = $assignment->config_json['audio_file_path'];
        $this->actingAs($this->lecturer)->put(route('courses.assignments.update', [$this->course, $assignment]), [
            'title' => 'Listening Without Audio',
            'instructions' => 'Hanya teks bacaan.',
            'assignment_type' => 'stimulus_exam',
            'status' => 'published',
            'delete_audio' => 1,
        ]);

        $assignment->refresh();
        Storage::disk('public')->assertMissing($currentPath);
        $this->assertArrayNotHasKey('audio_file_path', $assignment->config_json ?? []);
    }

    public function test_lecturer_can_delete_assignment_and_recalculate_grades(): void
    {
        $audio = UploadedFile::fake()->create('sample.mp3', 512, 'audio/mpeg');

        $this->actingAs($this->lecturer)->post(route('courses.assignments.store', $this->course), [
            'title' => 'Tugas Ujian untuk Dihapus',
            'instructions' => 'Soal latihan.',
            'assignment_type' => 'stimulus_exam',
            'audio_file' => $audio,
        ]);

        $assignment = WritingAssignment::where('course_id', $this->course->id)->first();
        $audioPath = $assignment->config_json['audio_file_path'];
        Storage::disk('public')->assertExists($audioPath);

        // Tambah butir pertanyaan
        $question = $assignment->questions()->create([
            'question_type' => 'multiple_choice',
            'prompt' => 'What is the main topic?',
            'options_json' => [['key' => 'A', 'text' => 'Option A']],
            'answer_key' => 'A',
            'points' => 10,
            'order' => 1,
        ]);

        // Buat submisi dan nilai untuk mahasiswa
        $submission = $assignment->submissions()->create([
            'student_id' => $this->student->id,
            'score' => 85.0,
            'status' => 'graded',
        ]);

        // Set nilai akhir mahasiswa pada kelas
        $courseStudent = CourseStudent::where('course_id', $this->course->id)->where('student_id', $this->student->id)->first();
        $courseStudent->update(['final_grade' => 85.0, 'letter_grade' => 'A']);

        // Hapus tugas
        $response = $this->actingAs($this->lecturer)->delete(route('courses.assignments.destroy', [$this->course, $assignment]));
        $response->assertRedirect(route('courses.show', ['course' => $this->course, 'tab' => 'assignments']));
        $response->assertSessionHas('status');

        // Verifikasi tugas terhapus dari database
        $this->assertDatabaseMissing('writing_assignments', ['id' => $assignment->id]);
        $this->assertDatabaseMissing('assignment_questions', ['id' => $question->id]);
        $this->assertDatabaseMissing('writing_submissions', ['id' => $submission->id]);

        // Verifikasi berkas audio terhapus dari storage
        Storage::disk('public')->assertMissing($audioPath);

        // Verifikasi nilai akhir mahasiswa dihitung ulang (menjadi null karena tidak ada tugas tersisa)
        $courseStudent->refresh();
        $this->assertNull($courseStudent->final_grade);
        $this->assertNull($courseStudent->letter_grade);
    }

    public function test_admin_can_update_and_delete_course_assignment(): void
    {
        $this->actingAs($this->lecturer)->post(route('courses.assignments.store', $this->course), [
            'title' => 'Tugas oleh Dosen',
            'instructions' => 'Instruksi.',
            'assignment_type' => 'writing',
        ]);

        $assignment = WritingAssignment::where('course_id', $this->course->id)->first();

        // Admin can update
        $updateRes = $this->actingAs($this->admin)->put(route('courses.assignments.update', [$this->course, $assignment]), [
            'title' => 'Tugas Diperbarui oleh Admin',
            'instructions' => 'Instruksi diubah admin.',
            'status' => 'published',
        ]);
        $updateRes->assertRedirect(route('courses.show', ['course' => $this->course, 'tab' => 'assignments']));

        $assignment->refresh();
        $this->assertEquals('Tugas Diperbarui oleh Admin', $assignment->title);

        // Admin can delete
        $deleteRes = $this->actingAs($this->admin)->delete(route('courses.assignments.destroy', [$this->course, $assignment]));
        $deleteRes->assertRedirect(route('courses.show', ['course' => $this->course, 'tab' => 'assignments']));
        $this->assertDatabaseMissing('writing_assignments', ['id' => $assignment->id]);
    }

    public function test_generic_assignments_destroy_route_redirects_to_course(): void
    {
        $this->actingAs($this->lecturer)->post(route('courses.assignments.store', $this->course), [
            'title' => 'Tugas Generic Destroy',
            'instructions' => 'Instruksi.',
            'assignment_type' => 'writing',
        ]);

        $assignment = WritingAssignment::where('course_id', $this->course->id)->first();

        $response = $this->actingAs($this->lecturer)->delete(route('assignments.destroy', $assignment));
        $response->assertRedirect(route('courses.show', ['course' => $this->course, 'tab' => 'assignments']));
        $this->assertDatabaseMissing('writing_assignments', ['id' => $assignment->id]);
    }
}
