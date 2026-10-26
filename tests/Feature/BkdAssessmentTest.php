<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\BkdAssessment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BkdAssessmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_can_submit_bkd_portfolio_for_academic_period(): void
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $period = AcademicPeriod::create([
            'name' => '2026/2027 Ganjil',
            'academic_year' => '2026/2027',
            'semester' => 'odd',
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-31',
            'is_active' => true,
        ]);

        $project = Project::create([
            'owner_id' => $lecturer->id,
            'academic_period_id' => $period->id,
            'name' => 'Riset Pembelajaran Bahasa',
            'pillars' => ['teaching', 'research'],
            'status' => 'active',
        ]);

        // Tambah sumber literatur terverifikasi (+1.0 SKS)
        $project->literatureSources()->create([
            'uploaded_by' => $lecturer->id,
            'original_name' => 'paper.pdf',
            'storage_path' => 'litsynth/paper.pdf',
            'sha256' => hash('sha256', 'paper-dummy'),
            'file_size' => 4096,
            'title' => 'Paper Terverifikasi',
            'status' => 'verified',
        ]);

        $response = $this->actingAs($lecturer)->post(route('bkd.submit', $period));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('bkd_assessments', [
            'academic_period_id' => $period->id,
            'lecturer_id' => $lecturer->id,
            'status' => 'submitted',
            'research_sks' => 1.00,
            'total_sks' => 1.00,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $lecturer->id,
            'action' => 'bkd.portfolio_submitted',
            'target_type' => 'bkd_assessment',
        ]);
    }

    public function test_assessor_can_evaluate_lecturer_portfolio(): void
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $assessor = User::factory()->create(['role' => 'admin']);
        $period = AcademicPeriod::create([
            'name' => '2026/2027 Ganjil',
            'academic_year' => '2026/2027',
            'semester' => 'odd',
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-31',
            'is_active' => true,
        ]);

        $assessment = BkdAssessment::create([
            'academic_period_id' => $period->id,
            'lecturer_id' => $lecturer->id,
            'status' => 'submitted',
            'teaching_sks' => 4.0,
            'research_sks' => 6.0,
            'community_sks' => 2.5,
            'total_sks' => 12.5,
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($assessor)->post(route('bkd.evaluate', $assessment), [
            'verdict' => 'M',
            'notes' => 'Portofolio lengkap dan memenuhi standar rubrik BKD Dikti.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $assessment->refresh();
        $this->assertEquals('evaluated', $assessment->status);
        $this->assertEquals('M', $assessment->verdict);
        $this->assertEquals($assessor->id, $assessment->assessor_id);
        $this->assertEquals('Portofolio lengkap dan memenuhi standar rubrik BKD Dikti.', $assessment->notes);
        $this->assertNotNull($assessment->evaluated_at);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $assessor->id,
            'action' => 'bkd.assessment_evaluated',
            'target_type' => 'bkd_assessment',
            'target_id' => $assessment->id,
        ]);
    }

    public function test_lecturer_cannot_evaluate_own_portfolio(): void
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $period = AcademicPeriod::create([
            'name' => '2026/2027 Ganjil',
            'academic_year' => '2026/2027',
            'semester' => 'odd',
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-31',
            'is_active' => true,
        ]);

        $assessment = BkdAssessment::create([
            'academic_period_id' => $period->id,
            'lecturer_id' => $lecturer->id,
            'status' => 'submitted',
            'total_sks' => 12.0,
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($lecturer)->post(route('bkd.evaluate', $assessment), [
            'verdict' => 'M',
            'notes' => 'Mencoba menilai diri sendiri',
        ]);

        $response->assertStatus(403);
    }

    public function test_student_cannot_submit_or_evaluate_bkd_portfolio(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $period = AcademicPeriod::create([
            'name' => '2026/2027 Ganjil',
            'academic_year' => '2026/2027',
            'semester' => 'odd',
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-31',
            'is_active' => true,
        ]);

        $assessment = BkdAssessment::create([
            'academic_period_id' => $period->id,
            'lecturer_id' => $lecturer->id,
            'status' => 'submitted',
            'total_sks' => 12.0,
            'submitted_at' => now(),
        ]);

        // Student submits
        $this->actingAs($student)->post(route('bkd.submit', $period))->assertStatus(403);

        // Student evaluates
        $this->actingAs($student)->post(route('bkd.evaluate', $assessment), [
            'verdict' => 'M',
        ])->assertStatus(403);
    }
}
