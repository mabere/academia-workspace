<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('hub:user-role {email} {role : lecturer, admin, superadmin, student, or public} {--force : Confirm the trusted operator action in production}')]
#[Description('Assign a platform role to an existing user from the trusted server console')]
class SetAcademicHubUserRole extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $role = (string) $this->argument('role');
        if (! in_array($role, ['lecturer', 'admin', 'superadmin', 'student', 'public'], true)) {
            $this->error('Role tidak valid.');

            return self::INVALID;
        }

        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Di production, ulangi dengan --force setelah identitas operator diverifikasi.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('Pengguna tidak ditemukan.');

            return self::FAILURE;
        }

        $user->update(['role' => $role]);
        $this->info("Role {$user->email} diperbarui menjadi {$role}.");

        return self::SUCCESS;
    }
}
