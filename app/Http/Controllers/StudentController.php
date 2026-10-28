<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    private function ensureStaff(): void
    {
        abort_unless(auth()->check() && auth()->user()->isStaff(), 403, 'Akses manajemen mahasiswa hanya untuk Dosen dan Pengelola.');
    }

    public function index(Request $request): View
    {
        $this->ensureStaff();

        $query = User::query()
            ->where('role', 'student')
            ->with(['studyProgram.faculty', 'enrolledCourses.academicPeriod']);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('study_program_id')) {
            $query->where('study_program_id', (int) $request->input('study_program_id'));
        }

        if ($request->filled('cohort_year')) {
            $query->where('cohort_year', (int) $request->input('cohort_year'));
        }

        if ($request->filled('course_id')) {
            $courseId = (int) $request->input('course_id');
            $query->whereHas('enrolledCourses', fn ($q) => $q->where('courses.id', $courseId));
        }

        $students = $query
            ->orderByRaw('CASE WHEN nim IS NULL OR nim = "" THEN 1 ELSE 0 END')
            ->orderBy('nim')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $studyPrograms = StudyProgram::with('faculty')->orderBy('name')->get();

        $user = $request->user();
        $coursesQuery = Course::with(['academicPeriod', 'studyProgram'])->orderBy('code');
        if ($user->role === 'lecturer') {
            $coursesQuery->where('lecturer_id', $user->id);
        }
        $courses = $coursesQuery->get();

        $cohortYears = User::query()
            ->where('role', 'student')
            ->whereNotNull('cohort_year')
            ->distinct()
            ->orderByDesc('cohort_year')
            ->pluck('cohort_year');

        $totalStudents = User::where('role', 'student')->count();
        $enrolledStudentsCount = User::where('role', 'student')->whereHas('enrolledCourses')->count();

        return view('students.index', [
            'students' => $students,
            'studyPrograms' => $studyPrograms,
            'courses' => $courses,
            'cohortYears' => $cohortYears,
            'totalStudents' => $totalStudents,
            'enrolledStudentsCount' => $enrolledStudentsCount,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureStaff();

        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:32', Rule::unique('users', 'nim')],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')],
            'study_program_id' => ['nullable', 'exists:study_programs,id'],
            'cohort_year' => ['nullable', 'integer', 'min:2000', 'max:2099'],
            'password' => ['nullable', 'string', 'min:6', 'max:100'],
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['exists:courses,id'],
        ]);

        $nim = trim($validated['nim']);
        $email = ! empty($validated['email'])
            ? Str::lower(trim($validated['email']))
            : $this->generateStudentEmail($nim);

        $password = ! empty($validated['password']) ? $validated['password'] : $nim;

        $student = DB::transaction(function () use ($validated, $nim, $email, $password) {
            $student = User::create([
                'name' => trim($validated['name']),
                'email' => $email,
                'nim' => $nim,
                'study_program_id' => $validated['study_program_id'] ?? null,
                'cohort_year' => $validated['cohort_year'] ?? null,
                'role' => 'student',
                'is_active' => true,
                'password' => Hash::make($password),
            ]);

            if (! empty($validated['course_ids'])) {
                $this->enrollStudentIntoCourses($student, $validated['course_ids']);
            }

            return $student;
        });

        $courseCount = count($validated['course_ids'] ?? []);
        $courseNote = $courseCount > 0 ? " dan di-assign ke {$courseCount} mata kuliah" : '';

        return redirect()
            ->route('students.index')
            ->with('status', "Mahasiswa {$student->name} (NIM: {$student->nim}) berhasil ditambahkan{$courseNote}.");
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $this->ensureStaff();

        $validated = $request->validate([
            'raw_list' => ['required', 'string', 'max:50000'],
            'study_program_id' => ['nullable', 'exists:study_programs,id'],
            'cohort_year' => ['nullable', 'integer', 'min:2000', 'max:2099'],
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['exists:courses,id'],
        ]);

        $parsedRows = self::parseBulkStudentLines($validated['raw_list']);

        if (empty($parsedRows)) {
            return redirect()
                ->route('students.index')
                ->withErrors(['raw_list' => 'Format daftar mahasiswa tidak valid. Gunakan format: NIM ; Nama Lengkap ; Email (opsional) per baris.']);
        }

        $createdCount = 0;
        $updatedCount = 0;
        $courseIds = $validated['course_ids'] ?? [];

        DB::transaction(function () use ($parsedRows, $validated, $courseIds, &$createdCount, &$updatedCount) {
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
                        'study_program_id' => $validated['study_program_id'] ?? $existing->study_program_id,
                        'cohort_year' => $validated['cohort_year'] ?? $existing->cohort_year,
                        'is_active' => true,
                    ]);
                    $student = $existing;
                    $updatedCount++;
                } else {
                    $email = $emailInput ?: $this->generateStudentEmail($nim);
                    $student = User::create([
                        'name' => $name,
                        'email' => $email,
                        'nim' => $nim,
                        'study_program_id' => $validated['study_program_id'] ?? null,
                        'cohort_year' => $validated['cohort_year'] ?? null,
                        'role' => 'student',
                        'is_active' => true,
                        'password' => Hash::make($nim),
                    ]);
                    $createdCount++;
                }

                if (! empty($courseIds)) {
                    $this->enrollStudentIntoCourses($student, $courseIds);
                }
            }
        });

        $total = $createdCount + $updatedCount;
        $courseMsg = count($courseIds) > 0 ? ' serta berhasil di-assign ke '.count($courseIds).' mata kuliah.' : '.';

        return redirect()
            ->route('students.index')
            ->with('status', "Berhasil memproses {$total} mahasiswa ({$createdCount} baru, {$updatedCount} diperbarui){$courseMsg}");
    }

    public function edit(Request $request, User $student): View
    {
        $this->ensureStaff();
        abort_unless($student->role === 'student', 404);

        $student->load(['studyProgram.faculty', 'enrolledCourses.academicPeriod', 'enrolledCourses.studyProgram']);

        $studyPrograms = StudyProgram::with('faculty')->orderBy('name')->get();

        $user = $request->user();
        $coursesQuery = Course::with(['academicPeriod', 'studyProgram'])->orderBy('code');
        if ($user->role === 'lecturer') {
            $coursesQuery->where('lecturer_id', $user->id);
        }
        $courses = $coursesQuery->get();

        return view('students.edit', [
            'student' => $student,
            'studyPrograms' => $studyPrograms,
            'courses' => $courses,
        ]);
    }

    public function update(Request $request, User $student): RedirectResponse
    {
        $this->ensureStaff();
        abort_unless($student->role === 'student', 404);

        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:32', Rule::unique('users', 'nim')->ignore($student->id)],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($student->id)],
            'study_program_id' => ['nullable', 'exists:study_programs,id'],
            'cohort_year' => ['nullable', 'integer', 'min:2000', 'max:2099'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:6', 'max:100'],
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['exists:courses,id'],
        ]);

        $nim = trim($validated['nim']);

        DB::transaction(function () use ($request, $student, $validated, $nim) {
            $payload = [
                'nim' => $nim,
                'name' => trim($validated['name']),
                'email' => Str::lower(trim($validated['email'])),
                'study_program_id' => $validated['study_program_id'] ?? null,
                'cohort_year' => $validated['cohort_year'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ];

            if (! empty($validated['password'])) {
                $payload['password'] = Hash::make($validated['password']);
            }

            $student->update($payload);

            // Sinkronkan NIM pada seluruh mata kuliah yang diikuti mahasiswa
            CourseStudent::where('student_id', $student->id)->update(['nim' => $nim]);

            // Sinkronkan plotting mata kuliah jika dikirim dari form edit
            if ($request->has('sync_courses')) {
                $selectedCourseIds = array_map('intval', $validated['course_ids'] ?? []);
                $user = $request->user();

                $manageableCourseIds = Course::query()
                    ->when($user->role === 'lecturer', fn ($q) => $q->where('lecturer_id', $user->id))
                    ->pluck('id')
                    ->all();

                // Hapus pendaftaran kelas yang dilepas centangnya (hanya pada kelas yang dikelola dosen ini)
                CourseStudent::query()
                    ->where('student_id', $student->id)
                    ->whereIn('course_id', $manageableCourseIds)
                    ->whereNotIn('course_id', $selectedCourseIds)
                    ->delete();

                // Tambahkan pendaftaran kelas baru yang dicentang
                $allowedSelected = array_intersect($selectedCourseIds, $manageableCourseIds);
                if (! empty($allowedSelected)) {
                    $this->enrollStudentIntoCourses($student, $allowedSelected);
                }
            }
        });

        return redirect()
            ->route('students.index')
            ->with('status', "Data mahasiswa {$student->name} ({$student->nim}) berhasil diperbarui.");
    }

    public function destroy(User $student): RedirectResponse
    {
        $this->ensureStaff();
        abort_unless($student->role === 'student', 404);

        $name = $student->name;
        $nim = $student->nim;
        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('status', "Mahasiswa {$name} ({$nim}) berhasil dihapus dari direktori.");
    }

    /**
     * Daftarkan mahasiswa ke daftar mata kuliah dan sinkronkan ke tugas kelas terkait.
     *
     * @param  array<int, int|string>  $courseIds
     */
    public function enrollStudentIntoCourses(User $student, array $courseIds): void
    {
        $courses = Course::with('assignments')->whereIn('id', $courseIds)->get();

        foreach ($courses as $course) {
            CourseStudent::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'student_id' => $student->id,
                ],
                [
                    'nim' => $student->nim,
                ]
            );

            foreach ($course->assignments as $assignment) {
                $assignment->students()->syncWithoutDetaching([$student->id]);
            }
        }
    }

    public function generateStudentEmail(string $nim): string
    {
        $cleanNim = Str::lower(preg_replace('/[^a-zA-Z0-9]/', '', $nim) ?: 'mhs');
        $candidate = "{$cleanNim}@student.academia.id";
        $counter = 1;

        while (User::where('email', $candidate)->exists()) {
            $candidate = "{$cleanNim}.{$counter}@student.academia.id";
            $counter++;
        }

        return $candidate;
    }

    /**
     * Parse baris input kolektif: mendukung pemisah Tab, titik koma (;), atau koma (,).
     *
     * @return array<int, array{nim: string, name: string, email: string|null}>
     */
    public static function parseBulkStudentLines(string $rawList): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($rawList)) ?: [];
        $parsed = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = array_values(array_map('trim', preg_split('/[\t;,]+/', $line) ?: []));
            $parts = array_values(array_filter($parts, fn ($val) => $val !== ''));

            if (count($parts) < 2) {
                continue;
            }

            // Lewati baris header jika terdeteksi
            if (Str::lower($parts[0]) === 'nim' || (Str::lower($parts[0]) === 'no' && Str::lower($parts[1]) === 'nim')) {
                continue;
            }

            // Jika kolom pertama adalah nomor urut (1-3 digit) dan ada >= 3 kolom
            if (count($parts) >= 3 && preg_match('/^\d{1,3}$/', $parts[0]) && strlen($parts[1]) >= 4) {
                array_shift($parts);
            }

            $nim = $parts[0] ?? '';
            $name = $parts[1] ?? '';
            $email = isset($parts[2]) && filter_var($parts[2], FILTER_VALIDATE_EMAIL)
                ? Str::lower($parts[2])
                : null;

            if ($nim !== '' && $name !== '') {
                $parsed[] = [
                    'nim' => Str::limit($nim, 32, ''),
                    'name' => Str::limit($name, 150, ''),
                    'email' => $email,
                ];
            }
        }

        return $parsed;
    }
}
