<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\BkdAssessment;
use App\Models\Project;
use App\Services\Bkd\BkdCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BkdAssessmentController extends Controller
{
    public function submit(Request $request, AcademicPeriod $period, BkdCalculator $calculator): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isStaff(), 403);

        // Ambil proyek pengguna pada periode ini
        $projectIds = Project::query()->where('academic_period_id', $period->id)
            ->where(fn ($q) => $q->where('owner_id', $user->id)
                ->orWhereHas('members', fn ($m) => $m->where('users.id', $user->id)->where('project_members.role', 'editor')))
            ->pluck('id');

        // Hitung total SKS terkini
        $items = collect();
        foreach (Project::query()->whereIn('id', $projectIds)->with(['literatureSources', 'instrumentBlueprints', 'guruBahasaMaterials', 'assignments.feedbackReports'])->get() as $project) {
            foreach ($project->literatureSources as $source) {
                $items->push(['type' => 'literature_source', 'pillar' => 'research', 'sks' => $calculator->calculateItemSks('literature_source', $source->status)]);
            }
            foreach ($project->instrumentBlueprints as $blueprint) {
                $items->push(['type' => 'instrument_blueprint', 'pillar' => 'research', 'sks' => $calculator->calculateItemSks('instrument_blueprint', $blueprint->status)]);
            }
            foreach ($project->guruBahasaMaterials as $material) {
                $items->push(['type' => 'gurubahasa_material', 'pillar' => 'community', 'sks' => $calculator->calculateItemSks('gurubahasa_material', $material->status)]);
            }
            foreach ($project->assignments as $assignment) {
                if ($assignment->status === 'published') {
                    $items->push(['type' => 'writewise_assignment', 'pillar' => 'teaching', 'sks' => $calculator->calculateItemSks('writewise_assignment', $assignment->status)]);
                }
            }
        }

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
}
