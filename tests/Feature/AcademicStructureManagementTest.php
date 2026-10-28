<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructureManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create([
            'role' => 'superadmin',
            'name' => 'Dr. Hendra Wijaya (Superadmin)',
        ]);
    }

    public function test_superadmin_can_manage_faculties_and_study_programs(): void
    {
        $this->actingAs($this->superadmin)
            ->get(route('academic-structure.index'))
            ->assertOk()
            ->assertSee('Master Unit Akademik Dosen');

        // 1. Tambah Fakultas
        $this->actingAs($this->superadmin)
            ->post(route('academic-structure.faculties.store'), [
                'code' => 'fkip',
                'name' => 'Fakultas Keguruan dan Ilmu Pendidikan',
                'description' => 'Fakultas kependidikan utama.',
            ])
            ->assertRedirect(route('academic-structure.index'));

        $faculty = Faculty::firstOrFail();
        $this->assertSame('FKIP', $faculty->code);

        // 2. Update Fakultas
        $this->actingAs($this->superadmin)
            ->put(route('academic-structure.faculties.update', $faculty), [
                'code' => 'FKIP',
                'name' => 'FKIP Universitas',
                'description' => 'Deskripsi diperbarui.',
            ])
            ->assertRedirect(route('academic-structure.index'));

        $this->assertSame('FKIP Universitas', $faculty->fresh()->name);

        // 3. Tambah Program Studi di bawah Fakultas
        $this->actingAs($this->superadmin)
            ->post(route('academic-structure.study-programs.store'), [
                'faculty_id' => $faculty->id,
                'code' => 'pbi-s1',
                'name' => 'Pendidikan Bahasa Inggris',
                'level' => 'S1',
            ])
            ->assertRedirect(route('academic-structure.index'));

        $prodi = StudyProgram::firstOrFail();
        $this->assertSame('PBI-S1', $prodi->code);
        $this->assertSame($faculty->id, $prodi->faculty_id);

        // 4. Proteksi hapus Fakultas jika masih menaungi Prodi
        $this->actingAs($this->superadmin)
            ->delete(route('academic-structure.faculties.destroy', $faculty))
            ->assertSessionHasErrors('faculty');

        $this->assertDatabaseHas('faculties', ['id' => $faculty->id]);

        // 5. Update Prodi
        $this->actingAs($this->superadmin)
            ->put(route('academic-structure.study-programs.update', $prodi), [
                'faculty_id' => $faculty->id,
                'code' => 'PBI',
                'name' => 'S1 Pendidikan Bahasa Inggris',
                'level' => 'S1',
            ])
            ->assertRedirect(route('academic-structure.index'));

        $this->assertSame('PBI', $prodi->fresh()->code);

        // 6. Hapus Prodi & kemudian Hapus Fakultas
        $this->actingAs($this->superadmin)
            ->delete(route('academic-structure.study-programs.destroy', $prodi))
            ->assertRedirect(route('academic-structure.index'));

        $this->assertDatabaseMissing('study_programs', ['id' => $prodi->id]);

        $this->actingAs($this->superadmin)
            ->delete(route('academic-structure.faculties.destroy', $faculty))
            ->assertRedirect(route('academic-structure.index'));

        $this->assertDatabaseMissing('faculties', ['id' => $faculty->id]);
    }

    public function test_superadmin_can_manage_and_activate_academic_periods(): void
    {
        $period1 = AcademicPeriod::create([
            'name' => '2025/2026 Genap',
            'academic_year' => '2025/2026',
            'semester' => 'even',
            'start_date' => '2026-02-01',
            'end_date' => '2026-07-31',
            'is_active' => true,
        ]);

        // Tambah periode baru dan jadikan aktif
        $this->actingAs($this->superadmin)
            ->post(route('academic-structure.periods.store'), [
                'name' => '2026/2027 Ganjil',
                'academic_year' => '2026/2027',
                'semester' => 'odd',
                'start_date' => '2026-09-01',
                'end_date' => '2027-01-31',
                'is_active' => '1',
            ])
            ->assertRedirect(route('academic-structure.index'));

        $period2 = AcademicPeriod::where('name', '2026/2027 Ganjil')->firstOrFail();
        $this->assertTrue($period2->is_active);
        $this->assertFalse($period1->fresh()->is_active);

        // Aktifkan kembali period1
        $this->actingAs($this->superadmin)
            ->patch(route('academic-structure.periods.activate', $period1))
            ->assertRedirect(route('academic-structure.index'));

        $this->assertTrue($period1->fresh()->is_active);
        $this->assertFalse($period2->fresh()->is_active);

        // Proteksi hapus periode jika sudah memiliki mata kuliah
        $faculty = Faculty::create(['name' => 'FKIP', 'code' => 'FKIP']);
        $prodi = StudyProgram::create(['faculty_id' => $faculty->id, 'name' => 'PBI', 'code' => 'PBI', 'level' => 'S1']);

        Course::create([
            'academic_period_id' => $period1->id,
            'study_program_id' => $prodi->id,
            'lecturer_id' => $this->superadmin->id,
            'code' => 'ENG101',
            'name' => 'General English',
            'credit_hours' => 2,
            'class_group' => 'Kelas A',
        ]);

        $this->actingAs($this->superadmin)
            ->delete(route('academic-structure.periods.destroy', $period1))
            ->assertSessionHasErrors('period');

        $this->assertDatabaseHas('academic_periods', ['id' => $period1->id]);
    }

    public function test_non_superadmin_roles_cannot_access_academic_structure_module(): void
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($lecturer)
            ->get(route('academic-structure.index'))
            ->assertForbidden();

        $this->actingAs($lecturer)
            ->post(route('academic-structure.faculties.store'), [
                'code' => 'FT',
                'name' => 'Fakultas Teknik',
            ])
            ->assertForbidden();

        $this->actingAs($student)
            ->get(route('academic-structure.index'))
            ->assertForbidden();
    }
}
