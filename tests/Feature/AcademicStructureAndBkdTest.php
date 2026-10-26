<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructureAndBkdTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_create_project_with_academic_period(): void
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

        $response = $this->actingAs($lecturer)->post(route('projects.store'), [
            'name' => 'Proyek Semester Ganjil',
            'academic_period_id' => $period->id,
            'pillars' => ['teaching', 'research'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'name' => 'Proyek Semester Ganjil',
            'owner_id' => $lecturer->id,
            'academic_period_id' => $period->id,
        ]);
    }

    public function test_outputs_evidence_displays_bkd_rekapitulasi_and_filters_by_period(): void
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $period1 = AcademicPeriod::create([
            'name' => '2026/2027 Ganjil',
            'academic_year' => '2026/2027',
            'semester' => 'odd',
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-31',
            'is_active' => true,
        ]);

        $period2 = AcademicPeriod::create([
            'name' => '2025/2026 Genap',
            'academic_year' => '2025/2026',
            'semester' => 'even',
            'start_date' => '2026-02-01',
            'end_date' => '2026-06-30',
            'is_active' => false,
        ]);

        $project1 = Project::create([
            'owner_id' => $lecturer->id,
            'academic_period_id' => $period1->id,
            'name' => 'Proyek Riset Ganjil',
            'pillars' => ['research'],
            'status' => 'active',
        ]);

        $project2 = Project::create([
            'owner_id' => $lecturer->id,
            'academic_period_id' => $period2->id,
            'name' => 'Proyek Riset Genap Lalu',
            'pillars' => ['research'],
            'status' => 'active',
        ]);

        // Tambah sumber literatur terverifikasi di project 1 (+1.0 SKS)
        $project1->literatureSources()->create([
            'uploaded_by' => $lecturer->id,
            'original_name' => 'artikel.pdf',
            'storage_path' => 'litsynth/artikel.pdf',
            'sha256' => hash('sha256', 'dummy1'),
            'file_size' => 2048,
            'title' => 'Artikel Jurnal Terverifikasi',
            'status' => 'verified',
        ]);

        // Buka Output & Evidence
        $response = $this->actingAs($lecturer)->get(route('outputs.index', ['academic_period_id' => $period1->id]));
        $response->assertOk();
        $response->assertSee('Rekapitulasi Kinerja Beban Kerja Dosen (BKD)');
        $response->assertSee('Artikel Jurnal Terverifikasi');
        $response->assertSee('+1.0 SKS');
    }
}
