<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_guest_navigation_for_unauthenticated_users(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('guest-shell');
        $response->assertSee('Masuk');
        $response->assertSee('Daftar');
        $response->assertSee(route('login'));
        $response->assertSee(route('register'));
        $response->assertDontSee('id="workspace-sidebar"', false);
        $response->assertDontSee('id="sidebar-toggle-mobile"', false);
        $response->assertDontSee('Buka Dashboard');
    }

    public function test_landing_page_hides_sidebar_and_shows_dashboard_action_for_authenticated_users(): void
    {
        $user = User::factory()->create([
            'role' => 'lecturer',
            'name' => 'Dr. H. Mulyadi',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSee('guest-shell');
        $response->assertDontSee('workspace-shell');
        $response->assertDontSee('id="workspace-sidebar"', false);
        $response->assertDontSee('id="sidebar-toggle-mobile"', false);
        $response->assertSee('Buka Dashboard');
        $response->assertSee(route('dashboard'));
        $response->assertSee('Dr. H. Mulyadi');
        $response->assertSee('Keluar');
        $response->assertDontSee(route('login'));
        $response->assertDontSee(route('register'));
    }

    public function test_dashboard_retains_workspace_sidebar_for_authenticated_users(): void
    {
        $user = User::factory()->create([
            'role' => 'lecturer',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('workspace-shell');
        $response->assertSee('id="workspace-sidebar"', false);
        $response->assertSee('id="sidebar-toggle-mobile"', false);
        $response->assertDontSee('guest-shell');
    }

    public function test_login_page_renders_professional_form_with_security_and_registration_links(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('Masuk ke Ruang Kerja');
        $response->assertSee('Sistem Informasi Kinerja Dosen');
        $response->assertSee('Alamat Email');
        $response->assertSee('Kata Sandi');
        $response->assertSee('Ingat saya di perangkat ini');
        $response->assertSee('Daftar Akun Baru');
        $response->assertSee(route('register'));
    }

    public function test_register_page_renders_role_options_and_lecturer_guidance(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSee('Pendaftaran Akun Baru');
        $response->assertSee('Informasi Akun Dosen / Tenaga Pendidik:');
        $response->assertSee('Mahasiswa (Akses Tugas, Materi & Presensi)', false);
        $response->assertSee('Pengguna Umum / Peneliti Tamu');
        $response->assertSee('Minimal 10 karakter');
        $response->assertSee('Sudah memiliki akun?');
        $response->assertSee(route('login'));
    }

    public function test_user_can_authenticate_via_login_form(): void
    {
        $user = User::factory()->create([
            'email' => 'dosen@universitas.ac.id',
            'password' => bcrypt('password12345'),
            'is_active' => true,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'dosen@universitas.ac.id',
            'password' => 'password12345',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_register_via_registration_form(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Fajar Pratama',
            'email' => 'fajar@mahasiswa.ac.id',
            'role' => 'student',
            'password' => 'KatasandiKuat123#',
            'password_confirmation' => 'KatasandiKuat123#',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', [
            'name' => 'Fajar Pratama',
            'email' => 'fajar@mahasiswa.ac.id',
            'role' => 'student',
        ]);
        $this->assertAuthenticated();
    }
}
