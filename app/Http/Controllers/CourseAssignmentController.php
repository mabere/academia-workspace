<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\Project;
use App\Models\WritingAssignment;
use App\Models\WritingSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CourseAssignmentController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        abort_unless($request->user()->id === $course->lecturer_id || $request->user()->role === 'admin', 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'instructions' => ['required', 'string', 'max:10000'],
            'due_at' => ['nullable', 'date'],
            'feedback_language' => ['nullable', 'string', 'in:id,en'],
            'feedback_level' => ['nullable', 'string', 'in:direct,indirect,balanced'],
            'rubric_name' => ['nullable', 'string', 'max:120'],
        ]);

        DB::transaction(function () use ($course, $data) {
            // Temukan atau buat wadah Proyek Tri Dharma untuk mata kuliah ini
            $project = Project::firstOrCreate(
                [
                    'owner_id' => $course->lecturer_id,
                    'academic_period_id' => $course->academic_period_id,
                    'name' => 'Perkuliahan: '.$course->name.' ('.$course->code.')',
                ],
                [
                    'description' => 'Wadah Tri Dharma pengajaran untuk mata kuliah '.$course->name.' kelas '.$course->class_group,
                    'pillars' => ['teaching'],
                    'status' => 'active',
                ]
            );

            $assignment = $course->assignments()->create([
                'project_id' => $project->id,
                'created_by' => Auth::id(),
                'title' => $data['title'],
                'instructions' => $data['instructions'],
                'feedback_language' => $data['feedback_language'] ?? 'id',
                'feedback_level' => $data['feedback_level'] ?? 'indirect',
                'status' => 'published',
                'due_at' => $data['due_at'] ?? null,
            ]);

            // Daftarkan semua mahasiswa kelas ke tugas ini secara otomatis
            $studentIds = $course->students()->pluck('student_id')->all();
            if (! empty($studentIds)) {
                $assignment->students()->sync($studentIds);
            }

            // Buat snapshot versi 1
            $assignment->versions()->create([
                'version' => 1,
                'title' => $assignment->title,
                'instructions' => $assignment->instructions,
                'feedback_language' => $assignment->feedback_language,
                'feedback_level' => $assignment->feedback_level,
                'status' => $assignment->status,
                'due_at' => $assignment->due_at,
                'created_by' => Auth::id(),
            ]);

            // Buat rubrik penilaian analitik default
            $criteria = [
                ['name' => 'Content & Argument', 'description' => 'Kesesuaian isi, kejelasan tesis, dan kedalaman argumen.', 'weight' => 1.0],
                ['name' => 'Organization & Structure', 'description' => 'Koherensi, transisi antar paragraf, dan alur pemikiran logis.', 'weight' => 1.0],
                ['name' => 'Vocabulary & Lexicon', 'description' => 'Kekayaan kosakata akademik dan ketepatan istilah bidang studi.', 'weight' => 1.0],
                ['name' => 'Language Use & Grammar', 'description' => 'Ketepatan tata bahasa, variasi kalimat, dan tanda baca.', 'weight' => 1.0],
                ['name' => 'Mechanics & Academic Style', 'description' => 'Format sitasi, ejaan, kerapian, dan konvensi akademik.', 'weight' => 1.0],
            ];

            $assignment->rubric()->create([
                'version' => 1,
                'name' => ! empty($data['rubric_name']) ? $data['rubric_name'] : 'Rubrik Analitik Penulisan Akademik',
                'scale_min' => 1,
                'scale_max' => 5,
                'criteria' => $criteria,
            ]);
        });

        return back()->with('status', 'Tugas perkuliahan "'.$data['title'].'" berhasil diterbitkan untuk seluruh mahasiswa kelas.');
    }

    public function grade(Request $request, Course $course, WritingAssignment $assignment, WritingSubmission $submission): RedirectResponse
    {
        abort_unless($request->user()->id === $course->lecturer_id || $request->user()->role === 'admin', 403);

        $data = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'feedback_notes' => ['nullable', 'string', 'max:5000'],
            'criteria_scores' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($course, $submission, $data) {
            $submission->update([
                'score' => $data['score'],
                'graded_by' => Auth::id(),
                'graded_at' => now(),
                'status' => 'graded',
            ]);

            // Jika ada revisi terbaru, simpan atau mutakhirkan laporan feedback dosen
            $latestRevision = $submission->revisions()->latest('revision_number')->first();
            if ($latestRevision) {
                $feedbackReport = $latestRevision->feedbackReports()->first();
                $payload = [
                    'overall_summary' => $data['feedback_notes'] ?: 'Penilaian dan umpan balik tugas oleh dosen pengampu.',
                    'score' => $data['score'],
                    'criteria_scores' => $data['criteria_scores'] ?? [],
                    'evaluated_by' => Auth::user()->name,
                ];

                if ($feedbackReport) {
                    $feedbackReport->update([
                        'feedback_json' => $payload,
                        'edited_by' => Auth::id(),
                        'status' => 'published',
                        'published_at' => now(),
                    ]);
                } else {
                    $latestRevision->feedbackReports()->create([
                        'edited_by' => Auth::id(),
                        'feedback_json' => $payload,
                        'prompt_version' => 'manual-evaluation-v1',
                        'status' => 'published',
                        'published_at' => now(),
                    ]);
                }
            }

            // Hitung ulang akumulasi nilai akhir mahasiswa pada kelas ini
            $this->recalculateCourseStudentGrade($course, $submission->student_id);
        });

        return back()->with('status', 'Nilai ('.$data['score'].') dan umpan balik tugas berhasil disimpan.');
    }

    public function printGrades(Request $request, Course $course): View
    {
        $user = $request->user();
        if (! $user->isStaff()) {
            abort_unless($course->students()->where('student_id', $user->id)->exists(), 403);
        }

        $course->load([
            'academicPeriod',
            'studyProgram.faculty',
            'lecturer.lecturerProfile',
            'assignments.submissions',
            'students.student',
        ]);

        return view('courses.grades-print', [
            'course' => $course,
            'students' => $course->students,
            'assignments' => $course->assignments,
            'lecturer' => $course->lecturer,
            'profile' => $course->lecturer->lecturerProfile,
        ]);
    }

    private function recalculateCourseStudentGrade(Course $course, int $studentId): void
    {
        $courseStudent = CourseStudent::where('course_id', $course->id)->where('student_id', $studentId)->first();
        if (! $courseStudent) {
            return;
        }

        // Ambil semua submisi tugas mahasiswa pada mata kuliah ini
        $scores = WritingSubmission::whereHas('assignment', fn ($q) => $q->where('course_id', $course->id))
            ->where('student_id', $studentId)
            ->whereNotNull('score')
            ->pluck('score');

        if ($scores->isNotEmpty()) {
            $average = round($scores->average(), 2);
            $letter = match (true) {
                $average >= 85.0 => 'A',
                $average >= 80.0 => 'A-',
                $average >= 75.0 => 'B+',
                $average >= 70.0 => 'B',
                $average >= 65.0 => 'B-',
                $average >= 60.0 => 'C+',
                $average >= 55.0 => 'C',
                $average >= 40.0 => 'D',
                default => 'E',
            };

            $courseStudent->update([
                'final_grade' => $average,
                'letter_grade' => $letter,
            ]);
        }
    }
}
