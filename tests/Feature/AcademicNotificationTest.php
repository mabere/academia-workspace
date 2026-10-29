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
use App\Notifications\AcademicNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;

    private User $student;

    private Course $course;

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

        $this->course = Course::create([
            'academic_period_id' => $this->period->id,
            'study_program_id' => $this->studyProgram->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG201',
            'name' => 'Academic Writing',
            'credit_hours' => 3,
            'class_group' => 'A',
            'schedule_day' => 'Senin',
            'schedule_time' => '08:00 - 10:30',
            'room' => 'Lab Bahasa 2',
        ]);

        $this->student = User::factory()->create([
            'name' => 'Siti Rahma',
            'email' => 'siti@student.ac.id',
            'role' => 'student',
            'nim' => '2101010045',
            'study_program_id' => $this->studyProgram->id,
        ]);

        CourseStudent::create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'nim' => '2101010045',
            'attendance_rate' => 100.0,
        ]);
    }

    public function test_student_receives_notification_when_new_assignment_published(): void
    {
        $this->actingAs($this->lecturer)
            ->post(route('courses.assignments.store', $this->course), [
                'title' => 'Esai Argumentatif Struktur Tesis',
                'instructions' => 'Tulis esai argumentatif minimal 500 kata dengan topik pendidikan modern.',
                'feedback_language' => 'id',
                'feedback_level' => 'balanced',
            ]);

        $this->assertEquals(1, $this->student->unreadNotifications()->count());
        $notif = $this->student->unreadNotifications()->first();
        $this->assertEquals('Tugas Perkuliahan Baru', $notif->data['title']);
        $this->assertEquals('assignment', $notif->data['category']);
    }

    public function test_lecturer_receives_notification_when_student_submits_draft(): void
    {
        // 1. Dosen membuat tugas
        $this->actingAs($this->lecturer)
            ->post(route('courses.assignments.store', $this->course), [
                'title' => 'Tugas Menulis Esai',
                'instructions' => 'Instruksi tugas',
                'feedback_language' => 'id',
                'feedback_level' => 'balanced',
            ]);

        $assignment = WritingAssignment::where('course_id', $this->course->id)->firstOrFail();

        // 2. Mahasiswa mengirimkan draf
        $this->actingAs($this->student)
            ->post(route('assignments.revisions.store', $assignment), [
                'body_text' => 'Ini adalah teks draf esai akademik yang dibuat oleh mahasiswa untuk pengujian sistem notifikasi lengkap.',
            ]);

        $this->assertEquals(1, $this->lecturer->unreadNotifications()->count());
        $notif = $this->lecturer->unreadNotifications()->first();
        $this->assertEquals('Submisi Draf Tugas Baru', $notif->data['title']);
        $this->assertEquals('assignment', $notif->data['category']);
    }

    public function test_student_receives_notification_when_graded(): void
    {
        // 1. Dosen membuat tugas
        $this->actingAs($this->lecturer)
            ->post(route('courses.assignments.store', $this->course), [
                'title' => 'Tugas Menulis Esai',
                'instructions' => 'Instruksi tugas',
                'feedback_language' => 'id',
                'feedback_level' => 'balanced',
            ]);

        $assignment = WritingAssignment::where('course_id', $this->course->id)->firstOrFail();

        // 2. Mahasiswa mengirim draf
        $this->actingAs($this->student)
            ->post(route('assignments.revisions.store', $assignment), [
                'body_text' => 'Ini adalah teks draf esai akademik mahasiswa yang siap dinilai oleh dosen pengampu.',
            ]);

        $submission = $assignment->submissions()->where('student_id', $this->student->id)->firstOrFail();

        // Bersihkan notifikasi awal agar pengujian penilaian spesifik
        $this->student->notifications()->delete();

        // 3. Dosen menilai draf mahasiswa
        $this->actingAs($this->lecturer)
            ->post(route('courses.assignments.grade', [$this->course, $assignment, $submission]), [
                'score' => 88.5,
                'feedback_notes' => 'Argumen sangat kuat dan struktur paragraf koheren.',
            ]);

        $this->assertEquals(1, $this->student->unreadNotifications()->count());
        $notif = $this->student->unreadNotifications()->first();
        $this->assertEquals('Nilai & Evaluasi Tugas Dirilis', $notif->data['title']);
        $this->assertEquals('grade', $notif->data['category']);
        $this->assertStringContainsString('88.5/100', $notif->data['message']);
    }

    public function test_student_receives_notification_when_session_attendance_opened(): void
    {
        $this->actingAs($this->lecturer)
            ->post(route('courses.sessions.quick-open', $this->course), [
                'session_number' => 1,
                'topic' => 'Diskusi Pendahuluan',
                'duration_minutes' => 60,
            ]);

        $this->assertEquals(1, $this->student->unreadNotifications()->count());
        $notif = $this->student->unreadNotifications()->first();
        $this->assertEquals('attendance', $notif->data['category']);
        $this->assertStringContainsString('Presensi Sesi Dibuka', $notif->data['title']);
    }

    public function test_user_can_view_notifications_index_page(): void
    {
        $this->student->notify(new AcademicNotification(
            title: 'Notifikasi Uji',
            message: 'Isi pesan uji',
            actionUrl: route('courses.show', $this->course),
            category: 'general'
        ));

        $response = $this->actingAs($this->student)
            ->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('Notifikasi Akademik');
        $response->assertSee('Notifikasi Uji');
        $response->assertSee('Isi pesan uji');
    }

    public function test_user_can_mark_notification_as_read_and_redirect(): void
    {
        $this->student->notify(new AcademicNotification(
            title: 'Notifikasi Uji',
            message: 'Isi pesan uji',
            actionUrl: route('courses.show', $this->course),
            category: 'general'
        ));

        $notif = $this->student->unreadNotifications()->first();
        $this->assertNotNull($notif);

        $response = $this->actingAs($this->student)
            ->get(route('notifications.read', ['id' => $notif->id]));

        $response->assertRedirect(route('courses.show', $this->course));
        $this->assertEquals(0, $this->student->unreadNotifications()->count());
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $this->student->notify(new AcademicNotification('Notif 1', 'Pesan 1', '/'));
        $this->student->notify(new AcademicNotification('Notif 2', 'Pesan 2', '/'));

        $this->assertEquals(2, $this->student->unreadNotifications()->count());

        $response = $this->actingAs($this->student)
            ->post(route('notifications.mark-all-read'));

        $response->assertRedirect();
        $this->assertEquals(0, $this->student->unreadNotifications()->count());
    }

    public function test_user_can_delete_notification(): void
    {
        $this->student->notify(new AcademicNotification('Notif 1', 'Pesan 1', '/'));
        $notif = $this->student->notifications()->first();

        $response = $this->actingAs($this->student)
            ->delete(route('notifications.destroy', ['id' => $notif->id]));

        $response->assertRedirect();
        $this->assertEquals(0, $this->student->notifications()->count());
    }
}
