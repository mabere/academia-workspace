<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\BkdAssessment;
use App\Models\Faculty;
use App\Models\LecturerProfile;
use App\Models\Project;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BkdExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_can_view_official_bkd_export_for_academic_period(): void
    {
        $faculty = Faculty::create(['name' => 'Fakultas Keguruan dan Ilmu Pendidikan', 'code' => 'FKIP']);
        $studyProgram = StudyProgram::create([
            'faculty_id' => $faculty->id,
            'name' => 'Pendidikan Bahasa Inggris',
            'code' => 'PBI-S1',
            'level' => 'S1',
        ]);

        $lecturer = User::factory()->create(['name' => 'Dr. Budi Santoso, M.Pd.', 'role' => 'lecturer']);
        LecturerProfile::create([
            'user_id' => $lecturer->id,
            'study_program_id' => $studyProgram->id,
            'nidn' => '0012058001',
            'nip' => '198005122005011002',
            'functional_rank' => 'lektor_kepala',
            'highest_education' => 'S3',
            'is_certified' => true,
            'target_sks' => 12,
        ]);

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
            'name' => 'Kajian Linguistik Terapan',
            'pillars' => ['teaching', 'research', 'community'],
            'status' => 'active',
        ]);

        $project->literatureSources()->create([
            'uploaded_by' => $lecturer->id,
            'original_name' => 'jurnal.pdf',
            'storage_path' => 'litsynth/jurnal.pdf',
            'sha256' => hash('sha256', 'dummy-export'),
            'file_size' => 1024,
            'title' => 'Analisis Semantik Corpus Mahasiswa',
            'status' => 'verified',
        ]);

        BkdAssessment::create([
            'academic_period_id' => $period->id,
            'lecturer_id' => $lecturer->id,
            'status' => 'evaluated',
            'verdict' => 'M',
            'teaching_sks' => 4.0,
            'research_sks' => 7.0,
            'community_sks' => 2.0,
            'total_sks' => 13.0,
            'notes' => 'Portofolio lengkap dan sangat memuaskan.',
            'submitted_at' => now()->subDays(2),
            'evaluated_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($lecturer)->get(route('bkd.export', $period));

        $response->assertOk();
        $response->assertSee('LEMBAR HASIL EVALUASI BEBAN KERJA DOSEN (BKD)');
        $response->assertSee('Dr. Budi Santoso, M.Pd.');
        $response->assertSee('0012058001');
        $response->assertSee('Fakultas Keguruan dan Ilmu Pendidikan');
        $response->assertSee('Pendidikan Bahasa Inggris');
        $response->assertSee('Analisis Semantik Corpus Mahasiswa');
        $response->assertSee('Cetak / Simpan PDF');
    }

    public function test_admin_can_view_export_for_other_lecturer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lecturer = User::factory()->create(['name' => 'Prof. Siti Rahma', 'role' => 'lecturer']);
        $period = AcademicPeriod::create([
            'name' => '2026/2027 Ganjil',
            'academic_year' => '2026/2027',
            'semester' => 'odd',
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-31',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('bkd.export', [
            'period' => $period->id,
            'lecturer_id' => $lecturer->id,
        ]));

        $response->assertOk();
        $response->assertSee('Prof. Siti Rahma');
    }

    public function test_student_cannot_access_bkd_export(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $period = AcademicPeriod::create([
            'name' => '2026/2027 Ganjil',
            'academic_year' => '2026/2027',
            'semester' => 'odd',
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-31',
            'is_active' => true,
        ]);

        $response = $this->actingAs($student)->get(route('bkd.export', $period));
        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $period = AcademicPeriod::create([
            'name' => '2026/2027 Ganjil',
            'academic_year' => '2026/2027',
            'semester' => 'odd',
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-31',
            'is_active' => true,
        ]);

        $response = $this->get(route('bkd.export', $period));
        $response->assertRedirect(route('login'));
    }
}
