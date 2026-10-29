<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;

    private User $student;

    private string $backupDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lecturer = User::factory()->create([
            'name' => 'Dr. Budi Santoso',
            'role' => 'lecturer',
        ]);

        $this->student = User::factory()->create([
            'name' => 'Ahmad Dahlan',
            'role' => 'student',
        ]);

        $this->backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($this->backupDir);
    }

    protected function tearDown(): void
    {
        // Bersihkan backup files yang dibuat saat testing
        if (File::isDirectory($this->backupDir)) {
            foreach (File::files($this->backupDir) as $file) {
                if (str_starts_with($file->getFilename(), 'academic-hub-')) {
                    File::delete($file->getRealPath());
                }
            }
        }

        parent::tearDown();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('backups.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_student_is_forbidden_from_accessing_backups(): void
    {
        $response = $this->actingAs($this->student)->get(route('backups.index'));
        $response->assertForbidden();

        $responseDb = $this->actingAs($this->student)->post(route('backups.database'));
        $responseDb->assertForbidden();

        $responseFull = $this->actingAs($this->student)->post(route('backups.full'));
        $responseFull->assertForbidden();
    }

    public function test_staff_can_view_backups_dashboard(): void
    {
        $response = $this->actingAs($this->lecturer)->get(route('backups.index'));

        $response->assertOk();
        $response->assertSee('Cadangan Data & Arsip Mandiri', false);
        $response->assertSee('Cadangan Database Saja (.sqlite)');
        $response->assertSee('Paket Arsip Lengkap (.zip)');
        $response->assertSee('Unduh Cadangan Database Sekarang (.sqlite)');
        $response->assertSee('Unduh Paket Arsip Lengkap (.zip)');
    }

    public function test_staff_can_generate_and_download_database_backup(): void
    {
        $response = $this->actingAs($this->lecturer)->post(route('backups.database'));

        $response->assertOk();
        $this->assertStringContainsString('attachment; filename=academic-hub-db-', $response->headers->get('Content-Disposition') ?? '');
    }

    public function test_staff_can_generate_and_download_full_backup_zip(): void
    {
        $response = $this->actingAs($this->lecturer)->post(route('backups.full'));

        $response->assertOk();
        $this->assertStringContainsString('attachment; filename=academic-hub-full-', $response->headers->get('Content-Disposition') ?? '');
        $this->assertEquals('application/zip', $response->headers->get('Content-Type'));
    }

    public function test_staff_can_download_existing_backup_file(): void
    {
        $dummyFile = 'academic-hub-db-2026-10-28-test.sqlite';
        $dummyPath = $this->backupDir.DIRECTORY_SEPARATOR.$dummyFile;
        File::put($dummyPath, 'DUMMY SQLITE CONTENT');

        $response = $this->actingAs($this->lecturer)->get(route('backups.download', $dummyFile));

        $response->assertOk();
        $this->assertStringContainsString($dummyFile, $response->headers->get('Content-Disposition') ?? '');

        if (File::exists($dummyPath)) {
            File::delete($dummyPath);
        }
    }

    public function test_staff_can_delete_backup_file(): void
    {
        $dummyFile = 'academic-hub-db-2026-10-28-delete.sqlite';
        $dummyPath = $this->backupDir.DIRECTORY_SEPARATOR.$dummyFile;
        File::put($dummyPath, 'DUMMY CONTENT');

        $this->assertTrue(File::exists($dummyPath));

        $response = $this->actingAs($this->lecturer)->delete(route('backups.destroy', $dummyFile));

        $response->assertRedirect(route('backups.index'));
        $response->assertSessionHas('status');
        $this->assertFalse(File::exists($dummyPath));
    }

    public function test_directory_traversal_is_blocked(): void
    {
        $response = $this->actingAs($this->lecturer)->get(route('backups.download', '../evil.sqlite'));
        $this->assertTrue(in_array($response->status(), [400, 404], true));
    }
}
