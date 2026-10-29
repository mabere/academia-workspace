<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\CourseStudent;
use App\Models\Faculty;
use App\Models\LecturerProfile;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseSessionAttendanceTest extends TestCase
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

    public function test_lecturer_can_quick_open_session_attendance_with_generated_passcode(): void
    {
        $response = $this->actingAs($this->lecturer)
            ->post(route('courses.sessions.quick-open', $this->course), [
                'session_number' => 1,
                'topic' => 'Pengenalan Silabus & Esai Akademik',
                'duration_minutes' => 60,
            ]);

        $response->assertRedirect(route('courses.show', ['course' => $this->course, 'tab' => 'sessions']));
        $response->assertSessionHas('status');

        $session = CourseSession::where('course_id', $this->course->id)->where('session_number', 1)->first();
        $this->assertNotNull($session);
        $this->assertTrue($session->is_attendance_open);
        $this->assertNotNull($session->passcode);
        $this->assertEquals(4, strlen($session->passcode));
        $this->assertTrue($session->isOpenForAttendance());
    }

    public function test_lecturer_can_open_and_close_attendance_on_existing_session(): void
    {
        $session = CourseSession::create([
            'course_id' => $this->course->id,
            'session_number' => 2,
            'topic' => 'Struktur Paragraf & Tesis',
            'date' => now()->toDateString(),
            'attendance_count' => 0,
            'total_students' => 1,
            'status' => 'completed',
        ]);

        // Open attendance
        $openResponse = $this->actingAs($this->lecturer)
            ->post(route('courses.sessions.open-attendance', [$this->course, $session]), [
                'duration_minutes' => 45,
                'custom_passcode' => '5831',
            ]);

        $openResponse->assertRedirect();
        $session->refresh();
        $this->assertTrue($session->is_attendance_open);
        $this->assertEquals('5831', $session->passcode);

        // Close attendance
        $closeResponse = $this->actingAs($this->lecturer)
            ->post(route('courses.sessions.close-attendance', [$this->course, $session]));

        $closeResponse->assertRedirect();
        $session->refresh();
        $this->assertFalse($session->is_attendance_open);
        $this->assertFalse($session->isOpenForAttendance());
    }

    public function test_enrolled_student_can_check_in_with_valid_passcode(): void
    {
        $session = CourseSession::create([
            'course_id' => $this->course->id,
            'session_number' => 1,
            'topic' => 'Pengantar Esai',
            'date' => now()->toDateString(),
            'passcode' => '7294',
            'passcode_expires_at' => now()->addMinutes(30),
            'is_attendance_open' => true,
            'attendance_count' => 0,
            'total_students' => 1,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->student)
            ->post(route('courses.sessions.check-in', [$this->course, $session]), [
                'passcode' => '7294',
            ]);

        $response->assertRedirect(route('courses.show', ['course' => $this->course, 'tab' => 'sessions']));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('course_session_attendances', [
            'course_session_id' => $session->id,
            'user_id' => $this->student->id,
            'status' => 'present',
        ]);

        $session->refresh();
        $this->assertEquals(1, $session->attendance_count);

        $courseStudent = CourseStudent::where('course_id', $this->course->id)
            ->where('student_id', $this->student->id)
            ->first();
        $this->assertEquals(100.0, (float) $courseStudent->attendance_rate);
    }

    public function test_student_cannot_check_in_with_invalid_passcode(): void
    {
        $session = CourseSession::create([
            'course_id' => $this->course->id,
            'session_number' => 1,
            'topic' => 'Pengantar Esai',
            'date' => now()->toDateString(),
            'passcode' => '7294',
            'passcode_expires_at' => now()->addMinutes(30),
            'is_attendance_open' => true,
            'attendance_count' => 0,
            'total_students' => 1,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->student)
            ->post(route('courses.sessions.check-in', [$this->course, $session]), [
                'passcode' => '0000',
            ]);

        $response->assertSessionHasErrors('passcode');
        $this->assertDatabaseMissing('course_session_attendances', [
            'course_session_id' => $session->id,
            'user_id' => $this->student->id,
        ]);
    }

    public function test_student_cannot_check_in_when_session_attendance_is_closed(): void
    {
        $session = CourseSession::create([
            'course_id' => $this->course->id,
            'session_number' => 1,
            'topic' => 'Pengantar Esai',
            'date' => now()->toDateString(),
            'passcode' => '7294',
            'passcode_expires_at' => now()->subMinutes(10), // expired
            'is_attendance_open' => false,
            'attendance_count' => 0,
            'total_students' => 1,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->student)
            ->post(route('courses.sessions.check-in', [$this->course, $session]), [
                'passcode' => '7294',
            ]);

        $response->assertSessionHasErrors('passcode');
        $this->assertDatabaseMissing('course_session_attendances', [
            'course_session_id' => $session->id,
            'user_id' => $this->student->id,
        ]);
    }

    public function test_non_enrolled_student_cannot_check_in(): void
    {
        $otherStudent = User::factory()->create(['role' => 'student']);

        $session = CourseSession::create([
            'course_id' => $this->course->id,
            'session_number' => 1,
            'topic' => 'Pengantar Esai',
            'date' => now()->toDateString(),
            'passcode' => '7294',
            'passcode_expires_at' => now()->addMinutes(30),
            'is_attendance_open' => true,
            'attendance_count' => 0,
            'total_students' => 1,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($otherStudent)
            ->post(route('courses.sessions.check-in', [$this->course, $session]), [
                'passcode' => '7294',
            ]);

        $response->assertForbidden();
    }

    public function test_lecturer_can_manually_toggle_student_attendance(): void
    {
        $session = CourseSession::create([
            'course_id' => $this->course->id,
            'session_number' => 1,
            'topic' => 'Pengantar Esai',
            'date' => now()->toDateString(),
            'attendance_count' => 0,
            'total_students' => 1,
            'status' => 'completed',
        ]);

        // Mark present manually
        $response = $this->actingAs($this->lecturer)
            ->post(route('courses.sessions.attendances.toggle', [$this->course, $session]), [
                'student_id' => $this->student->id,
                'status' => 'present',
                'notes' => 'Izin lisan hadir terlambat',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('course_session_attendances', [
            'course_session_id' => $session->id,
            'user_id' => $this->student->id,
            'status' => 'present',
        ]);

        // Mark absent manually
        $absentResponse = $this->actingAs($this->lecturer)
            ->post(route('courses.sessions.attendances.toggle', [$this->course, $session]), [
                'student_id' => $this->student->id,
                'status' => 'absent',
            ]);

        $absentResponse->assertRedirect();
        $this->assertDatabaseMissing('course_session_attendances', [
            'course_session_id' => $session->id,
            'user_id' => $this->student->id,
        ]);
    }

    public function test_lecturer_can_regenerate_session_passcode(): void
    {
        $session = CourseSession::create([
            'course_id' => $this->course->id,
            'session_number' => 1,
            'topic' => 'Pengantar Esai',
            'date' => now()->toDateString(),
            'passcode' => '1111',
            'passcode_expires_at' => now()->addMinutes(30),
            'is_attendance_open' => true,
            'attendance_count' => 0,
            'total_students' => 1,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->lecturer)
            ->post(route('courses.sessions.regenerate-code', [$this->course, $session]));

        $response->assertRedirect();
        $session->refresh();
        $this->assertNotEquals('1111', $session->passcode);
        $this->assertEquals(4, strlen($session->passcode));
    }

    public function test_active_session_attendance_rendered_in_course_show_for_student_and_lecturer(): void
    {
        $session = CourseSession::create([
            'course_id' => $this->course->id,
            'session_number' => 1,
            'topic' => 'Diskusi Paragraf Argumentatif',
            'date' => now()->toDateString(),
            'passcode' => '8492',
            'passcode_expires_at' => now()->addMinutes(60),
            'is_attendance_open' => true,
            'attendance_count' => 0,
            'total_students' => 1,
            'status' => 'completed',
        ]);

        // Lecturer sees passcode prominently
        $lecturerView = $this->actingAs($this->lecturer)->get(route('courses.show', ['course' => $this->course, 'tab' => 'sessions']));
        $lecturerView->assertOk();
        $lecturerView->assertSee('8492');
        $lecturerView->assertSee('Presensi Kelas Sedang Dibuka');

        // Student sees prompt to enter passcode
        $studentView = $this->actingAs($this->student)->get(route('courses.show', ['course' => $this->course, 'tab' => 'sessions']));
        $studentView->assertOk();
        $studentView->assertSee('Presensi Perkuliahan Sedang Dibuka');
        $studentView->assertSee('Kirim Presensi');
    }
}
