@extends('layouts.hub')
@section('title', 'Manajemen Pengguna — Superadmin Academic Hub')

@section('content')
<div class="space-y-6">
    {{-- Header Halaman --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex rounded-md bg-purple-100 px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider text-purple-900 border border-purple-200">
                    Otoritas Superadmin
                </span>
                <span class="text-xs text-slate-500 font-medium">Sistem Kontrol Akses Terpusat</span>
            </div>
            <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Pengguna</h1>
            <p class="mt-1.5 text-xs sm:text-sm text-slate-600 max-w-2xl leading-relaxed">
                Kelola hak akses seluruh pengguna platform, penetapan peran (superadmin, admin, dosen, mahasiswa, publik), profil dosen, dan keamanan akun.
            </p>
        </div>
        <div>
            <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-purple-900 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-purple-800 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Tambah Pengguna Baru
            </a>
        </div>
    </div>

    {{-- Kartu Ringkasan Distribusi Peran (Stats Cards) --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-xs">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total Pengguna</span>
            <p class="mt-1.5 text-2xl font-extrabold text-slate-900">{{ number_format($counts['all']) }}</p>
            <span class="mt-1 block text-[10px] text-slate-400">Seluruh basis akun</span>
        </div>

        <div class="rounded-2xl border border-purple-200 bg-purple-50/40 p-4 shadow-xs">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-purple-900">Superadmin & Admin</span>
            <p class="mt-1.5 text-2xl font-extrabold text-purple-900">{{ number_format($counts['superadmin'] + $counts['admin']) }}</p>
            <span class="mt-1 block text-[10px] text-purple-700">{{ $counts['superadmin'] }} Super · {{ $counts['admin'] }} Admin</span>
        </div>

        <div class="rounded-2xl border border-teal-200 bg-teal-50/40 p-4 shadow-xs">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-teal-900">Dosen Pengampu</span>
            <p class="mt-1.5 text-2xl font-extrabold text-teal-900">{{ number_format($counts['lecturer']) }}</p>
            <span class="mt-1 block text-[10px] text-teal-700">Pilar Tri Dharma</span>
        </div>

        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-4 shadow-xs">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-900">Mahasiswa</span>
            <p class="mt-1.5 text-2xl font-extrabold text-emerald-900">{{ number_format($counts['student']) }}</p>
            <span class="mt-1 block text-[10px] text-emerald-700">Peserta Kuliah</span>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 shadow-xs">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Pengguna Umum</span>
            <p class="mt-1.5 text-2xl font-extrabold text-slate-700">{{ number_format($counts['public']) }}</p>
            <span class="mt-1 block text-[10px] text-slate-400">Akun terdaftar baru</span>
        </div>
    </div>

    {{-- Bilah Pencarian & Filter Peran --}}
    <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-xs">
        <form method="get" action="{{ route('users.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            {{-- Filter Tab Peran --}}
            <div class="flex flex-wrap items-center gap-1.5">
                @php
                    $roleOptions = [
                        'all' => 'Semua ('.$counts['all'].')',
                        'superadmin' => 'Superadmin ('.$counts['superadmin'].')',
                        'admin' => 'Admin ('.$counts['admin'].')',
                        'lecturer' => 'Dosen ('.$counts['lecturer'].')',
                        'student' => 'Mahasiswa ('.$counts['student'].')',
                        'public' => 'Umum ('.$counts['public'].')',
                    ];
                @endphp
                @foreach($roleOptions as $roleKey => $roleLabel)
                    <a href="{{ route('users.index', array_merge(request()->query(), ['role' => $roleKey, 'page' => 1])) }}"
                       class="rounded-lg px-2.5 py-1 text-xs font-semibold transition
                       {{ $selectedRole === $roleKey ? 'bg-purple-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $roleLabel }}
                    </a>
                @endforeach
            </div>

            {{-- Kolom Input Pencarian --}}
            <div class="flex items-center gap-2">
                @if(request()->filled('role') && request('role') !== 'all')
                    <input type="hidden" name="role" value="{{ request('role') }}">
                @endif
                <div class="relative w-full sm:w-64">
                    <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Cari nama, email, NIDN..."
                           class="w-full rounded-lg border-slate-300 py-1.5 pl-8 pr-3 text-xs focus:border-purple-800 focus:ring-purple-800">
                    <svg class="absolute left-2.5 top-2 h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-800 transition">
                    Cari
                </button>
                @if($searchQuery || request()->filled('study_program_id'))
                    <a href="{{ route('users.index') }}" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Daftar Pengguna --}}
    <div class="rounded-2xl border border-slate-200/90 bg-white overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="border-b border-slate-200 bg-slate-50 font-bold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Lengkap & Akun</th>
                        <th class="py-3 px-4 w-36 text-center">Peran Sistem</th>
                        <th class="py-3 px-4">Informasi Akademik / Profil</th>
                        <th class="py-3 px-4 w-32">Terdaftar Sejak</th>
                        <th class="py-3 px-4 w-40 text-center">Aksi Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $idx => $user)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 text-center text-slate-400 font-medium">
                                {{ $users->firstItem() + $idx }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full font-bold text-xs
                                        {{ $user->role === 'superadmin' ? 'bg-purple-100 text-purple-900' :
                                           ($user->role === 'admin' ? 'bg-blue-100 text-blue-900' :
                                           ($user->role === 'lecturer' ? 'bg-teal-100 text-teal-900' :
                                           ($user->role === 'student' ? 'bg-emerald-100 text-emerald-900' : 'bg-slate-100 text-slate-700'))) }}">
                                        {{ $user->initials() }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                            <span>{{ $user->name }}</span>
                                            @if($user->id === auth()->id())
                                                <span class="rounded bg-slate-100 px-1.5 py-0.2 text-[9px] font-bold text-slate-600 uppercase">Anda</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-mono">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $badgeStyle = match($user->role) {
                                        'superadmin' => 'bg-purple-50 text-purple-900 border-purple-200',
                                        'admin' => 'bg-blue-50 text-blue-900 border-blue-200',
                                        'lecturer' => 'bg-teal-50 text-teal-900 border-teal-200',
                                        'student' => 'bg-emerald-50 text-emerald-900 border-emerald-200',
                                        default => 'bg-slate-50 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold border {{ $badgeStyle }}">
                                    {{ $user->roleLabel() }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($user->role === 'lecturer' && $user->lecturerProfile)
                                    <div class="font-semibold text-slate-800">
                                        {{ $user->lecturerProfile->studyProgram?->name ?: 'Belum diatur prodi' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        NIDN: {{ $user->lecturerProfile->nidn ?: '-' }} · {{ $user->lecturerProfile->functionalRankLabel() }} · {{ $user->lecturerProfile->highest_education }}
                                    </div>
                                @elseif($user->role === 'student')
                                    <span class="text-slate-600">Mahasiswa Peserta</span>
                                @elseif($user->role === 'superadmin')
                                    <span class="text-purple-700 font-medium text-[11px]">Akses Penuh Semua Modul & Konfigurasi</span>
                                @elseif($user->role === 'admin')
                                    <span class="text-blue-700 font-medium text-[11px]">Administrator Institusi</span>
                                @else
                                    <span class="text-slate-400 italic">Pengguna Publik</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 text-[11px]">
                                {{ $user->created_at?->format('d M Y, H:i') ?: '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- Tombol Edit --}}
                                    <a href="{{ route('users.edit', $user) }}" class="rounded-lg border border-slate-200 bg-white p-1.5 text-slate-600 hover:bg-slate-100 hover:text-purple-900 transition" title="Edit Pengguna & Peran">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    {{-- Tombol Hapus (Dilarang menghapus akun sendiri) --}}
                                    @if($user->id !== auth()->id())
                                        <form method="post" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ $user->name }} ({{ $user->email }})? Tindakan ini tidak dapat dibatalkan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-slate-200 bg-white p-1.5 text-slate-400 hover:border-red-200 hover:bg-red-50 hover:text-red-700 transition" title="Hapus Pengguna">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="rounded-lg border border-slate-100 p-1.5 text-slate-300 cursor-not-allowed" title="Anda tidak dapat menghapus akun Anda sendiri">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500">
                                Tidak ada data pengguna yang sesuai dengan kriteria filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
