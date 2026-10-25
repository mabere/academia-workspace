<?php

namespace App\Http\Controllers;

use App\Exceptions\AiUnavailableException;
use App\Models\Project;
use App\Models\User;
use App\Models\WritingAssignment;
use App\Models\WritingFeedbackReport;
use App\Models\WritingRevision;
use App\Models\WritingSubmission;
use App\Services\Ai\AiGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WritingAssignmentController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        abort_unless(Auth::user()->isStaff() && ($project->isOwnedBy(Auth::user()) || $project->hasEditor(Auth::user())), 403);
        abort_if($project->archived_at !== null, 409, 'Tugas baru tidak dapat dibuat pada proyek yang diarsipkan.');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'instructions' => ['required', 'string', 'max:10000'],
            'feedback_language' => ['required', 'in:id,en'],
            'feedback_level' => ['required', 'in:indirect,direct,socratic'],
            'status' => ['required', 'in:draft,published'],
            'student_emails' => ['nullable', 'string', 'max:5000'],
            'due_at' => ['nullable', 'date'],
            'rubric_name' => ['sometimes', 'required', 'string', 'max:120'],
            'scale_min' => ['sometimes', 'required', 'integer', 'min:0', 'max:100'],
            'scale_max' => ['sometimes', 'required', 'integer', 'gt:scale_min', 'max:100'],
            'criteria' => ['sometimes', 'required', 'array', 'min:1', 'max:12'],
            'criteria.*.name' => ['required_with:criteria', 'string', 'max:120'],
            'criteria.*.description' => ['nullable', 'string', 'max:1000'],
            'criteria.*.weight' => ['required_with:criteria', 'numeric', 'gt:0', 'max:100'],
        ]);
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

    public function update(Request $request, WritingAssignment $assignment): RedirectResponse
    {
        abort_unless($this->canManage($assignment), 403);
        abort_if($assignment->project->archived_at !== null, 409, 'Tugas dalam proyek arsip tidak dapat diubah.');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'instructions' => ['required', 'string', 'max:10000'],
            'feedback_language' => ['required', 'in:id,en'],
            'feedback_level' => ['required', 'in:indirect,direct,socratic'],
            'status' => ['required', 'in:draft,published'],
            'student_emails' => ['nullable', 'string', 'max:5000'],
            'due_at' => ['nullable', 'date'],
            'rubric_name' => ['required', 'string', 'max:120'],
            'scale_min' => ['required', 'integer', 'min:0', 'max:100'],
            'scale_max' => ['required', 'integer', 'gt:scale_min', 'max:100'],
            'criteria' => ['required', 'array', 'min:1', 'max:12'],
            'criteria.*.name' => ['required', 'string', 'max:120'],
            'criteria.*.description' => ['nullable', 'string', 'max:1000'],
            'criteria.*.weight' => ['required', 'numeric', 'gt:0', 'max:100'],
        ]);
        $studentIds = $this->resolveStudentIds($data['student_emails']);
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

    public function show(WritingAssignment $assignment, AiGateway $gateway): View
    {
        $this->authorizeView($assignment);
        $assignment->load(['project', 'students', 'versions' => fn ($query) => $query->orderByDesc('version'), 'rubric' => fn ($query) => $query->orderByDesc('version')]);
        $submissions = $assignment->submissions()->with(['student', 'revisions.feedbackReports', 'revisions.assignmentVersion', 'revisions.rubricVersion']);
        if (Auth::user()->role === 'student') {
            $submissions->where('student_id', Auth::id());
        }
        $assignment->setRelation('submissions', $submissions->get());
        $assignment->setRelation('currentRubric', $assignment->rubric->first());

        $aiFeedbackAvailable = Auth::user()->isStaff() && $gateway->isAvailable();

        return view('assignments.show', compact('assignment', 'aiFeedbackAvailable'));
    }

    public function submit(Request $request, WritingAssignment $assignment): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'student' && $assignment->status === 'published' && $assignment->students()->whereKey(Auth::id())->exists(), 403);
        abort_unless($assignment->due_at === null || $assignment->due_at->isFuture(), 409, 'Tenggat tugas telah lewat.');
        $data = $request->validate(['body_text' => ['required', 'string', 'min:20', 'max:50000']]);
        DB::transaction(function () use ($assignment, $data) {
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
                'body_text' => $data['body_text'],
                'submitted_at' => now(),
            ]);
            $submission->update(['status' => 'submitted']);
        });

        return back()->with('status', 'Revisi tersimpan sebagai versi baru.');
    }

    public function editFeedback(Request $request, WritingFeedbackReport $report): RedirectResponse
    {
        abort_unless($this->canManage($report->revision->submission->assignment), 403);
        abort_unless($report->status === 'draft', 409);
        $data = $request->validate(['feedback_json' => ['required', 'array'], 'feedback_json.overall_summary' => ['required', 'string', 'max:5000']]);
        $report->update(['feedback_json' => $data['feedback_json'], 'edited_by' => Auth::id()]);

        return back()->with('status', 'Draf umpan balik diperbarui.');
    }

    public function createFeedback(Request $request, WritingRevision $revision): RedirectResponse
    {
        abort_unless($this->canManage($revision->submission->assignment), 403);
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
        abort_unless($this->canManage($report->revision->submission->assignment), 403);
        abort_unless($report->status === 'draft', 409);
        $report->update(['status' => 'published', 'edited_by' => Auth::id(), 'published_at' => now()]);

        return back()->with('status', 'Umpan balik diterbitkan untuk mahasiswa.');
    }

    public function generateFeedback(WritingRevision $revision, AiGateway $gateway): RedirectResponse
    {
        abort_unless($this->canManage($revision->submission->assignment), 403);

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

    private function authorizeView(WritingAssignment $assignment): void
    {
        $user = Auth::user();
        $allowed = $this->canManage($assignment) || ($user->role === 'student' && $assignment->status === 'published' && $assignment->students()->whereKey($user->id)->exists());
        abort_unless($allowed, 403);
    }

    private function canManage(WritingAssignment $assignment): bool
    {
        return Auth::user()->isStaff() && ($assignment->project->owner_id === Auth::id() || $assignment->project->members()->whereKey(Auth::id())->wherePivot('role', 'editor')->exists());
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
