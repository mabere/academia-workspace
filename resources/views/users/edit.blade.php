@extends('layouts.hub')
@section('title', 'Edit Pengguna — '.$user->name.' — Superadmin Academic Hub')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('users.index') }}" class="text-xs font-semibold text-purple-900 hover:text-purple-700">
                    &larr; Kembali ke Manajemen Pengguna
                </a>
            </div>
            <h1 class="mt-2 text-2xl font-extrabold text-slate-900 tracking-tight">Edit Pengguna: {{ $user->name }}</h1>
            <p class="mt-1 text-xs text-slate-500">
                Perbarui identitas akun, ubah peran otoritas, dan kelola profil akademik.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold border
                {{ $user->role === 'superadmin' ? 'bg-purple-50 text-purple-900 border-purple-200' :
                   ($user->role === 'admin' ? 'bg-blue-50 text-blue-900 border-blue-200' :
                   ($user->role === 'lecturer' ? 'bg-teal-50 text-teal-900 border-teal-200' :
                   ($user->role === 'student' ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : 'bg-slate-50 text-slate-700 border-slate-200'))) }}">
                Peran Saat Ini: {{ $user->roleLabel() }}
            </span>
        </div>
    </div>

    @if($user->id === auth()->id())
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-900">
            <strong>Perhatian:</strong> Anda sedang menyunting akun Anda sendiri yang sedang aktif digunakan. Peran superadmin tidak dapat diturunkan demi integritas kendali sistem.
        </div>
    @endif

    <form method="post" action="{{ route('users.update', $user) }}" class="space-y-6" x-data="{ role: '{{ old('role', $user->role) }}' }">
        @csrf
        @method('PUT')

        {{-- Seksi 1: Data Akun Utama --}}
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
                1. Identitas Akun Pengguna
            </h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700">Nama Lengkap & Gelar *</label>
                    <input type="text" id="name" name="name" required value="{{ old('name', $user->name) }}"
                           class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-purple-800 focus:ring-purple-800">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700">Alamat Email *</label>
                    <input type="email" id="email" name="email" required value="{{ old('email', $user->email) }}"
                           class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-purple-800 focus:ring-purple-800">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Pemilihan Peran Sistem --}}
            <div class="pt-2">
                <label class="block text-xs font-semibold text-slate-700 mb-2">Peran Sistem (Hak Akses) *</label>
                <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition"
                           :class="role === 'lecturer' ? 'border-teal-700 bg-teal-50/50 text-teal-900 ring-1 ring-teal-700' : 'border-slate-200 hover:bg-slate-50'">
                        <input type="radio" name="role" value="lecturer" x-model="role" class="mt-0.5 text-teal-700 focus:ring-teal-700">
                        <div>
                            <span class="block text-xs font-bold text-slate-900">Dosen Pengampu</span>
                            <span class="block text-[11px] text-slate-500">Akses Tri Dharma, perkuliahan, bahan ajar, dan BKD.</span>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition"
                           :class="role === 'student' ? 'border-emerald-700 bg-emerald-50/50 text-emerald-900 ring-1 ring-emerald-700' : 'border-slate-200 hover:bg-slate-50'">
                        <input type="radio" name="role" value="student" x-model="role" class="mt-0.5 text-emerald-700 focus:ring-emerald-700">
                        <div>
                            <span class="block text-xs font-bold text-slate-900">Mahasiswa</span>
                            <span class="block text-[11px] text-slate-500">Peserta kelas kuliah, submisi tugas, dan akses materi.</span>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition"
                           :class="role === 'admin' ? 'border-blue-700 bg-blue-50/50 text-blue-900 ring-1 ring-blue-700' : 'border-slate-200 hover:bg-slate-50'">
                        <input type="radio" name="role" value="admin" x-model="role" class="mt-0.5 text-blue-700 focus:ring-blue-700">
                        <div>
                            <span class="block text-xs font-bold text-slate-900">Administrator</span>
                            <span class="block text-[11px] text-slate-500">Pengelola akademik prodi & universitas.</span>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition"
                           :class="role === 'superadmin' ? 'border-purple-700 bg-purple-50/50 text-purple-900 ring-1 ring-purple-700' : 'border-slate-200 hover:bg-slate-50'">
                        <input type="radio" name="role" value="superadmin" x-model="role" class="mt-0.5 text-purple-700 focus:ring-purple-700">
                        <div>
                            <span class="block text-xs font-bold text-slate-900">Superadmin</span>
                            <span class="block text-[11px] text-slate-500">Akses penuh semua modul dan manajemen user.</span>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition sm:col-span-2"
                           :class="role === 'public' ? 'border-slate-700 bg-slate-50 text-slate-900 ring-1 ring-slate-700' : 'border-slate-200 hover:bg-slate-50'">
                        <input type="radio" name="role" value="public" x-model="role" class="mt-0.5 text-slate-700 focus:ring-slate-700">
                        <div>
                            <span class="block text-xs font-bold text-slate-900">Pengguna Umum</span>
                            <span class="block text-[11px] text-slate-500">Akun publik tanpa hak akses dosen/staf akademik.</span>
                        </div>
                    </label>
                </div>
                @error('role')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status Keaktifan Akun --}}
            <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->isActive())) {{ $user->id === auth()->id() ? 'disabled' : '' }} class="rounded text-purple-800 focus:ring-purple-800">
                        <span class="text-xs font-bold text-slate-800">Status Akun Aktif (Dapat Login & Akses Platform)</span>
                    </label>
                    @if($user->id === auth()->id())
                        <p class="mt-1 text-[11px] text-slate-400">Akun Anda sendiri harus selalu aktif demi keamanan akses sistem.</p>
                    @else
                        <p class="mt-1 text-[11px] text-slate-500">Jika dinonaktifkan, akun ini diblokir dari login ke aplikasi hingga diaktifkan kembali.</p>
                    @endif
                </div>
                <div>
                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold border {{ $user->isActive() ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200' }}">
                        {{ $user->isActive() ? '● Akun Aktif' : '○ Akun Nonaktif' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Seksi 2: Informasi Khusus Dosen (Tampil saat role = lecturer) --}}
        @php
            $profile = $user->lecturerProfile;
        @endphp
        <div x-show="role === 'lecturer'" class="rounded-2xl border border-teal-200 bg-teal-50/20 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-teal-100 pb-2">
                <h2 class="text-sm font-bold uppercase tracking-wider text-teal-900">
                    2. Profil Akademik Dosen (Untuk BKD & Siakad)
                </h2>
                <span class="rounded bg-teal-100 px-2 py-0.5 text-[10px] font-bold text-teal-800 uppercase">Khusus Dosen</span>
            </div>

            <div>
                <label for="study_program_id" class="block text-xs font-semibold text-slate-700">Program Studi Homebase</label>
                <select id="study_program_id" name="study_program_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700 bg-white">
                    <option value="">-- Pilih Program Studi --</option>
                    @foreach($studyPrograms as $prodi)
                        <option value="{{ $prodi->id }}" @selected(old('study_program_id', $profile?->study_program_id) == $prodi->id)>
                            {{ $prodi->name }} ({{ $prodi->level }}) — {{ $prodi->faculty->name ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="nidn" class="block text-xs font-semibold text-slate-700">NIDN / NIDK</label>
                    <input type="text" id="nidn" name="nidn" value="{{ old('nidn', $profile?->nidn) }}" placeholder="Contoh: 0012058001"
                           class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700 bg-white font-mono">
                </div>

                <div>
                    <label for="nip" class="block text-xs font-semibold text-slate-700">NIP / NUP</label>
                    <input type="text" id="nip" name="nip" value="{{ old('nip', $profile?->nip) }}" placeholder="Contoh: 198005122005011002"
                           class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700 bg-white font-mono">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="functional_rank" class="block text-xs font-semibold text-slate-700">Jabatan Fungsional</label>
                    <select id="functional_rank" name="functional_rank" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700 bg-white">
                        <option value="tenaga_pengajar" @selected(old('functional_rank', $profile?->functional_rank) === 'tenaga_pengajar')>Tenaga Pengajar</option>
                        <option value="asisten_ahli" @selected(old('functional_rank', $profile?->functional_rank) === 'asisten_ahli')>Asisten Ahli</option>
                        <option value="lektor" @selected(old('functional_rank', $profile?->functional_rank ?? 'lektor') === 'lektor')>Lektor</option>
                        <option value="lektor_kepala" @selected(old('functional_rank', $profile?->functional_rank) === 'lektor_kepala')>Lektor Kepala</option>
                        <option value="guru_besar" @selected(old('functional_rank', $profile?->functional_rank) === 'guru_besar')>Guru Besar / Profesor</option>
                    </select>
                </div>

                <div>
                    <label for="highest_education" class="block text-xs font-semibold text-slate-700">Pendidikan Terakhir</label>
                    <select id="highest_education" name="highest_education" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700 bg-white">
                        <option value="S2" @selected(old('highest_education', $profile?->highest_education ?? 'S2') === 'S2')>Magister / Master (S2)</option>
                        <option value="S3" @selected(old('highest_education', $profile?->highest_education) === 'S3')>Doktor / Ph.D (S3)</option>
                        <option value="S1" @selected(old('highest_education', $profile?->highest_education) === 'S1')>Sarjana (S1)</option>
                    </select>
                </div>

                <div>
                    <label for="target_sks" class="block text-xs font-semibold text-slate-700">Target Beban SKS BKD</label>
                    <input type="number" id="target_sks" name="target_sks" value="{{ old('target_sks', $profile?->target_sks ?? 12) }}" min="0" max="24"
                           class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700 bg-white">
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_certified" value="1" @checked(old('is_certified', $profile?->is_certified ?? true))
                           class="rounded text-teal-700 focus:ring-teal-700">
                    <span class="text-xs font-semibold text-slate-800">Dosen Memiliki Sertifikat Pendidik (Serdos)</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('users.index') }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                Batal
            </a>
            <button type="submit" class="rounded-xl bg-purple-900 px-6 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-purple-800 transition">
                Simpan Perubahan Pengguna
            </button>
        </div>
    </form>

    {{-- Seksi Terpisah: Reset Cepat Kata Sandi --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
            Reset Kata Sandi Akun Pengguna
        </h2>
        <p class="mt-2 text-xs text-slate-500">
            Sebagai superadmin, Anda dapat langsung mengatur ulang kata sandi pengguna ini jika mereka lupa kredensial login.
        </p>

        <form method="post" action="{{ route('users.reset-password', $user) }}" class="mt-4 space-y-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="new_password" class="block text-xs font-semibold text-slate-700">Kata Sandi Baru *</label>
                    <input type="password" id="new_password" name="password" required minlength="8"
                           placeholder="Minimal 8 karakter baru"
                           class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-purple-800 focus:ring-purple-800">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new_password_confirmation" class="block text-xs font-semibold text-slate-700">Konfirmasi Kata Sandi Baru *</label>
                    <input type="password" id="new_password_confirmation" name="password_confirmation" required minlength="8"
                           placeholder="Ulangi kata sandi baru"
                           class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-purple-800 focus:ring-purple-800">
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="rounded-xl border border-purple-900 bg-purple-50 px-5 py-2 text-xs font-bold text-purple-900 hover:bg-purple-100 transition">
                    Perbarui Kata Sandi
                </button>
            </div>
        </form>
    </div>

    {{-- Zona Tindakan Khusus: Status & Penghapusan Akun --}}
    @if($user->id !== auth()->id())
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
                Aksi Khusus Akun Pengguna
            </h2>

            <div class="flex flex-wrap items-center justify-between gap-4 p-4 rounded-xl border {{ $user->isActive() ? 'border-amber-200 bg-amber-50/50' : 'border-emerald-200 bg-emerald-50/50' }}">
                <div>
                    <h3 class="text-xs font-bold text-slate-900">
                        {{ $user->isActive() ? 'Nonaktifkan Akun Pengguna' : 'Aktifkan Kembali Akun Pengguna' }}
                    </h3>
                    <p class="text-[11px] text-slate-600 mt-0.5">
                        {{ $user->isActive() ? 'Pengguna tidak akan dapat login ke platform sampai diaktifkan kembali.' : 'Buka blokir agar pengguna dapat kembali masuk dan menggunakan platform.' }}
                    </p>
                </div>
                <form method="post" action="{{ route('users.toggle-status', $user) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="rounded-xl px-4 py-2 text-xs font-bold transition {{ $user->isActive() ? 'bg-amber-800 text-white hover:bg-amber-700' : 'bg-emerald-800 text-white hover:bg-emerald-700' }}">
                        {{ $user->isActive() ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                    </button>
                </form>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4 p-4 rounded-xl border border-red-200 bg-red-50/40">
                <div>
                    <h3 class="text-xs font-bold text-red-900">Hapus Akun Pengguna Secara Permanen</h3>
                    <p class="text-[11px] text-red-700 mt-0.5">
                        Menghapus akun pengguna akan mencabut seluruh data akses. Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <form method="post" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }} ({{ $user->email }}) secara permanen?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl bg-red-700 px-4 py-2 text-xs font-bold text-white hover:bg-red-800 transition">
                        Hapus Akun Pengguna
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
