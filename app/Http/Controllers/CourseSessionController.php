<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseSessionRequest;
use App\Models\Course;
use App\Models\CourseSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseSessionController extends Controller
{
    public function store(StoreCourseSessionRequest $request, Course $course): RedirectResponse
    {
        abort_unless($request->user()->id === $course->lecturer_id || $request->user()->role === 'admin', 403);

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

        return back()->with('status', 'Jurnal perkuliahan pertemuan ke-'.$data['session_number'].' berhasil dicatat.');
    }

    public function destroy(Request $request, Course $course, CourseSession $session): RedirectResponse
    {
        abort_unless($request->user()->id === $course->lecturer_id || $request->user()->role === 'admin', 403);

        $num = $session->session_number;
        $session->delete();

        return back()->with('status', 'Jurnal pertemuan ke-'.$num.' berhasil dihapus.');
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
