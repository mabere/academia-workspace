<?php

namespace Tests\Feature;

use App\Models\Faculty;
use App\Models\LecturerProfile;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    private User $lecturer;

    private User $student;

    private StudyProgram $studyProgram;

    protected function setUp(): void
    {
        parent::setUp();

        $faculty = Faculty::create(['name' => 'Fakultas Keguruan dan Ilmu Pendidikan', 'code' => 'FKIP']);
        $this->studyProgram = StudyProgram::create([
            'faculty_id' => $faculty->id,
            'name' => 'Pendidikan Bahasa Inggris',
            'code' => 'PBI-S1',
            'level' => 'S1',
        ]);

        $this->superadmin = User::factory()->create([
            'name' => 'Root Superadmin',
            'email' => 'superadmin@example.test',
            'role' => 'superadmin',
        ]);

        $this->lecturer = User::factory()->create([
            'name' => 'Dr. Budi Santoso',
            'email' => 'budi@example.test',
            'role' => 'lecturer',
        ]);

        LecturerProfile::create([
            'user_id' => $this->lecturer->id,
            'study_program_id' => $this->studyProgram->id,
            'nidn' => '0012058001',
            'functional_rank' => 'lektor',
            'highest_education' => 'S3',
            'is_certified' => true,
            'target_sks' => 12,
        ]);

        $this->student = User::factory()->create([
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad@example.test',
            'role' => 'student',
        ]);
    }

    public function test_superadmin_can_view_user_management_index_with_statistics_and_filters(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('users.index'));

        $response->assertOk();
        $response->assertSee('Manajemen Pengguna');
        $response->assertSee('Root Superadmin');
        $response->assertSee('Dr. Budi Santoso');
        $response->assertSee('Ahmad Fauzi');
        $response->assertSee('0012058001');

        // Uji filter peran (role=lecturer)
        $filterResponse = $this->actingAs($this->superadmin)->get(route('users.index', ['role' => 'lecturer']));
        $filterResponse->assertOk();
        $filterResponse->assertSee('Dr. Budi Santoso');
        $filterResponse->assertDontSee('Ahmad Fauzi');

        // Uji pencarian (q=budi)
        $searchResponse = $this->actingAs($this->superadmin)->get(route('users.index', ['q' => 'budi']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('Dr. Budi Santoso');
        $searchResponse->assertDontSee('Ahmad Fauzi');
    }

    public function test_superadmin_can_create_new_user_with_lecturer_profile(): void
    {
        $response = $this->actingAs($this->superadmin)->post(route('users.store'), [
            'name' => 'Prof. Dewi Sartika, M.Hum.',
            'email' => 'dewi@example.test',
            'role' => 'lecturer',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'study_program_id' => $this->studyProgram->id,
            'nidn' => '0020088501',
            'nip' => '198508202010122001',
            'functional_rank' => 'guru_besar',
            'highest_education' => 'S3',
            'is_certified' => 1,
            'target_sks' => 14,
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'name' => 'Prof. Dewi Sartika, M.Hum.',
            'email' => 'dewi@example.test',
            'role' => 'lecturer',
        ]);

        $createdUser = User::where('email', 'dewi@example.test')->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue(Hash::check('Password123!', $createdUser->password));

        $this->assertDatabaseHas('lecturer_profiles', [
            'user_id' => $createdUser->id,
            'study_program_id' => $this->studyProgram->id,
            'nidn' => '0020088501',
            'functional_rank' => 'guru_besar',
        ]);
    }

    public function test_superadmin_can_edit_user_and_change_role(): void
    {
        $response = $this->actingAs($this->superadmin)->put(route('users.update', $this->student), [
            'name' => 'Ahmad Fauzi, S.Pd.',
            'email' => 'ahmad@example.test',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('status');

        $this->student->refresh();
        $this->assertEquals('Ahmad Fauzi, S.Pd.', $this->student->name);
        $this->assertEquals('admin', $this->student->role);
    }

    public function test_superadmin_cannot_demote_their_own_account_role(): void
    {
        $response = $this->actingAs($this->superadmin)->put(route('users.update', $this->superadmin), [
            'name' => 'Root Superadmin',
            'email' => 'superadmin@example.test',
            'role' => 'student', // Percobaan demote diri sendiri
        ]);

        $response->assertSessionHasErrors('role');

        $this->superadmin->refresh();
        $this->assertEquals('superadmin', $this->superadmin->role);
    }

    public function test_superadmin_can_reset_user_password(): void
    {
        $response = $this->actingAs($this->superadmin)->post(route('users.reset-password', $this->student), [
            'password' => 'NewSecretPassword2026!',
            'password_confirmation' => 'NewSecretPassword2026!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->student->refresh();
        $this->assertTrue(Hash::check('NewSecretPassword2026!', $this->student->password));
    }

    public function test_superadmin_can_delete_another_user_but_cannot_delete_self(): void
    {
        // 1. Superadmin hapus mahasiswa
        $deleteResponse = $this->actingAs($this->superadmin)->delete(route('users.destroy', $this->student));
        $deleteResponse->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $this->student->id]);

        // 2. Superadmin mencoba hapus akun sendiri (dilarang)
        $selfDeleteResponse = $this->actingAs($this->superadmin)->delete(route('users.destroy', $this->superadmin));
        $selfDeleteResponse->assertSessionHasErrors('error');
        $this->assertDatabaseHas('users', ['id' => $this->superadmin->id]);
    }

    public function test_non_superadmin_cannot_access_user_management(): void
    {
        // Dosen dilarang
        $this->actingAs($this->lecturer)->get(route('users.index'))->assertForbidden();
        $this->actingAs($this->lecturer)->get(route('users.create'))->assertForbidden();
        $this->actingAs($this->lecturer)->post(route('users.store'), [])->assertForbidden();

        // Mahasiswa dilarang
        $this->actingAs($this->student)->get(route('users.index'))->assertForbidden();
    }

    public function test_superadmin_can_toggle_user_status_and_deactivated_user_cannot_login(): void
    {
        $testUser = User::factory()->create([
            'email' => 'aktif@example.test',
            'password' => Hash::make('Secret12345!'),
            'role' => 'student',
            'is_active' => true,
        ]);

        // 1. Superadmin menonaktifkan akun
        $response = $this->actingAs($this->superadmin)->patch(route('users.toggle-status', $testUser));
        $response->assertRedirect();
        $response->assertSessionHas('status');

        $testUser->refresh();
        $this->assertFalse($testUser->isActive());

        // 2. Pengguna nonaktif mencoba login (harus ditolak dengan pesan jelas)
        Auth::logout();
        $this->flushSession();

        $loginResponse = $this->post(route('login'), [
            'email' => 'aktif@example.test',
            'password' => 'Secret12345!',
        ]);
        $loginResponse->assertSessionHasErrors('email');
        $this->assertGuest();

        // 3. Superadmin mengaktifkan kembali akun
        $reactivateResponse = $this->actingAs($this->superadmin)->patch(route('users.toggle-status', $testUser));
        $reactivateResponse->assertRedirect();

        $testUser->refresh();
        $this->assertTrue($testUser->isActive());

        // 4. Pengguna aktif sekarang dapat login
        Auth::logout();
        $this->flushSession();

        $successLoginResponse = $this->post(route('login'), [
            'email' => 'aktif@example.test',
            'password' => 'Secret12345!',
        ]);
        $successLoginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($testUser);
    }

    public function test_superadmin_cannot_deactivate_their_own_account(): void
    {
        $response = $this->actingAs($this->superadmin)->patch(route('users.toggle-status', $this->superadmin));

        $response->assertSessionHasErrors('error');
        $this->superadmin->refresh();
        $this->assertTrue($this->superadmin->isActive());
    }

    public function test_users_index_can_filter_by_active_and_inactive_status(): void
    {
        $inactiveStudent = User::factory()->create([
            'name' => 'Siswa Nonaktif',
            'email' => 'nonaktif@example.test',
            'role' => 'student',
            'is_active' => false,
        ]);

        // Filter aktif
        $activeResponse = $this->actingAs($this->superadmin)->get(route('users.index', ['status' => 'active']));
        $activeResponse->assertOk();
        $activeResponse->assertSee('Dr. Budi Santoso');
        $activeResponse->assertDontSee('Siswa Nonaktif');

        // Filter nonaktif
        $inactiveResponse = $this->actingAs($this->superadmin)->get(route('users.index', ['status' => 'inactive']));
        $inactiveResponse->assertOk();
        $inactiveResponse->assertSee('Siswa Nonaktif');
        $inactiveResponse->assertDontSee('Dr. Budi Santoso');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        // Tamu tanpa login dialihkan ke halaman login
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }
}
