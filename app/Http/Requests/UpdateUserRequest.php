<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'superadmin';
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'role' => ['required', 'string', 'in:superadmin,admin,lecturer,student,public'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            // Profil Dosen
            'study_program_id' => ['nullable', 'exists:study_programs,id'],
            'nidn' => ['nullable', 'string', 'max:20'],
            'nip' => ['nullable', 'string', 'max:30'],
            'functional_rank' => ['nullable', 'string', 'in:tenaga_pengajar,asisten_ahli,lektor,lektor_kepala,guru_besar'],
            'highest_education' => ['nullable', 'string', 'in:S1,S2,S3'],
            'is_certified' => ['nullable', 'boolean'],
            'target_sks' => ['nullable', 'integer', 'min:0', 'max:24'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Alamat email ini sudah terdaftar di sistem.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'role.required' => 'Peran pengguna wajib ditentukan.',
            'role.in' => 'Peran yang dipilih tidak valid.',
        ];
    }
}
