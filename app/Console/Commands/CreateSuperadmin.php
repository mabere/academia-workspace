<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

#[Signature('hub:superadmin:create {email} {name} {--force : Confirm the trusted operator action in production}')]
#[Description('Create a superadmin account with a password entered securely in the console')]
class CreateSuperadmin extends Command
{
    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Di production, ulangi dengan --force setelah identitas operator diverifikasi.');

            return self::FAILURE;
        }

        $email = mb_strtolower(trim((string) $this->argument('email')));
        $name = trim((string) $this->argument('name'));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '' || mb_strlen($name) > 120) {
            $this->error('Nama atau email tidak valid.');

            return self::INVALID;
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->error('Email sudah digunakan. Gunakan hub:user-role untuk akun yang sudah ada.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Masukkan kata sandi superadmin (min. 12 karakter)');
        validator(['password' => $password], ['password' => ['required', Password::min(12)]])->validate();
        $confirmation = (string) $this->secret('Ulangi kata sandi');
        if (! hash_equals($password, $confirmation)) {
            $this->error('Konfirmasi kata sandi tidak cocok. Akun tidak dibuat.');

            return self::FAILURE;
        }

        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'role' => 'superadmin',
            'password' => Hash::make($password),
        ]);

        $this->info("Akun superadmin {$user->email} berhasil dibuat.");

        return self::SUCCESS;
    }
}
