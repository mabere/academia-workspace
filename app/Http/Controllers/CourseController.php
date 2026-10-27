<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\StudyProgram;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $academicPeriods = AcademicPeriod::orderByDesc('start_date')->get();

        $selectedPeriodId = $request->filled('academic_period_id')
            ? (int) $request->input('academic_period_id')
            : ($academicPeriods->firstWhere('is_active', true)?->id ?? $academicPeriods->first()?->id);

        if ($user->isStaff()) {
            $coursesQuery = Course::query()->where('lecturer_id', $user->id);
            if ($user->role === 'admin' && $request->filled('all_lecturers')) {
                $coursesQuery = Course::query();
            }
        } else {
            $coursesQuery = Course::query()->whereHas('students', fn ($q) => $q->where('student_id', $user->id));
        }

        if ($selectedPeriodId) {
            $coursesQuery->where('academic_period_id', $selectedPeriodId);
        }

        $courses = $coursesQuery->with(['studyProgram', 'academicPeriod', 'lecturer'])
            ->withCount(['materials', 'sessions', 'students', 'assignments'])
            ->orderBy('code')
            ->get();

        $totalSks = $courses->sum('credit_hours');

        return view('courses.index', [
            'courses' => $courses,
            'academicPeriods' => $academicPeriods,
            'selectedPeriodId' => $selectedPeriodId,
            'totalSks' => $totalSks,
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->isStaff(), 403);

        $academicPeriods = AcademicPeriod::orderByDesc('start_date')->get();
        $studyPrograms = StudyProgram::orderBy('name')->get();

        return view('courses.create', [
            'academicPeriods' => $academicPeriods,
            'studyPrograms' => $studyPrograms,
        ]);
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['lecturer_id'] = $request->user()->id;

        $course = Course::create($data);

        return redirect()->route('courses.show', $course)->with('status', 'Mata kuliah '.$course->name.' ('.$course->code.') berhasil ditambahkan.');
    }

    public function show(Request $request, Course $course): View
    {
        $user = $request->user();

        // Otorisasi: dosen pengampu, admin, atau mahasiswa terdaftar
        if (! $user->isStaff()) {
            abort_unless($course->students()->where('student_id', $user->id)->exists(), 403, 'Anda tidak terdaftar pada mata kuliah ini.');
        }

        $course->load([
            'academicPeriod',
            'studyProgram.faculty',
            'lecturer.lecturerProfile',
            'materials.uploader',
            'sessions',
            'students.student',
            'assignments' => fn ($q) => $q->withCount(['submissions', 'students']),
        ]);

        return view('courses.show', [
            'course' => $course,
            'materials' => $course->materials,
            'sessions' => $course->sessions,
            'students' => $course->students,
            'assignments' => $course->assignments,
        ]);
    }

    public function edit(Course $course): View
    {
        abort_unless(auth()->user()->isStaff() && ($authId = auth()->id()) === $course->lecturer_id || auth()->user()->role === 'admin', 403);

        $academicPeriods = AcademicPeriod::orderByDesc('start_date')->get();
        $studyPrograms = StudyProgram::orderBy('name')->get();

        return view('courses.edit', [
            'course' => $course,
            'academicPeriods' => $academicPeriods,
            'studyPrograms' => $studyPrograms,
        ]);
    }

    public function update(StoreCourseRequest $request, Course $course): RedirectResponse
    {
        abort_unless($request->user()->id === $course->lecturer_id || $request->user()->role === 'admin', 403);

        $course->update($request->validated());

        return redirect()->route('courses.show', $course)->with('status', 'Informasi mata kuliah berhasil diperbarui.');
    }

    public function destroy(Request $request, Course $course): RedirectResponse
    {
        abort_unless($request->user()->id === $course->lecturer_id || $request->user()->role === 'admin', 403);

        $name = $course->name;
        $course->delete();

        return redirect()->route('courses.index')->with('status', 'Mata kuliah '.$name.' berhasil dihapus.');
    }
}
