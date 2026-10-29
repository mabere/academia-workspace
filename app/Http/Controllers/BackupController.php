<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class BackupController extends Controller
{
    private string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()->isStaff(), 403, 'Akses terbatas untuk staf dan dosen.');

        File::ensureDirectoryExists($this->backupDir);

        // Informasi Database Aktif
        $dbPath = $this->getActiveDatabasePath();
        $dbSize = File::exists($dbPath) ? File::size($dbPath) : 0;
        $dbDriver = config('database.default', 'sqlite');

        // Informasi Berkas Media & Materi
        $storagePrivate = storage_path('app/private');
        $storagePublic = storage_path('app/public');
        $totalStorageFiles = 0;
        $totalStorageBytes = 0;

        if (File::isDirectory($storagePrivate)) {
            $files = File::allFiles($storagePrivate);
            $totalStorageFiles += count($files);
            foreach ($files as $file) {
                $totalStorageBytes += $file->getSize();
            }
        }

        if (File::isDirectory($storagePublic)) {
            $files = File::allFiles($storagePublic);
            $totalStorageFiles += count($files);
            foreach ($files as $file) {
                $totalStorageBytes += $file->getSize();
            }
        }

        // Daftar Berkas Backup yang Tersimpan di Server
        $backupFiles = [];
        $totalBackupBytes = 0;

        if (File::isDirectory($this->backupDir)) {
            $files = File::files($this->backupDir);
            // Urutkan dari yang paling baru
            usort($files, fn ($a, $b) => $b->getMTime() <=> $a->getMTime());

            foreach ($files as $file) {
                $filename = $file->getFilename();
                $size = $file->getSize();
                $totalBackupBytes += $size;
                $isZip = str_ends_with($filename, '.zip');

                $backupFiles[] = [
                    'filename' => $filename,
                    'is_zip' => $isZip,
                    'type_label' => $isZip ? 'Paket Lengkap (.zip)' : 'Database Saja (.sqlite)',
                    'size_bytes' => $size,
                    'size_formatted' => $this->formatBytes($size),
                    'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                ];
            }
        }

        return view('backups.index', [
            'dbSizeFormatted' => $this->formatBytes($dbSize),
            'dbDriver' => strtoupper($dbDriver),
            'totalStorageFiles' => $totalStorageFiles,
            'totalStorageFormatted' => $this->formatBytes($totalStorageBytes),
            'totalBackupsCount' => count($backupFiles),
            'totalBackupsFormatted' => $this->formatBytes($totalBackupBytes),
            'backupFiles' => $backupFiles,
        ]);
    }

    public function createDatabaseBackup(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()->isStaff(), 403, 'Akses terbatas untuk staf dan dosen.');

        File::ensureDirectoryExists($this->backupDir);

        $timestamp = now()->format('Y-m-d-His');
        $filename = "academic-hub-db-{$timestamp}.sqlite";
        $targetPath = $this->backupDir.DIRECTORY_SEPARATOR.$filename;

        $this->performDatabaseSnapshot($targetPath);

        return response()->download($targetPath, $filename, [
            'Content-Type' => 'application/x-sqlite3',
        ]);
    }

    public function createFullBackup(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()->isStaff(), 403, 'Akses terbatas untuk staf dan dosen.');

        File::ensureDirectoryExists($this->backupDir);

        $timestamp = now()->format('Y-m-d-His');
        $zipFilename = "academic-hub-full-{$timestamp}.zip";
        $zipPath = $this->backupDir.DIRECTORY_SEPARATOR.$zipFilename;

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat arsip kompresi ZIP di server.');
        }

        // 1. Snapshot database ke temporary file
        $tempDbName = "temp-db-{$timestamp}.sqlite";
        $tempDbPath = $this->backupDir.DIRECTORY_SEPARATOR.$tempDbName;
        $this->performDatabaseSnapshot($tempDbPath);
        $zip->addFile($tempDbPath, 'database.sqlite');

        // 2. Berkas Private (Bahan Ajar & Dokumen Penelitian)
        $storagePrivate = storage_path('app/private');
        if (File::isDirectory($storagePrivate)) {
            foreach (File::allFiles($storagePrivate) as $file) {
                $zip->addFile($file->getRealPath(), 'uploads/private/'.$file->getRelativePathname());
            }
        }

        // 3. Berkas Public (Avatars & Media Publik)
        $storagePublic = storage_path('app/public');
        if (File::isDirectory($storagePublic)) {
            foreach (File::allFiles($storagePublic) as $file) {
                $zip->addFile($file->getRealPath(), 'uploads/public/'.$file->getRelativePathname());
            }
        }

        // 4. File Readme Informasi Cadangan
        $readme = "ACADEMIA WORKSPACE — ARSIP CADANGAN LENGKAP (FULL BACKUP)\n"
            ."============================================================\n"
            .'Waktu Pencadangan : '.now()->translatedFormat('l, d F Y H:i:s')."\n"
            .'Oleh Akun Dosen    : '.$request->user()->name.' ('.$request->user()->email.")\n"
            ."Aplikasi          : ELT Academic Hub · Ruang Kerja Tri Dharma\n\n"
            ."Struktur Berkas dalam Arsip Ini:\n"
            ."- database.sqlite             : Basis data SQLite lengkap (akun, kelas, jurnal, tugas, nilai)\n"
            ."- uploads/private/           : Materi perkuliahan PDF/DOCX/Audio/Video & dokumen kajian\n"
            ."- uploads/public/            : Berkas media publik & profil\n\n"
            ."Petunjuk Pemulihan (Restore):\n"
            ."Jika memindahkan ke server/laptop baru, ekstrak berkas database.sqlite ke direktori database/\n"
            ."dan isi folder uploads/private/ ke storage/app/private/.\n";
        $zip->addFromString('README-CADANGAN.txt', $readme);

        $zip->close();

        // Hapus file temporary database snapshot setelah masuk ke zip
        if (File::exists($tempDbPath)) {
            File::delete($tempDbPath);
        }

        return response()->download($zipPath, $zipFilename, [
            'Content-Type' => 'application/zip',
        ]);
    }

    public function download(Request $request, string $filename): BinaryFileResponse
    {
        abort_unless($request->user()->isStaff(), 403, 'Akses terbatas untuk staf dan dosen.');

        // Proteksi Directory Traversal
        if (basename($filename) !== $filename) {
            abort(400, 'Permintaan berkas tidak valid.');
        }

        $filePath = $this->backupDir.DIRECTORY_SEPARATOR.$filename;
        if (! File::exists($filePath)) {
            abort(404, 'Berkas cadangan tidak ditemukan di server.');
        }

        $mime = str_ends_with($filename, '.zip') ? 'application/zip' : 'application/x-sqlite3';

        return response()->download($filePath, $filename, ['Content-Type' => $mime]);
    }

    public function destroy(Request $request, string $filename): RedirectResponse
    {
        abort_unless($request->user()->isStaff(), 403, 'Akses terbatas untuk staf dan dosen.');

        if (basename($filename) !== $filename) {
            abort(400, 'Permintaan berkas tidak valid.');
        }

        $filePath = $this->backupDir.DIRECTORY_SEPARATOR.$filename;
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        return redirect()->route('backups.index')->with('status', "Berkas cadangan '{$filename}' berhasil dihapus dari server.");
    }

    private function performDatabaseSnapshot(string $targetPath): void
    {
        $currentDb = $this->getActiveDatabasePath();

        if (config('database.default') === 'sqlite') {
            $safeTarget = str_replace('\\', '/', $targetPath);
            try {
                // SQLite 3.27+ atomic online snapshot
                DB::statement("VACUUM INTO '{$safeTarget}'");

                return;
            } catch (\Throwable) {
                // Fallback copy aman jika VACUUM INTO terkendala
            }
        }

        if (File::exists($currentDb)) {
            File::copy($currentDb, $targetPath);
        } else {
            // Jika file db belum ada fisik (misal in-memory saat testing), sentuh file kosong
            File::put($targetPath, '');
        }
    }

    private function getActiveDatabasePath(): string
    {
        $dbPath = config('database.connections.sqlite.database');

        if (is_string($dbPath) && File::exists($dbPath)) {
            return $dbPath;
        }

        return database_path('database.sqlite');
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}
