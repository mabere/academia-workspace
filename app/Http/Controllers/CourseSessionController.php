<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseSessionRequest;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\CourseSessionAttendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseSessionController extends Controller
{
    private function ensureCanManage(Request $request, Course $course): void
    {
        $user = $request->user();
        abort_unless(
            $user && $user->isStaff() && ($user->id === $course->lecturer_id || in_array($user->role, ['admin', 'superadmin'], true)),
            403,
            'Anda tidak memiliki akses untuk mengelola sesi perkuliahan pada mata kuliah ini.'
        );
    }

    public function store(StoreCourseSessionRequest $request, Course $course): RedirectResponse
    {
        $this->ensureCanManage($request, $course);

        $data = $request->validated();
        $data['course_id'] = $course->id;
        $data['status'] = 'completed';

        CourseSession::updateOrCreate(
            [
                'course_id' => $course->id,
                'session_number' => $data['session_number'],
            ],
            $data
        );

        return redirect()
            ->route('courses.show', ['course' => $course, 'tab' => 'sessions'])
            ->with('status', 'Jurnal perkuliahan pertemuan ke-'.$data['session_number'].' berhasil dicatat.');
    }

    public function destroy(Request $request, Course $course, CourseSession $session): RedirectResponse
    {
        $this->ensureCanManage($request, $course);
        abort_unless($session->course_id === $course->id, 404);

        $num = $session->session_number;
        $session->delete();

        $course->syncStudentAttendanceRates();

        return redirect()
            ->route('courses.show', ['course' => $course, 'tab' => 'sessions'])
            ->with('status', 'Jurnal pertemuan ke-'.$num.' berhasil dihapus.');
    }

    public function quickOpen(Request $request, Course $course): RedirectResponse
    {
        $this->ensureCanManage($request, $course);

        $validated = $request->validate([
            'session_number' => ['required', 'integer', 'min:1', 'max:16'],
            'topic' => ['nullable', 'string', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:360'],
        ]);

        $duration = (int) ($validated['duration_minutes'] ?? 60);
        $passcode = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);

        $session = CourseSession::updateOrCreate(
            [
                'course_id' => $course->id,
                'session_number' => $validated['session_number'],
            ],
            [
                'date' => now()->toDateString(),
                'topic' => ! empty($validated['topic']) ? $validated['topic'] : "Perkuliahan Pertemuan Ke-{$validated['session_number']}",
                'teaching_method' => 'Tatap Muka & Diskusi Kelas',
                'passcode' => $passcode,
                'passcode_expires_at' => now()->addMinutes($duration),
                'is_attendance_open' => true,
                'status' => 'completed',
                'total_students' => $course->students()->count() ?: 1,
            ]
        );

        return redirect()
            ->route('courses.show', ['course' => $course, 'tab' => 'sessions'])
            ->with('status', "Presensi Pertemuan Ke-{$session->session_number} berhasil dibuka! Kode: {$passcode} (aktif {$duration} menit).");
    }

    public function openAttendance(Request $request, Course $course, CourseSession $session): RedirectResponse
    {
        $this->ensureCanManage($request, $course);
        abort_unless($session->course_id === $course->id, 404);

        $validated = $request->validate([
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:360'],
            'custom_passcode' => ['nullable', 'string', 'size:4', 'regex:/^[0-9]{4}$/'],
        ]);

        $duration = (int) ($validated['duration_minutes'] ?? 60);
        $passcode = ! empty($validated['custom_passcode'])
            ? $validated['custom_passcode']
            : str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);

        $session->update([
            'passcode' => $passcode,
            'passcode_expires_at' => now()->addMinutes($duration),
            'is_attendance_open' => true,
            'total_students' => $course->students()->count() ?: 1,
        ]);

        return redirect()
            ->route('courses.show', ['course' => $course, 'tab' => 'sessions'])
            ->with('status', "Presensi Pertemuan Ke-{$session->session_number} dibuka dengan kode {$passcode} (aktif {$duration} menit).");
    }

    public function closeAttendance(Request $request, Course $course, CourseSession $session): RedirectResponse
    {
        $this->ensureCanManage($request, $course);
        abort_unless($session->course_id === $course->id, 404);

        $session->update([
            'is_attendance_open' => false,
            'passcode_expires_at' => now(),
        ]);

        $course->syncStudentAttendanceRates();

        return redirect()
            ->route('courses.show', ['course' => $course, 'tab' => 'sessions'])
            ->with('status', "Presensi Sesi Pertemuan Ke-{$session->session_number} telah ditutup.");
    }

    public function regeneratePasscode(Request $request, Course $course, CourseSession $session): RedirectResponse
    {
        $this->ensureCanManage($request, $course);
        abort_unless($session->course_id === $course->id, 404);

        $newPasscode = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);

        $session->update([
            'passcode' => $newPasscode,
        ]);

        return redirect()
            ->route('courses.show', ['course' => $course, 'tab' => 'sessions'])
            ->with('status', "Kode presensi Pertemuan Ke-{$session->session_number} diperbarui menjadi: {$newPasscode}.");
    }

    public function checkIn(Request $request, Course $course, CourseSession $session): RedirectResponse
    {
        $user = $request->user();
        abort_unless($course->students()->where('student_id', $user->id)->exists(), 403, 'Anda tidak terdaftar dalam mata kuliah ini.');
        abort_unless($session->course_id === $course->id, 404);

        $validated = $request->validate([
            'passcode' => ['required', 'string', 'size:4'],
        ], [
            'passcode.required' => 'Masukkan 4-digit kode presensi yang diberikan dosen.',
            'passcode.size' => 'Kode presensi harus terdiri dari 4 digit angka.',
        ]);

        if (! $session->isOpenForAttendance()) {
            return redirect()
                ->route('courses.show', ['course' => $course, 'tab' => 'sessions'])
                ->withErrors(['passcode' => 'Sesi presensi untuk pertemuan ini belum dibuka atau sudah ditutup oleh dosen pengampu.']);
        }

        if (trim($validated['passcode']) !== (string) $session->passcode) {
            return redirect()
                ->route('courses.show', ['course' => $course, 'tab' => 'sessions'])
                ->withErrors(['passcode' => 'Kode presensi salah! Pastikan memasukkan 4-digit angka yang benar dari layar kelas dosen.']);
        }

        $existing = CourseSessionAttendance::where('course_session_id', $session->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return redirect()
                ->route('courses.show', ['course' => $course, 'tab' => 'sessions'])
                ->with('status', 'Anda sudah tercatat hadir pada pertemuan ini sebelumnya.');
        }

        CourseSessionAttendance::create([
            'course_session_id' => $session->id,
            'user_id' => $user->id,
            'status' => 'present',
            'checked_in_at' => now(),
            'device_ip' => $request->ip(),
        ]);

        $session->update([
            'attendance_count' => $session->attendances()->whereIn('status', ['present', 'late'])->count(),
            'total_students' => $course->students()->count() ?: 1,
        ]);

        $course->syncStudentAttendanceRates();

        return redirect()
            ->route('courses.show', ['course' => $course, 'tab' => 'sessions'])
            ->with('status', "Presensi berhasil! Kehadiran Anda pada pertemuan ke-{$session->session_number} telah tercatat.");
    }

    public function toggleStudentAttendance(Request $request, Course $course, CourseSession $session): RedirectResponse
    {
        $this->ensureCanManage($request, $course);
        abort_unless($session->course_id === $course->id, 404);

        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'status' => ['required', 'in:present,absent,late,excused'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['status'] === 'absent') {
            CourseSessionAttendance::where('course_session_id', $session->id)
                ->where('user_id', $validated['student_id'])
                ->delete();
        } else {
            CourseSessionAttendance::updateOrCreate(
                [
                    'course_session_id' => $session->id,
                    'user_id' => $validated['student_id'],
                ],
                [
                    'status' => $validated['status'],
                    'checked_in_at' => now(),
                    'notes' => $validated['notes'] ?? 'Diperbarui secara manual oleh dosen pengampu',
                ]
            );
        }

        $session->update([
            'attendance_count' => $session->attendances()->whereIn('status', ['present', 'late'])->count(),
            'total_students' => $course->students()->count() ?: 1,
        ]);

        $course->syncStudentAttendanceRates();

        return redirect()
            ->route('courses.show', ['course' => $course, 'tab' => 'sessions'])
            ->with('status', 'Status kehadiran mahasiswa pada sesi ini berhasil diperbarui.');
    }

    public function syncAttendance(Request $request, Course $course): RedirectResponse
    {
        $this->ensureCanManage($request, $course);

        $course->syncStudentAttendanceRates();

        return redirect()
            ->route('courses.show', ['course' => $course, 'tab' => 'sessions'])
            ->with('status', 'Persentase presensi seluruh mahasiswa berhasil disinkronkan.');
    }

    public function print(Request $request, Course $course): View
    {
        $user = $request->user();
        if (! $user->isStaff()) {
            abort_unless($course->students()->where('student_id', $user->id)->exists(), 403);
        }

        $course->load([
            'academicPeriod',
            'studyProgram.faculty',
            'lecturer.lecturerProfile',
            'sessions' => fn ($q) => $q->orderBy('session_number'),
            'students.student',
        ]);

        return view('courses.sessions-print', [
            'course' => $course,
            'sessions' => $course->sessions,
            'lecturer' => $course->lecturer,
            'profile' => $course->lecturer->lecturerProfile,
        ]);
    }
}
