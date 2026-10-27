<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\BkdAssessment;
use App\Models\Course;
use App\Models\Project;
use App\Models\User;
use App\Services\Bkd\BkdCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BkdAssessmentController extends Controller
{
    public function submit(Request $request, AcademicPeriod $period, BkdCalculator $calculator): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isStaff(), 403);

        $items = $this->collectEvidenceItems($user, $period, $calculator);
        $summary = $calculator->calculateSummary($items, $user->lecturerProfile);

        DB::transaction(function () use ($period, $user, $summary) {
            $assessment = BkdAssessment::updateOrCreate(
                ['academic_period_id' => $period->id, 'lecturer_id' => $user->id],
                [
                    'teaching_sks' => $summary['teaching_sks'],
                    'research_sks' => $summary['research_sks'],
                    'community_sks' => $summary['community_sks'],
                    'total_sks' => $summary['total_sks'],
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]
            );

            DB::table('audit_logs')->insert([
                'actor_id' => $user->id,
                'action' => 'bkd.portfolio_submitted',
                'target_type' => 'bkd_assessment',
                'target_id' => $assessment->id,
                'metadata' => json_encode(['period_id' => $period->id, 'total_sks' => $summary['total_sks']]),
                'created_at' => now(),
            ]);
        });

        return back()->with('status', 'Portofolio kinerja BKD semester '.$period->name.' berhasil diajukan untuk penilaian asesor.');
    }

    public function evaluate(Request $request, BkdAssessment $assessment): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isStaff(), 403);
        abort_if($user->id === $assessment->lecturer_id, 403, 'Dosen tidak dapat menilai portofolio kinerjanya sendiri.');

        $data = $request->validate([
            'verdict' => ['required', 'in:M,TM'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($assessment, $user, $data) {
            $assessment->update([
                'assessor_id' => $user->id,
                'verdict' => $data['verdict'],
                'notes' => $data['notes'] ?? null,
                'status' => 'evaluated',
                'evaluated_at' => now(),
            ]);

            DB::table('audit_logs')->insert([
                'actor_id' => $user->id,
                'action' => 'bkd.assessment_evaluated',
                'target_type' => 'bkd_assessment',
                'target_id' => $assessment->id,
                'metadata' => json_encode(['verdict' => $data['verdict']]),
                'created_at' => now(),
            ]);
        });

        return back()->with('status', 'Penilaian asesor BKD berhasil disimpan dengan hasil: '.$assessment->verdictLabel().'.');
    }

    public function export(Request $request, AcademicPeriod $period, BkdCalculator $calculator): View
    {
        $user = $request->user();
        abort_unless($user->isStaff(), 403);

        $lecturerId = ($user->role === 'admin' && $request->filled('lecturer_id'))
            ? (int) $request->input('lecturer_id')
            : $user->id;

        $targetLecturer = ($lecturerId === $user->id)
            ? $user
            : User::query()->where('id', $lecturerId)->whereIn('role', ['lecturer', 'admin', 'superadmin'])->firstOrFail();

        $targetLecturer->loadMissing(['lecturerProfile.studyProgram.faculty']);

        $evidenceItems = $this->collectEvidenceItems($targetLecturer, $period, $calculator);
        $summary = $calculator->calculateSummary($evidenceItems, $targetLecturer->lecturerProfile);

        $assessment = BkdAssessment::query()
            ->where('academic_period_id', $period->id)
            ->where('lecturer_id', $targetLecturer->id)
            ->with('assessor')
            ->first();

        return view('bkd.export', [
            'period' => $period,
            'lecturer' => $targetLecturer,
            'profile' => $targetLecturer->lecturerProfile,
            'evidenceItems' => $evidenceItems,
            'summary' => $summary,
            'assessment' => $assessment,
        ]);
    }

    /**
     * @return Collection<int, array{type: string, type_label: string, title: string, project_name: string, pillar: string, status: string, sks: float, date: Carbon|null}>
     */
    private function collectEvidenceItems(User $lecturer, AcademicPeriod $period, BkdCalculator $calculator): Collection
    {
        $projectIds = Project::query()->where('academic_period_id', $period->id)
            ->where(fn ($q) => $q->where('owner_id', $lecturer->id)
                ->orWhereHas('members', fn ($m) => $m->where('users.id', $lecturer->id)->where('project_members.role', 'editor')))
            ->pluck('id');

        $items = collect();

        // Ambil mata kuliah yang diampu dosen pada periode semester ini
        $courses = Course::query()->where('academic_period_id', $period->id)
            ->where('lecturer_id', $lecturer->id)
            ->with(['studyProgram'])
            ->withCount(['sessions', 'materials', 'students'])
            ->get();

        foreach ($courses as $course) {
            $items->push([
                'type' => 'course_teaching',
                'type_label' => 'Perkuliahan: '.$course->code.' ('.$course->class_group.')',
                'title' => 'Mengampu Mata Kuliah '.$course->name.' ('.$course->credit_hours.' SKS, '.$course->sessions_count.' Sesi Jurnal Kuliah)',
                'project_name' => $course->studyProgram->name ?? 'Prodi',
                'pillar' => 'teaching',
                'status' => $course->sessions_count >= 1 ? 'verified' : 'draft',
                'sks' => (float) $course->credit_hours,
                'date' => $course->updated_at,
            ]);
        }

        $projects = Project::query()->whereIn('id', $projectIds)
            ->with(['literatureSources', 'instrumentBlueprints', 'guruBahasaMaterials', 'assignments'])
            ->get();

        foreach ($projects as $project) {
            foreach ($project->literatureSources as $source) {
                $items->push([
                    'type' => 'literature_source',
                    'type_label' => 'Sumber Literatur (LitSynth)',
                    'title' => $source->title ?: $source->original_name,
                    'project_name' => $project->name,
                    'pillar' => 'research',
                    'status' => $source->status,
                    'sks' => $calculator->calculateItemSks('literature_source', $source->status),
                    'date' => $source->updated_at,
                ]);
            }
            foreach ($project->instrumentBlueprints as $blueprint) {
                $items->push([
                    'type' => 'instrument_blueprint',
                    'type_label' => 'Blueprint Instrumen Penelitian',
                    'title' => $blueprint->title.' (v'.$blueprint->current_version.')',
                    'project_name' => $project->name,
                    'pillar' => 'research',
                    'status' => $blueprint->status,
                    'sks' => $calculator->calculateItemSks('instrument_blueprint', $blueprint->status),
                    'date' => $blueprint->updated_at,
                ]);
            }
            foreach ($project->guruBahasaMaterials as $material) {
                $items->push([
                    'type' => 'gurubahasa_material',
                    'type_label' => 'Materi Pengabdian GuruBahasa',
                    'title' => $material->title,
                    'project_name' => $project->name,
                    'pillar' => 'community',
                    'status' => $material->status,
                    'sks' => $calculator->calculateItemSks('gurubahasa_material', $material->status),
                    'date' => $material->updated_at,
                ]);
            }
            foreach ($project->assignments as $assignment) {
                if ($assignment->status === 'published') {
                    $items->push([
                        'type' => 'writewise_assignment',
                        'type_label' => 'Tugas Pembelajaran WriteWise',
                        'title' => $assignment->title,
                        'project_name' => $project->name,
                        'pillar' => 'teaching',
                        'status' => $assignment->status,
                        'sks' => $calculator->calculateItemSks('writewise_assignment', $assignment->status),
                        'date' => $assignment->updated_at,
                    ]);
                }
            }
        }

        return $items;
    }
}
