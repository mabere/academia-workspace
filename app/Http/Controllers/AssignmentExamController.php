<?php

namespace App\Http\Controllers;

use App\Models\AssignmentQuestion;
use App\Models\AssignmentQuestionAnswer;
use App\Models\CourseStudent;
use App\Models\WritingAssignment;
use App\Models\WritingSubmission;
use App\Models\WritingSubmissionAttempt;
use App\Notifications\AcademicNotification;
use Illuminate\Http\JsonResponse;
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
            $user->id === $assignment->course?->lecturer_id || in_array($user->role, ['admin', 'superadmin'], true) || $user->id === $assignment->created_by,
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
            $user->id === $assignment->course?->lecturer_id || in_array($user->role, ['admin', 'superadmin'], true) || $user->id === $assignment->created_by,
            403
        );

        $data = $request->validate([
            'question_type' => ['required', 'string', 'in:multiple_choice,short_answer,essay'],
            'stimulus_type' => ['nullable', 'string', 'in:audio,reading_text,none'],
            'prompt' => ['required', 'string', 'max:5000'],
            'options' => ['nullable', 'array', 'max:10'],
            'options.*' => ['nullable', 'string', 'max:500'],
            'answer_key' => ['nullable', 'string', 'max:100'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'explanation' => ['nullable', 'string', 'max:5000'],
        ]);

        $optionsJson = null;
        if ($data['question_type'] === 'multiple_choice') {
            $options = $request->input('options', []);
            $validKeys = collect($options)
                ->filter(fn ($text) => trim((string) $text) !== '')
                ->keys()
                ->map(fn ($key) => strtoupper(trim((string) $key)))
                ->all();
            $answerKey = strtoupper(trim((string) ($data['answer_key'] ?? '')));

            if (count($validKeys) < 2 || ! in_array($answerKey, $validKeys, true)) {
                return back()->withErrors(['answer_key' => 'Pilihan ganda memerlukan minimal dua opsi dan kunci jawaban yang sesuai.'])->withInput();
            }

            $optionsJson = [];
            foreach ($options as $key => $text) {
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
            $user->id === $assignment->course?->lecturer_id || in_array($user->role, ['admin', 'superadmin'], true) || $user->id === $assignment->created_by,
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
        $isLecturer = $user->id === $assignment->course?->lecturer_id || in_array($user->role, ['admin', 'superadmin'], true);
        $isEnrolled = $assignment->course?->students()->where('student_id', $user->id)->exists();

        abort_unless($isLecturer || $isEnrolled, 403, 'Anda tidak terdaftar pada mata kuliah ini.');
        abort_unless($isLecturer || $assignment->status === 'published', 404);

        if (! $isLecturer) {
            $attempt = WritingSubmission::where('assignment_id', $assignment->id)
                ->where('student_id', $user->id)
                ->where('status', 'draft')
                ->whereNotNull('started_at')
                ->exists();
            abort_unless($attempt, 403, 'Mulai ujian sebelum mengakses audio stimulus.');
        }

        $audioPath = $assignment->config_json['audio_file_path'] ?? null;
        abort_unless($audioPath, 404, 'Berkas audio stimulus tidak ditemukan.');

        $disk = null;
        if (Storage::disk('local')->exists($audioPath)) {
            $disk = 'local';
        } elseif (Storage::disk('public')->exists($audioPath)) {
            $disk = 'public';
        }

        abort_unless($disk !== null, 404, 'Berkas audio stimulus tidak ditemukan.');

        $fullPath = Storage::disk($disk)->path($audioPath);
        $mime = mime_content_type($fullPath) ?: 'audio/mpeg';

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /**
     * Catat Pemutaran Audio Stimulus di Sisi Server (Anti-Reset Refresh)
     */
    public function recordAudioPlay(Request $request, WritingAssignment $assignment): JsonResponse
    {
        $user = $request->user();
        $isLecturer = $user->id === $assignment->course?->lecturer_id || in_array($user->role, ['admin', 'superadmin'], true);
        $isEnrolled = $assignment->course?->students()->where('student_id', $user->id)->exists();

        abort_unless($isLecturer || $isEnrolled, 403, 'Anda tidak memiliki akses ke tugas ini.');
        abort_unless($isLecturer || $assignment->status === 'published', 404);

        $maxPlays = (int) ($assignment->config_json['audio_max_plays'] ?? 2);

        if ($isLecturer) {
            return response()->json([
                'plays_used' => 0,
                'plays_left' => $maxPlays,
                'exhausted' => false,
            ]);
        }

        $submission = WritingSubmission::where([
            'assignment_id' => $assignment->id,
            'student_id' => $user->id,
        ])->first();
        abort_unless($submission, 409, 'Mulai ujian sebelum mengirim jawaban.');
        abort_unless($submission->started_at && $submission->status === 'draft', 409, 'Audio hanya tersedia selama pengerjaan ujian.');

        $playsUsed = (int) ($submission->current_milestone_id ?? 0);

        if ($maxPlays > 0 && $playsUsed >= $maxPlays) {
            return response()->json([
                'plays_used' => $playsUsed,
                'plays_left' => 0,
                'exhausted' => true,
            ], 429);
        }

        $playsUsed++;
        $submission->update(['current_milestone_id' => $playsUsed]);
        $playsLeft = $maxPlays > 0 ? max(0, $maxPlays - $playsUsed) : 0;

        return response()->json([
            'plays_used' => $playsUsed,
            'plays_left' => $playsLeft,
            'exhausted' => $maxPlays > 0 && $playsLeft <= 0,
        ]);
    }

    /**
     * Halaman Pengerjaan Ujian / Latihan Stimulus (Mahasiswa & Mode Pratinjau Dosen)
     */
    public function take(WritingAssignment $assignment): View|RedirectResponse
    {
        $user = Auth::user();
        $isLecturer = $user->id === $assignment->course?->lecturer_id || in_array($user->role, ['admin', 'superadmin'], true);
        $isEnrolled = $assignment->course?->students()->where('student_id', $user->id)->exists();

        abort_unless($isLecturer || $isEnrolled, 403, 'Anda tidak memiliki akses ke tugas ini.');
        abort_unless($isLecturer || $assignment->status === 'published', 404);

        $assignment->load(['questions' => fn ($q) => $q->orderBy('order'), 'course.lecturer']);

        $maxPlays = (int) ($assignment->config_json['audio_max_plays'] ?? 2);

        // Jika dosen, buka dalam mode pratinjau
        if ($isLecturer) {
            $submission = null;
            $remainingSeconds = ($assignment->time_limit_minutes ?? 60) * 60;

            return view('assignments.exam', [
                'assignment' => $assignment,
                'submission' => $submission,
                'remainingSeconds' => $remainingSeconds,
                'playsLeft' => $maxPlays,
                'isPreview' => true,
            ]);
        }

        // Membuka halaman soal tidak membuat submission ataupun memulai timer.
        $submission = WritingSubmission::where([
            'assignment_id' => $assignment->id,
            'student_id' => $user->id,
        ])->first();

        // Cek tenggat waktu jika belum mengumpulkan
        if (! $submission || ! in_array($submission->status, ['submitted', 'graded'])) {
            if ($assignment->due_at && $assignment->due_at->isPast()) {
                return redirect()->route('courses.show', ['course' => $assignment->course, 'tab' => 'assignments'])
                    ->withErrors(['exam' => 'Batas waktu pengumpulan tugas ini telah berakhir.']);
            }
        }

        // Hitung sisa waktu jika ada timer
        $remainingSeconds = null;
        if ($assignment->time_limit_minutes && $submission?->started_at) {
            $totalAllowed = $assignment->time_limit_minutes * 60;
            $elapsed = (int) abs(now()->diffInSeconds($submission->started_at));
            $remainingSeconds = max(0, $totalAllowed - $elapsed);
        }

        $playsUsed = (int) ($submission?->current_milestone_id ?? 0);
        $playsLeft = $maxPlays > 0 ? max(0, $maxPlays - $playsUsed) : 0;

        if ($submission) {
            $submission->setRelation('answers', $submission->answers()
                ->where('attempt_number', $submission->current_attempt ?: 1)
                ->with('question')
                ->get());
        }

        return view('assignments.exam', [
            'assignment' => $assignment,
            'submission' => $submission,
            'remainingSeconds' => $remainingSeconds,
            'playsLeft' => $playsLeft,
            'isPreview' => false,
        ]);
    }

    public function startExam(Request $request, WritingAssignment $assignment): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->role === 'student' && $assignment->status === 'published', 403);
        abort_unless($assignment->course?->students()->where('student_id', $user->id)->exists(), 403);
        abort_unless($assignment->due_at === null || $assignment->due_at->isFuture(), 409, 'Batas waktu pengerjaan tugas ini telah berakhir.');

        $submission = WritingSubmission::firstOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $user->id],
            ['status' => 'draft', 'started_at' => now(), 'current_attempt' => 1]
        );

        if ($submission->submitted_at && ! $assignment->allow_revisions) {
            abort(409, 'Dosen tidak mengizinkan revisi jawaban untuk tugas ini.');
        }

        $attemptNumber = (int) ($submission->current_attempt ?: 1);
        if ($submission->submitted_at && $assignment->allow_revisions) {
            $this->snapshotAttempt($submission, $attemptNumber);
            $attemptNumber++;
            $submission->update(['current_attempt' => $attemptNumber, 'started_at' => now(), 'status' => 'draft']);
        } elseif (! $submission->started_at) {
            $submission->update(['current_attempt' => $attemptNumber, 'started_at' => now(), 'status' => 'draft']);
        }

        WritingSubmissionAttempt::firstOrCreate(
            ['submission_id' => $submission->id, 'attempt_number' => $attemptNumber],
            ['status' => 'draft', 'started_at' => $submission->started_at ?? now()]
        );

        return redirect()->route('assignments.exam.take', $assignment);
    }

    public function saveExamDraft(Request $request, WritingAssignment $assignment): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->role === 'student' && $assignment->status === 'published', 403);
        abort_unless($assignment->course?->students()->where('student_id', $user->id)->exists(), 403);
        abort_unless($assignment->due_at === null || $assignment->due_at->isFuture(), 409, 'Batas waktu pengerjaan tugas ini telah berakhir.');

        $data = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'string', 'max:10000'],
            'essay_answer' => ['nullable', 'string', 'max:50000'],
        ]);

        $submission = WritingSubmission::firstOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $user->id],
            ['status' => 'draft', 'started_at' => now(), 'current_attempt' => 1]
        );
        abort_if($submission->submitted_at && ! $assignment->allow_revisions, 409, 'Dosen tidak mengizinkan revisi jawaban untuk tugas ini.');

        if ($submission->started_at && $assignment->time_limit_minutes) {
            abort_if(now()->diffInSeconds($submission->started_at) > ($assignment->time_limit_minutes * 60) + 300, 409, 'Waktu ujian telah habis.');
        }

        DB::transaction(function () use ($assignment, $submission, $data): void {
            foreach ($assignment->questions as $question) {
                AssignmentQuestionAnswer::updateOrCreate(
                    ['submission_id' => $submission->id, 'question_id' => $question->id, 'attempt_number' => $submission->current_attempt ?: 1],
                    ['student_answer' => $data['answers'][$question->id] ?? null, 'is_correct' => null, 'score_awarded' => 0]
                );
            }

            $submission->update([
                'status' => 'draft',
                'draft_body_text' => trim((string) ($data['essay_answer'] ?? '')),
                'draft_saved_at' => now(),
            ]);

            WritingSubmissionAttempt::updateOrCreate(
                ['submission_id' => $submission->id, 'attempt_number' => $submission->current_attempt ?: 1],
                ['status' => 'draft', 'started_at' => $submission->started_at, 'answers_snapshot' => $this->answerSnapshot($assignment, $submission)]
            );
        });

        return redirect()->route('assignments.exam.take', $assignment)->with('status', 'Draf jawaban ujian berhasil disimpan.');
    }

    /**
     * Kirim Jawaban Ujian Stimulus / Hybrid Mahasiswa
     */
    public function submitExam(Request $request, WritingAssignment $assignment): RedirectResponse
    {
        $user = Auth::user();
        $isEnrolled = $assignment->course?->students()->where('student_id', $user->id)->exists();
        abort_unless($isEnrolled, 403, 'Hanya mahasiswa terdaftar yang dapat mengumpulkan jawaban.');
        abort_unless($assignment->status === 'published', 404);
        abort_unless($assignment->due_at === null || $assignment->due_at->isFuture(), 409, 'Batas waktu pengumpulan tugas ini telah berakhir.');

        $submission = WritingSubmission::where([
            'assignment_id' => $assignment->id,
            'student_id' => $user->id,
        ])->first();
        abort_unless($submission, 409, 'Mulai ujian sebelum mengirim jawaban.');

        abort_if(
            $submission->submitted_at !== null && ! $assignment->allow_revisions,
            409,
            'Dosen tidak mengizinkan revisi jawaban untuk tugas ini.'
        );

        abort_unless($submission->started_at, 409, 'Mulai pengerjaan sebelum mengirim jawaban.');

        if ($assignment->time_limit_minutes && $submission->started_at) {
            $maxAllowedSeconds = ($assignment->time_limit_minutes * 60) + 300;
            $elapsedSeconds = (int) abs(now()->diffInSeconds($submission->started_at));
            abort_if(
                $elapsedSeconds > $maxAllowedSeconds,
                409,
                'Batas waktu pengerjaan ujian telah habis melebihi batas toleransi sistem.'
            );
        }

        $validated = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'string', 'max:10000'],
            'essay_answer' => ['nullable', 'string', 'max:50000'],
        ]);
        $answersInput = $validated['answers'] ?? [];
        $essayAnswer = trim((string) ($validated['essay_answer'] ?? $submission->draft_body_text ?? ''));

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
                        'attempt_number' => $submission->current_attempt ?: 1,
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
                    'draft_body_text' => null,
                    'draft_saved_at' => null,
                ]);

                // Sinkronkan rata-rata nilai mahasiswa di kelas
                $this->syncCourseStudentGrade($assignment, $user->id);
            } else {
                // Ujian hybrid: menunggu penilaian esai oleh dosen
                $submission->update([
                    'mcq_score' => $mcqScore,
                    'score' => null,
                    'graded_at' => null,
                    'graded_by' => null,
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'draft_body_text' => null,
                    'draft_saved_at' => null,
                ]);
            }

            WritingSubmissionAttempt::updateOrCreate(
                ['submission_id' => $submission->id, 'attempt_number' => $submission->current_attempt ?: 1],
                [
                    'status' => $submission->fresh()->status,
                    'started_at' => $submission->started_at,
                    'submitted_at' => now(),
                    'mcq_score' => $mcqScore,
                    'essay_score' => $submission->essay_score,
                    'score' => $submission->score,
                    'graded_by' => $submission->graded_by,
                    'graded_at' => $submission->graded_at,
                    'answers_snapshot' => $this->answerSnapshot($assignment, $submission),
                ]
            );

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

    private function snapshotAttempt(WritingSubmission $submission, int $attemptNumber): void
    {
        $answers = $submission->answers()->where('attempt_number', $attemptNumber)->with('question')->get();
        WritingSubmissionAttempt::updateOrCreate(
            ['submission_id' => $submission->id, 'attempt_number' => $attemptNumber],
            [
                'status' => $submission->status,
                'started_at' => $submission->started_at,
                'submitted_at' => $submission->submitted_at,
                'mcq_score' => $submission->mcq_score,
                'essay_score' => $submission->essay_score,
                'score' => $submission->score,
                'graded_by' => $submission->graded_by,
                'graded_at' => $submission->graded_at,
                'answers_snapshot' => $answers->map(fn ($answer) => [
                    'question_id' => $answer->question_id,
                    'question' => $answer->question?->prompt,
                    'answer' => $answer->student_answer,
                    'is_correct' => $answer->is_correct,
                    'score' => $answer->score_awarded,
                ])->values()->all(),
            ]
        );
    }

    private function answerSnapshot(WritingAssignment $assignment, WritingSubmission $submission): array
    {
        return $assignment->questions->map(function ($question) use ($submission) {
            $answer = $submission->answers()
                ->where('question_id', $question->id)
                ->where('attempt_number', $submission->current_attempt ?: 1)
                ->first();

            return [
                'question_id' => $question->id,
                'question' => $question->prompt,
                'answer' => $answer?->student_answer,
                'is_correct' => $answer?->is_correct,
                'score' => $answer?->score_awarded,
            ];
        })->values()->all();
    }
}
