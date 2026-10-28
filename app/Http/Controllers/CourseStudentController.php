<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CourseStudentController extends Controller
{
    private function ensureCanManageCourse(Course $course): void
    {
        $user = auth()->user();
        abort_unless(
            $user && $user->isStaff() && ($user->id === $course->lecturer_id || in_array($user->role, ['admin', 'superadmin'], true)),
            403,
            'Anda tidak memiliki otoritas untuk mengelola peserta pada mata kuliah ini.'
        );
    }

    public function enroll(Request $request, Course $course, StudentController $studentController): RedirectResponse
    {
        $this->ensureCanManageCourse($course);

        $validated = $request->validate([
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:users,id'],
            'raw_list' => ['nullable', 'string', 'max:50000'],
            'cohort_year' => ['nullable', 'integer', 'min:2000', 'max:2099'],
        ]);

        $enrolledCount = 0;

        DB::transaction(function () use ($validated, $course, $studentController, &$enrolledCount) {
            // 1. Plotting dari direktori mahasiswa eksisting (checkbox)
            if (! empty($validated['student_ids'])) {
                $students = User::query()
                    ->where('role', 'student')
                    ->whereIn('id', $validated['student_ids'])
                    ->get();

                foreach ($students as $student) {
                    if (! $student->study_program_id && $course->study_program_id) {
                        $student->update(['study_program_id' => $course->study_program_id]);
                    }
                    $studentController->enrollStudentIntoCourses($student, [$course->id]);
                    $enrolledCount++;
                }
            }

            // 2. Plotting dari input / copy-paste cepat langsung di dalam kelas
            if (! empty($validated['raw_list'])) {
                $parsedRows = StudentController::parseBulkStudentLines($validated['raw_list']);

                foreach ($parsedRows as $row) {
                    $nim = $row['nim'];
                    $name = $row['name'];
                    $emailInput = $row['email'];

                    $existing = User::query()
                        ->where('role', 'student')
                        ->where(function ($q) use ($nim, $emailInput) {
                            $q->where('nim', $nim);
                            if ($emailInput) {
                                $q->orWhere('email', $emailInput);
                            }
                        })
                        ->first();

                    if ($existing) {
                        $existing->update([
                            'name' => $name,
                            'nim' => $nim,
                            'study_program_id' => $existing->study_program_id ?? $course->study_program_id,
                            'cohort_year' => $validated['cohort_year'] ?? $existing->cohort_year,
                            'is_active' => true,
                        ]);
                        $student = $existing;
                    } else {
                        $email = $emailInput ?: $studentController->generateStudentEmail($nim);
                        $student = User::create([
                            'name' => $name,
                            'email' => $email,
                            'nim' => $nim,
                            'study_program_id' => $course->study_program_id,
                            'cohort_year' => $validated['cohort_year'] ?? null,
                            'role' => 'student',
                            'is_active' => true,
                            'password' => Hash::make($nim),
                        ]);
                    }

                    $studentController->enrollStudentIntoCourses($student, [$course->id]);
                    $enrolledCount++;
                }
            }
        });

        if ($enrolledCount === 0) {
            return redirect()
                ->route('courses.show', ['course' => $course, 'tab' => 'students'])
                ->withErrors(['students' => 'Pilih minimal satu mahasiswa dari direktori atau masukkan daftar NIM dan Nama yang valid.']);
        }

        return redirect()
            ->route('courses.show', ['course' => $course, 'tab' => 'students'])
            ->with('status', "{$enrolledCount} mahasiswa berhasil di-assign ke kelas {$course->name} ({$course->class_group}).");
    }

    public function update(Request $request, Course $course, CourseStudent $courseStudent): RedirectResponse
    {
        $this->ensureCanManageCourse($course);
        abort_unless($courseStudent->course_id === $course->id, 404);

        $validated = $request->validate([
            'nim' => ['nullable', 'string', 'max:32'],
            'attendance_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $nim = ! empty($validated['nim']) ? trim($validated['nim']) : $courseStudent->nim;

        $courseStudent->update([
            'nim' => $nim,
            'attendance_rate' => (float) $validated['attendance_rate'],
        ]);

        if ($nim && $courseStudent->student) {
            $courseStudent->student->update(['nim' => $nim]);
        }

        return redirect()
            ->route('courses.show', ['course' => $course, 'tab' => 'students'])
            ->with('status', 'Data kehadiran dan NIM peserta berhasil diperbarui.');
    }

    public function unenroll(Course $course, CourseStudent $courseStudent): RedirectResponse
    {
        $this->ensureCanManageCourse($course);
        abort_unless($courseStudent->course_id === $course->id, 404);

        $studentName = $courseStudent->student->name ?? 'Mahasiswa';
        $courseStudent->delete();

        return redirect()
            ->route('courses.show', ['course' => $course, 'tab' => 'students'])
            ->with('status', "{$studentName} berhasil dikeluarkan dari kelas {$course->name}.");
    }
}
