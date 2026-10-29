<?php

namespace App\Http\Controllers;

use App\Models\AssignmentQuestion;
use App\Models\AssignmentQuestionAnswer;
use App\Models\CourseStudent;
use App\Models\WritingAssignment;
use App\Models\WritingSubmission;
use App\Notifications\AcademicNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssignmentExamController extends Controller
{
    /**
     * Halaman Kelola Butir Soal (Khusus Dosen Pengampu & Admin)
     */
    public function manageQuestions(WritingAssignment $assignment): View
    {
        $user = Auth::user();
        abort_unless(
            $user->id === $assignment->course?->lecturer_id || $user->role === 'admin' || $user->id === $assignment->created_by,
            403,
            'Hanya dosen pengampu mata kuliah yang berhak mengelola butir soal.'
        );

        $assignment->load([
            'questions' => fn ($q) => $q->orderBy('order'),
            'course.lecturer',
        ]);

        return view('assignments.questions', compact('assignment'));
    }

    /**
     * Tambah Butir Soal Baru
     */
    public function storeQuestion(Request $request, WritingAssignment $assignment): RedirectResponse
    {
        $user = Auth::user();
        abort_unless(
            $user->id === $assignment->course?->lecturer_id || $user->role === 'admin' || $user->id === $assignment->created_by,
            403
        );

        $data = $request->validate([
            'question_type' => ['required', 'string', 'in:multiple_choice,short_answer,essay'],
            'stimulus_type' => ['nullable', 'string', 'in:audio,reading_text,none'],
            'prompt' => ['required', 'string', 'max:5000'],
            'options' => ['nullable', 'array'],
            'answer_key' => ['nullable', 'string', 'max:100'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'explanation' => ['nullable', 'string', 'max:5000'],
        ]);

        $optionsJson = null;
        if ($data['question_type'] === 'multiple_choice' && ! empty($request->input('options'))) {
            $optionsJson = [];
            foreach ($request->input('options') as $key => $text) {
                if (trim((string) $text) !== '') {
                    $optionsJson[] = [
                        'key' => strtoupper(trim((string) $key)),
                        'text' => trim((string) $text),
                    ];
                }
            }
        }

        $nextOrder = (int) $assignment->questions()->max('order') + 1;

        $assignment->questions()->create([
            'question_type' => $data['question_type'],
            'stimulus_type' => $data['stimulus_type'] ?? null,
            'prompt' => trim($data['prompt']),
            'options_json' => $optionsJson,
            'answer_key' => $data['answer_key'] ? strtoupper(trim($data['answer_key'])) : null,
            'points' => (int) $data['points'],
            'order' => $nextOrder,
            'explanation' => ! empty($data['explanation']) ? trim($data['explanation']) : null,
        ]);

        return back()->with('status', 'Butir soal baru berhasil ditambahkan ke tugas.');
    }

    /**
     * Hapus Butir Soal
     */
    public function destroyQuestion(WritingAssignment $assignment, AssignmentQuestion $question): RedirectResponse
    {
        $user = Auth::user();
        abort_unless(
            $user->id === $assignment->course?->lecturer_id || $user->role === 'admin' || $user->id === $assignment->created_by,
            403
        );
        abort_unless($question->assignment_id === $assignment->id, 404);

        $question->delete();

        return back()->with('status', 'Butir soal berhasil dihapus.');
    }

    /**
     * Stream Berkas Audio Stimulus (Mendukung HTTP Range Requests)
     */
    public function streamAudio(WritingAssignment $assignment): BinaryFileResponse|StreamedResponse
    {
        $user = Auth::user();
        $isLecturer = $user->id === $assignment->course?->lecturer_id || $user->role === 'admin';
        $isEnrolled = $assignment->course?->students()->where('student_id', $user->id)->exists();

        abort_unless($isLecturer || $isEnrolled, 403, 'Anda tidak terdaftar pada mata kuliah ini.');

        $audioPath = $assignment->config_json['audio_file_path'] ?? null;
        abort_unless($audioPath && Storage::disk('public')->exists($audioPath), 404, 'Berkas audio stimulus tidak ditemukan.');

        $fullPath = Storage::disk('public')->path($audioPath);
        $mime = mime_content_type($fullPath) ?: 'audio/mpeg';

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /**
     * Halaman Pengerjaan Ujian / Latihan Stimulus (Mahasiswa & Mode Pratinjau Dosen)
     */
    public function take(WritingAssignment $assignment): View|RedirectResponse
    {
        $user = Auth::user();
        $isLecturer = $user->id === $assignment->course?->lecturer_id || $user->role === 'admin';
        $isEnrolled = $assignment->course?->students()->where('student_id', $user->id)->exists();

        abort_unless($isLecturer || $isEnrolled, 403, 'Anda tidak memiliki akses ke tugas ini.');

        $assignment->load(['questions' => fn ($q) => $q->orderBy('order'), 'course.lecturer']);

        // Jika dosen, buka dalam mode pratinjau
        if ($isLecturer) {
            $submission = null;
            $remainingSeconds = ($assignment->time_limit_minutes ?? 60) * 60;

            return view('assignments.exam', [
                'assignment' => $assignment,
                'submission' => $submission,
                'remainingSeconds' => $remainingSeconds,
                'isPreview' => true,
            ]);
        }

        // Mahasiswa: Ambil atau inisialisasi submisi
        $submission = WritingSubmission::firstOrCreate([
            'assignment_id' => $assignment->id,
            'student_id' => $user->id,
        ]);

        // Cek tenggat waktu jika belum mengumpulkan
        if (! in_array($submission->status, ['submitted', 'graded'])) {
            if ($assignment->due_at && $assignment->due_at->isPast()) {
                return redirect()->route('courses.show', ['course' => $assignment->course, 'tab' => 'assignments'])
                    ->withErrors(['exam' => 'Batas waktu pengumpulan tugas ini telah berakhir.']);
            }
        }

        // Catat waktu mulai jika belum pernah dimulai
        if ($submission->started_at === null) {
            $submission->update(['started_at' => now()]);
        }

        // Hitung sisa waktu jika ada timer
        $remainingSeconds = null;
        if ($assignment->time_limit_minutes) {
            $totalAllowed = $assignment->time_limit_minutes * 60;
            $elapsed = now()->diffInSeconds($submission->started_at);
            $remainingSeconds = max(0, $totalAllowed - $elapsed);

            // Jika waktu telah habis dan belum submit, otomatis selesaikan
            if ($remainingSeconds <= 0 && ! in_array($submission->status, ['submitted', 'graded'])) {
                $submission->update(['submitted_at' => now(), 'status' => 'submitted']);
            }
        }

        $submission->load(['answers.question']);

        return view('assignments.exam', [
            'assignment' => $assignment,
            'submission' => $submission,
            'remainingSeconds' => $remainingSeconds,
            'isPreview' => false,
        ]);
    }

    /**
     * Kirim Jawaban Ujian Stimulus / Hybrid Mahasiswa
     */
    public function submitExam(Request $request, WritingAssignment $assignment): RedirectResponse
    {
        $user = Auth::user();
        $isEnrolled = $assignment->course?->students()->where('student_id', $user->id)->exists();
        abort_unless($isEnrolled, 403, 'Hanya mahasiswa terdaftar yang dapat mengumpulkan jawaban.');

        $submission = WritingSubmission::firstOrCreate([
            'assignment_id' => $assignment->id,
            'student_id' => $user->id,
        ]);

        abort_if(
            in_array($submission->status, ['submitted', 'graded']) && $submission->submitted_at !== null,
            409,
            'Anda sudah pernah mengumpulkan jawaban untuk tugas ini.'
        );

        $answersInput = $request->input('answers', []);
        $essayAnswer = $request->input('essay_answer', '');

        $questions = $assignment->questions()->orderBy('order')->get();
        $mcqQuestions = $questions->where('question_type', 'multiple_choice');

        $totalMcqPoints = (float) ($mcqQuestions->sum('points') ?: 1);
        $earnedMcqPoints = 0.0;

        DB::transaction(function () use (
            $assignment,
            $submission,
            $questions,
            $mcqQuestions,
            $answersInput,
            $essayAnswer,
            $totalMcqPoints,
            &$earnedMcqPoints,
            $user
        ) {
            foreach ($questions as $question) {
                $studentVal = isset($answersInput[$question->id]) ? trim((string) $answersInput[$question->id]) : null;
                $isCorrect = null;
                $scoreAwarded = 0.0;

                if ($question->question_type === 'multiple_choice') {
                    $isCorrect = $question->answer_key !== null && strtoupper((string) $studentVal) === strtoupper((string) $question->answer_key);
                    if ($isCorrect) {
                        $scoreAwarded = (float) $question->points;
                        $earnedMcqPoints += $scoreAwarded;
                    }
                }

                AssignmentQuestionAnswer::updateOrCreate(
                    [
                        'submission_id' => $submission->id,
                        'question_id' => $question->id,
                    ],
                    [
                        'student_answer' => $studentVal,
                        'is_correct' => $isCorrect,
                        'score_awarded' => $scoreAwarded,
                    ]
                );
            }

            // Hitung skor pilihan ganda skala 0 - 100
            $mcqScore = $mcqQuestions->isNotEmpty()
                ? round(($earnedMcqPoints / $totalMcqPoints) * 100, 2)
                : null;

            // Jika format hybrid atau ada esai, simpan teks esai sebagai draf revisi WriteWise
            if (! empty($essayAnswer)) {
                $assignmentVersionId = $assignment->versions()->orderByDesc('version')->value('id');
                $rubricVersionId = $assignment->rubric()->orderByDesc('version')->value('id');
                $submission->update([
                    'assignment_version_id' => $assignmentVersionId,
                    'rubric_version_id' => $rubricVersionId,
                ]);

                $revisionNumber = (int) $submission->revisions()->max('revision_number') + 1;
                $submission->revisions()->create([
                    'created_by' => $user->id,
                    'assignment_version_id' => $assignmentVersionId,
                    'rubric_version_id' => $rubricVersionId,
                    'revision_number' => $revisionNumber,
                    'body_text' => trim($essayAnswer),
                    'submitted_at' => now(),
                ]);
            }

            // Tentukan status dan kalkulasi nilai akhir
            if ($assignment->isStimulusExam() && empty($essayAnswer)) {
                // Ujian stimulus murni (MCQ): langsung dinilai otomatis!
                $submission->update([
                    'mcq_score' => $mcqScore,
                    'score' => $mcqScore,
                    'status' => 'graded',
                    'graded_at' => now(),
                    'graded_by' => $assignment->course?->lecturer_id,
                    'submitted_at' => now(),
                ]);

                // Sinkronkan rata-rata nilai mahasiswa di kelas
                $this->syncCourseStudentGrade($assignment, $user->id);
            } else {
                // Ujian hybrid: menunggu penilaian esai oleh dosen
                $submission->update([
                    'mcq_score' => $mcqScore,
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);
            }

            // Beri notifikasi ke dosen pengampu
            if ($assignment->course && $assignment->course->lecturer) {
                $assignment->course->lecturer->notify(new AcademicNotification(
                    title: 'Submisi Ujian Selesai',
                    message: "Mahasiswa {$user->name} telah menyelesaikan ujian \"{$assignment->title}\" ({$assignment->course->name}).",
                    actionUrl: route('courses.show', ['course' => $assignment->course, 'tab' => 'assignments']),
                    icon: 'assignment',
                    category: 'assignment',
                    meta: [
                        'course_id' => $assignment->course_id,
                        'assignment_id' => $assignment->id,
                        'student_id' => $user->id,
                        'mcq_score' => $mcqScore,
                    ]
                ));
            }
        });

        $message = $assignment->isStimulusExam()
            ? 'Jawaban ujian berhasil dikumpulkan dan telah selesai dinilai otomatis oleh sistem.'
            : 'Jawaban pilihan ganda dan draf analisis berhasil dikumpulkan. Bagian esai sedang menunggu evaluasi dosen.';

        return redirect()->route('assignments.exam.take', $assignment)->with('status', $message);
    }

    /**
     * Perbarui rata-rata nilai mahasiswa pada mata kuliah
     */
    private function syncCourseStudentGrade(WritingAssignment $assignment, int $studentId): void
    {
        if (! $assignment->course_id) {
            return;
        }

        $allAssignmentIds = $assignment->course->assignments()->pluck('id');
        $averageScore = WritingSubmission::whereIn('assignment_id', $allAssignmentIds)
            ->where('student_id', $studentId)
            ->whereNotNull('score')
            ->avg('score');

        if ($averageScore !== null) {
            $average = round((float) $averageScore, 2);
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

            CourseStudent::where('course_id', $assignment->course_id)
                ->where('student_id', $studentId)
                ->update([
                    'final_grade' => $average,
                    'letter_grade' => $letter,
                ]);
        }
    }
}
