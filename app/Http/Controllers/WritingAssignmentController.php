<?php

namespace App\Http\Controllers;

use App\Exceptions\AiUnavailableException;
use App\Http\Requests\StoreWritingAssignmentRequest;
use App\Http\Requests\UpdateWritingAssignmentRequest;
use App\Models\Project;
use App\Models\User;
use App\Models\WritingAssignment;
use App\Models\WritingFeedbackReport;
use App\Models\WritingRevision;
use App\Models\WritingSubmission;
use App\Notifications\AcademicNotification;
use App\Services\Ai\AiGateway;
use App\Services\DocumentTextExtractor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WritingAssignmentController extends Controller
{
    public function store(StoreWritingAssignmentRequest $request, Project $project): RedirectResponse
    {
        abort_if($project->archived_at !== null, 409, 'Tugas baru tidak dapat dibuat pada proyek yang diarsipkan.');

        $data = $request->validated();
        $studentIds = $this->resolveStudentIds($data['student_emails'] ?? '');
        if ($studentIds === null) {
            return back()->withErrors(['student_emails' => 'Pastikan semua email sudah terdaftar sebagai akun mahasiswa.'])->withInput();
        }

        $assignment = DB::transaction(function () use ($data, $studentIds, $project) {
            $assignmentData = $data;
            unset($assignmentData['student_emails'], $assignmentData['rubric_name'], $assignmentData['scale_min'], $assignmentData['scale_max'], $assignmentData['criteria']);
            $assignment = $project->assignments()->create($assignmentData + ['created_by' => Auth::id()]);
            $assignment->students()->sync($studentIds);
            $version = $assignment->versions()->create($this->assignmentSnapshot($assignment, 1));
            $criteria = $data['criteria'] ?? collect(['Content', 'Organization', 'Vocabulary', 'Language Use', 'Mechanics'])->map(fn ($name) => ['name' => $name, 'description' => '', 'weight' => 1])->all();
            $assignment->rubric()->create([
                'version' => 1,
                'name' => $data['rubric_name'] ?? 'Rubrik menulis',
                'scale_min' => $data['scale_min'] ?? 1,
                'scale_max' => $data['scale_max'] ?? 5,
                'criteria' => $criteria,
            ]);
            DB::table('audit_logs')->insert(['actor_id' => Auth::id(), 'project_id' => $project->id, 'action' => 'assignment.created', 'target_type' => 'writing_assignment', 'target_id' => $assignment->id, 'metadata' => json_encode(['status' => $assignment->status, 'version' => 1, 'assignment_version_id' => $version->id]), 'created_at' => now()]);

            return $assignment;
        });

        return redirect()->route('assignments.show', $assignment)->with('status', 'Tugas berhasil dibuat.');
    }

    public function update(UpdateWritingAssignmentRequest $request, WritingAssignment $assignment): RedirectResponse
    {
        abort_if($assignment->project->archived_at !== null, 409, 'Tugas dalam proyek arsip tidak dapat diubah.');

        $data = $request->validated();
        $studentIds = $this->resolveStudentIds($data['student_emails'] ?? '');
        if ($studentIds === null) {
            return back()->withErrors(['student_emails' => 'Pastikan semua email sudah terdaftar sebagai akun mahasiswa.'])->withInput();
        }
        if ($data['status'] === 'draft' && $assignment->submissions()->exists()) {
            abort(409, 'Tugas yang sudah menerima submission tidak dapat dikembalikan menjadi draf.');
        }

        DB::transaction(function () use ($assignment, $data, $studentIds): void {
            $assignmentData = collect($data)->only(['title', 'instructions', 'feedback_language', 'feedback_level', 'status', 'due_at'])->all();
            $assignment->update($assignmentData);
            $assignment->students()->sync($studentIds);
            $versionNumber = (int) $assignment->versions()->max('version') + 1;
            $version = $assignment->versions()->create($this->assignmentSnapshot($assignment, $versionNumber));

            $latestRubric = $assignment->rubric()->orderByDesc('version')->first();
            $criteria = collect($data['criteria'])->map(fn (array $criterion) => [
                'name' => trim($criterion['name']),
                'description' => trim($criterion['description'] ?? ''),
                'weight' => (float) $criterion['weight'],
            ])->values()->all();
            $currentCriteria = collect($latestRubric?->criteria ?? [])->map(fn (array $criterion) => [
                'name' => trim($criterion['name']),
                'description' => trim($criterion['description'] ?? ''),
                'weight' => (float) $criterion['weight'],
            ])->values()->all();
            if (! $latestRubric || $latestRubric->name !== $data['rubric_name'] || $latestRubric->scale_min !== (int) $data['scale_min'] || $latestRubric->scale_max !== (int) $data['scale_max'] || $currentCriteria !== $criteria) {
                $assignment->rubric()->create([
                    'version' => (int) $assignment->rubric()->max('version') + 1,
                    'name' => $data['rubric_name'],
                    'scale_min' => $data['scale_min'],
                    'scale_max' => $data['scale_max'],
                    'criteria' => $criteria,
                ]);
            }

            DB::table('audit_logs')->insert(['actor_id' => Auth::id(), 'project_id' => $assignment->project_id, 'action' => 'assignment.updated', 'target_type' => 'writing_assignment', 'target_id' => $assignment->id, 'metadata' => json_encode(['version' => $versionNumber, 'assignment_version_id' => $version->id]), 'created_at' => now()]);
        });

        return back()->with('status', 'Tugas diperbarui dan versi baru tersimpan.');
    }

    public function destroy(Request $request, WritingAssignment $assignment): RedirectResponse
    {
        Gate::authorize('delete', $assignment);

        $course = $assignment->course;
        $project = $assignment->project;
        $title = $assignment->title;

        DB::transaction(function () use ($assignment) {
            if (! empty($assignment->config_json['audio_file_path'])) {
                foreach (['local', 'public'] as $disk) {
                    if (Storage::disk($disk)->exists($assignment->config_json['audio_file_path'])) {
                        Storage::disk($disk)->delete($assignment->config_json['audio_file_path']);
                    }
                }
            }

            $assignment->delete();
        });

        if ($course) {
            return redirect()
                ->route('courses.show', ['course' => $course, 'tab' => 'assignments'])
                ->with('status', 'Tugas perkuliahan "'.$title.'" berhasil dihapus.');
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Tugas "'.$title.'" berhasil dihapus.');
    }

    public function show(WritingAssignment $assignment, AiGateway $gateway): View
    {
        Gate::authorize('view', $assignment);

        $assignment->load([
            'course.lecturer',
            'course.students.student.studyProgram',
            'project',
            'students',
            'questions',
            'versions' => fn ($query) => $query->orderByDesc('version'),
            'rubric' => fn ($query) => $query->orderByDesc('version'),
        ]);
        $submissions = $assignment->submissions()->with([
            'student.studyProgram',
            'grader',
            'revisions' => fn ($rq) => $rq->orderBy('revision_number')->with(['feedbackReports', 'assignmentVersion', 'rubricVersion']),
            'answers.question',
            'attempts',
        ]);
        if (Auth::user()->role === 'student') {
            $submissions->where('student_id', Auth::id());
        }
        $assignment->setRelation('submissions', $submissions->get());
        $assignment->setRelation('currentRubric', $assignment->rubric->first());

        $mySubmission = Auth::user()->role === 'student' ? $assignment->submissions->first() : null;
        $aiFeedbackAvailable = Auth::user()->isStaff() && $gateway->isAvailable();

        return view('assignments.show', compact('assignment', 'aiFeedbackAvailable', 'mySubmission'));
    }

    public function submit(Request $request, WritingAssignment $assignment, DocumentTextExtractor $extractor): RedirectResponse
    {
        Gate::authorize('submitRevision', $assignment);
        abort_unless($assignment->due_at === null || $assignment->due_at->isFuture(), 409, 'Tenggat tugas telah lewat.');

        $submission = WritingSubmission::where('assignment_id', $assignment->id)->where('student_id', Auth::id())->first();
        abort_if($submission?->status === 'graded' && $submission?->hasMaxGrade(), 409, 'Anda telah meraih nilai maksimal (Grade A). Tugas ini sudah tuntas dan tidak memerlukan revisi.');
        abort_if($submission?->submitted_at && ! $assignment->allow_revisions, 409, 'Dosen tidak mengizinkan revisi tambahan untuk tugas ini.');

        $data = $request->validate([
            'body_text' => ['nullable', 'string', 'max:50000'],
            'document_file' => ['nullable', 'file', 'mimes:docx,pdf,txt', 'max:10240'],
            'artifact_url' => ['nullable', 'url', 'max:500'],
            'milestone_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'section_responses' => ['nullable', 'array', 'max:12'],
            'section_responses.*' => ['nullable', 'string', 'max:50000'],
        ]);

        $rawSectionResponses = $data['section_responses'] ?? [];
        $sectionsConfig = $assignment->config_json['sections'] ?? [];

        if ($request->hasFile('document_file') && ! empty($sectionsConfig) && empty(array_filter($rawSectionResponses, fn ($v) => trim((string) $v) !== ''))) {
            try {
                $split = $extractor->extractSections($request->file('document_file'), $sectionsConfig);
                $rawSectionResponses = $split['section_texts'];
            } catch (\Throwable $e) {
                return back()->withErrors(['document_file' => 'Gagal mengekstrak teks berkas: '.$e->getMessage()])->withInput();
            }
        }

        $sectionAnswers = $this->validatedSectionAnswers($assignment, $rawSectionResponses);

        $bodyText = ! empty($data['body_text']) ? trim($data['body_text']) : '';

        if ($request->hasFile('document_file') && empty($sectionsConfig)) {
            try {
                $extracted = $extractor->extract($request->file('document_file'));
                if ($extracted !== '') {
                    $bodyText = $extracted;
                }
            } catch (\Throwable $e) {
                return back()->withErrors(['document_file' => 'Gagal mengekstrak teks berkas: '.$e->getMessage()]);
            }
        }

        if (! empty($sectionAnswers)) {
            $sectionTitles = collect($sectionsConfig)->keyBy('key');
            $bodyText = collect($sectionAnswers)->map(function ($answer, $key) use ($sectionTitles) {
                return ($sectionTitles[$key]['title'] ?? 'Bagian tugas')."\n".$answer;
            })->implode("\n\n");
        }

        if (mb_strlen($bodyText) < 20 && empty($data['artifact_url']) && empty(array_filter($sectionAnswers))) {
            return back()->withErrors(['body_text' => 'Teks naskah tugas atau hasil ekstraksi berkas minimal 20 karakter (atau sertakan tautan artefak digital).']);
        }

        DB::transaction(function () use ($assignment, $bodyText, $data, $sectionAnswers) {
            $assignmentVersionId = $assignment->versions()->orderByDesc('version')->value('id');
            $rubricVersionId = $assignment->rubric()->orderByDesc('version')->value('id');
            $submission = WritingSubmission::firstOrCreate(['assignment_id' => $assignment->id, 'student_id' => Auth::id()]);
            $submission->update(['assignment_version_id' => $assignmentVersionId, 'rubric_version_id' => $rubricVersionId]);
            $number = (int) $submission->revisions()->max('revision_number') + 1;
            $submission->revisions()->create([
                'created_by' => Auth::id(),
                'assignment_version_id' => $assignmentVersionId,
                'rubric_version_id' => $rubricVersionId,
                'revision_number' => $number,
                'body_text' => $bodyText,
                'section_answers' => $sectionAnswers,
                'artifact_url' => ! empty($data['artifact_url']) ? trim($data['artifact_url']) : null,
                'milestone_id' => ! empty($data['milestone_id']) ? (int) $data['milestone_id'] : null,
                'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
                'submitted_at' => now(),
            ]);
            $submission->update([
                'status' => 'submitted',
                'score' => null,
                'graded_by' => null,
                'graded_at' => null,
                'artifact_url' => ! empty($data['artifact_url']) ? trim($data['artifact_url']) : $submission->artifact_url,
                'current_milestone_id' => ! empty($data['milestone_id']) ? (int) $data['milestone_id'] : $submission->current_milestone_id,
                'notes' => ! empty($data['notes']) ? trim($data['notes']) : $submission->notes,
                'submitted_at' => now(),
                'draft_body_text' => null,
                'draft_artifact_url' => null,
                'draft_milestone_id' => null,
                'draft_notes' => null,
                'draft_section_answers' => null,
                'draft_saved_at' => null,
            ]);

            // Beri notifikasi ke dosen pengampu jika tugas terhubung dengan mata kuliah
            if ($assignment->course && $assignment->course->lecturer) {
                $studentUser = Auth::user();
                $assignment->course->lecturer->notify(new AcademicNotification(
                    title: 'Submisi Tugas Baru',
                    message: "Mahasiswa {$studentUser->name} telah mengumpulkan jawaban untuk tugas \"{$assignment->title}\" ({$assignment->course->name}).",
                    actionUrl: route('assignments.show', $assignment),
                    icon: 'assignment',
                    category: 'assignment',
                    meta: ['course_id' => $assignment->course_id, 'assignment_id' => $assignment->id, 'student_id' => $studentUser->id]
                ));
            }
        });

        return back()->with('status', 'Jawaban berhasil dikumpulkan.');
    }

    public function saveDraft(Request $request, WritingAssignment $assignment, DocumentTextExtractor $extractor): RedirectResponse
    {
        Gate::authorize('submitRevision', $assignment);
        abort_unless($assignment->due_at === null || $assignment->due_at->isFuture(), 409, 'Tenggat tugas telah lewat.');

        $submission = WritingSubmission::where('assignment_id', $assignment->id)->where('student_id', Auth::id())->first();
        abort_if($submission?->status === 'graded' && $submission?->hasMaxGrade(), 409, 'Anda telah meraih nilai maksimal (Grade A). Tugas ini sudah tuntas dan tidak memerlukan revisi.');
        abort_if($submission?->submitted_at && ! $assignment->allow_revisions, 409, 'Dosen tidak mengizinkan revisi tambahan untuk tugas ini.');

        $data = $request->validate([
            'body_text' => ['nullable', 'string', 'max:50000'],
            'document_file' => ['nullable', 'file', 'mimes:docx,pdf,txt', 'max:10240'],
            'artifact_url' => ['nullable', 'url', 'max:500'],
            'milestone_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'section_responses' => ['nullable', 'array', 'max:12'],
            'section_responses.*' => ['nullable', 'string', 'max:50000'],
        ]);

        $rawSectionResponses = $data['section_responses'] ?? [];
        $sectionsConfig = $assignment->config_json['sections'] ?? [];

        if ($request->hasFile('document_file') && ! empty($sectionsConfig) && empty(array_filter($rawSectionResponses, fn ($v) => trim((string) $v) !== ''))) {
            try {
                $split = $extractor->extractSections($request->file('document_file'), $sectionsConfig);
                $rawSectionResponses = $split['section_texts'];
            } catch (\Throwable $e) {
                return back()->withErrors(['document_file' => 'Gagal mengekstrak teks berkas: '.$e->getMessage()])->withInput();
            }
        }

        $sectionAnswers = $this->validatedSectionAnswers($assignment, $rawSectionResponses);

        $bodyText = trim((string) ($data['body_text'] ?? ''));
        if ($request->hasFile('document_file') && empty($sectionsConfig)) {
            try {
                $bodyText = $extractor->extract($request->file('document_file')) ?: $bodyText;
            } catch (\Throwable $e) {
                return back()->withErrors(['document_file' => 'Gagal mengekstrak teks berkas: '.$e->getMessage()])->withInput();
            }
        }

        if (! empty($sectionAnswers)) {
            $sectionTitles = collect($sectionsConfig)->keyBy('key');
            $bodyText = collect($sectionAnswers)->map(fn ($answer, $key) => ($sectionTitles[$key]['title'] ?? 'Bagian tugas')."\n".$answer)->implode("\n\n");
        }

        $submission = WritingSubmission::firstOrNew([
            'assignment_id' => $assignment->id,
            'student_id' => Auth::id(),
        ]);
        $submission->fill([
            'status' => 'draft',
            'draft_body_text' => $bodyText,
            'draft_artifact_url' => $data['artifact_url'] ?? null,
            'draft_milestone_id' => $data['milestone_id'] ?? null,
            'draft_notes' => $data['notes'] ?? null,
            'draft_section_answers' => $sectionAnswers,
            'draft_saved_at' => now(),
        ])->save();

        return back()->with('status', 'Draf tugas berhasil disimpan. Anda dapat melanjutkannya dan mengirim nanti.');
    }

    /** @return array<string, string> */
    private function validatedSectionAnswers(WritingAssignment $assignment, array $answers): array
    {
        $sections = $assignment->config_json['sections'] ?? [];
        $result = [];

        foreach ($sections as $section) {
            $key = $section['key'] ?? null;
            if (! $key) {
                continue;
            }

            $value = trim((string) ($answers[$key] ?? ''));
            if (($section['required'] ?? false) && $value === '') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'section_responses.'.$key => 'Bagian "'.($section['title'] ?? 'Tugas').'" wajib diisi.',
                ]);
            }
            if ($value !== '' && in_array($section['response_type'] ?? '', ['url', 'audio_video'], true)
                && ! filter_var($value, FILTER_VALIDATE_URL)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'section_responses.'.$key => 'Masukkan tautan yang valid untuk bagian ini.',
                ]);
            }
            if ($value !== '' && ($section['response_type'] ?? '') === 'numeric' && ! is_numeric($value)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'section_responses.'.$key => 'Masukkan nilai angka yang valid.',
                ]);
            }

            $result[$key] = $value;
        }

        return $result;
    }

    public function extractDocument(Request $request, DocumentTextExtractor $extractor): JsonResponse
    {
        $request->validate([
            'document_file' => ['required', 'file', 'mimes:docx,pdf,txt', 'max:10240'],
            'assignment_id' => ['nullable', 'integer', 'exists:writing_assignments,id'],
        ], [
            'document_file.required' => 'Pilih berkas Word (.docx), PDF (.pdf), atau Text (.txt) terlebih dahulu.',
            'document_file.mimes' => 'Format berkas harus berupa .docx, .pdf, atau .txt.',
            'document_file.max' => 'Ukuran berkas maksimal 10 MB.',
        ]);

        try {
            $file = $request->file('document_file');
            $assignment = $request->filled('assignment_id')
                ? WritingAssignment::find((int) $request->input('assignment_id'))
                : null;

            $sectionsConfig = $assignment?->config_json['sections'] ?? [];

            if (! empty($sectionsConfig)) {
                $split = $extractor->extractSections($file, $sectionsConfig);

                $summaryParts = [];
                foreach ($split['section_stats'] as $stat) {
                    if ($stat['word_count'] > 0) {
                        $summaryParts[] = "{$stat['title']} ({$stat['word_count']} kata)";
                    }
                }

                if ($split['unmatched']) {
                    $firstTitle = $sectionsConfig[0]['title'] ?? 'Bagian 1';
                    $message = "Teks berhasil diekstrak ke {$firstTitle} ({$split['total_words']} kata). Penanda bagian lain tidak terdeteksi pada dokumen, silakan sesuaikan secara manual bila perlu.";
                } else {
                    $allocatedText = ! empty($summaryParts) ? implode(', ', $summaryParts) : "{$split['total_words']} kata";
                    $preambleNote = $split['ignored_preamble'] ? ' Bagian cover/pengantar diabaikan.' : '';
                    $message = "Ekstraksi berhasil: Teks dialokasikan ke {$allocatedText}.{$preambleNote}";
                }

                return response()->json([
                    'success' => true,
                    'text' => $split['full_text'],
                    'section_texts' => $split['section_texts'],
                    'section_stats' => $split['section_stats'],
                    'ignored_preamble' => $split['ignored_preamble'],
                    'unmatched' => $split['unmatched'],
                    'message' => $message,
                    'char_count' => $split['total_chars'],
                    'word_count' => $split['total_words'],
                    'filename' => $file->getClientOriginalName(),
                ]);
            }

            $text = $extractor->extract($file);
            $wordCount = $extractor->countWords($text);

            return response()->json([
                'success' => true,
                'text' => $text,
                'message' => "Ekstraksi berhasil: {$wordCount} kata (" . mb_strlen($text) . " karakter) diekstrak dari {$file->getClientOriginalName()}.",
                'char_count' => mb_strlen($text),
                'word_count' => $wordCount,
                'filename' => $file->getClientOriginalName(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function editFeedback(Request $request, WritingFeedbackReport $report): RedirectResponse
    {
        Gate::authorize('manage', $report->revision->submission->assignment);
        abort_unless($report->status === 'draft', 409);

        $data = $request->validate(['feedback_json' => ['required', 'array'], 'feedback_json.overall_summary' => ['required', 'string', 'max:5000']]);
        $report->update(['feedback_json' => $data['feedback_json'], 'edited_by' => Auth::id()]);

        return back()->with('status', 'Draf umpan balik diperbarui.');
    }

    public function createFeedback(Request $request, WritingRevision $revision): RedirectResponse
    {
        Gate::authorize('manage', $revision->submission->assignment);
        abort_unless(! $revision->feedbackReports()->where('status', 'draft')->exists(), 409, 'Draf feedback sudah tersedia; edit draf yang ada.');

        $data = $request->validate(['overall_summary' => ['required', 'string', 'max:5000']]);
        $report = $revision->feedbackReports()->create([
            'edited_by' => Auth::id(),
            'feedback_json' => ['overall_summary' => $data['overall_summary'], 'criteria_feedback' => [], 'language_issues' => [], 'socratic_questions' => [], 'limitations' => ['Feedback disusun oleh dosen.']],
            'prompt_version' => 'manual-v1',
            'status' => 'draft',
        ]);
        DB::table('audit_logs')->insert(['actor_id' => Auth::id(), 'project_id' => $revision->submission->assignment->project_id, 'action' => 'feedback.manual_created', 'target_type' => 'writing_feedback_report', 'target_id' => $report->id, 'metadata' => null, 'created_at' => now()]);

        return back()->with('status', 'Draf umpan balik disimpan untuk ditinjau sebelum diterbitkan.');
    }

    public function publishFeedback(WritingFeedbackReport $report): RedirectResponse
    {
        Gate::authorize('manage', $report->revision->submission->assignment);
        abort_unless($report->status === 'draft', 409);

        $report->update(['status' => 'published', 'edited_by' => Auth::id(), 'published_at' => now()]);

        return back()->with('status', 'Umpan balik diterbitkan untuk mahasiswa.');
    }

    public function generateFeedback(WritingRevision $revision, AiGateway $gateway): RedirectResponse
    {
        Gate::authorize('manage', $revision->submission->assignment);

        if ($revision->feedbackReports()->where('status', 'draft')->exists()) {
            abort(409, 'Draf feedback sudah tersedia; edit draf yang ada.');
        }

        try {
            $feedback = $gateway->generateWritingFeedback($revision);
        } catch (AiUnavailableException $exception) {
            DB::table('audit_logs')->insert([
                'actor_id' => Auth::id(),
                'project_id' => $revision->submission->assignment->project_id,
                'action' => 'feedback.ai_blocked',
                'target_type' => 'writing_revision',
                'target_id' => $revision->id,
                'metadata' => json_encode(['reason' => $exception->reason]),
                'created_at' => now(),
            ]);

            return back()->withErrors(['ai' => 'Feedback AI belum tersedia. Tidak ada teks mahasiswa yang dikirim. Gunakan feedback manual atau tunggu persetujuan kebijakan/provider.']);
        }

        $revision->feedbackReports()->create([
            'edited_by' => Auth::id(),
            'feedback_json' => $feedback,
            'provider' => config('ai.provider'),
            'model' => config('ai.model'),
            'prompt_version' => config('ai.prompt_version'),
            'status' => 'draft',
        ]);

        return back()->with('status', 'Draf feedback AI dibuat untuk ditinjau dosen sebelum diterbitkan.');
    }

    /** @return array<string, mixed> */
    private function assignmentSnapshot(WritingAssignment $assignment, int $version): array
    {
        return [
            'version' => $version,
            'title' => $assignment->title,
            'instructions' => $assignment->instructions,
            'feedback_language' => $assignment->feedback_language,
            'feedback_level' => $assignment->feedback_level,
            'status' => $assignment->status,
            'due_at' => $assignment->due_at,
            'edited_by' => Auth::id(),
        ];
    }

    /** @return list<int>|null */
    private function resolveStudentIds(string $studentEmails): ?array
    {
        $parsedEmails = preg_split('/[\s,;]+/', strtolower($studentEmails), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $emails = collect($parsedEmails)->unique()->values();
        $studentIds = $emails->isEmpty() ? [] : User::query()->whereIn('email', $emails)->where('role', 'student')->pluck('id')->all();

        return count($studentIds) === $emails->count() ? $studentIds : null;
    }
}
