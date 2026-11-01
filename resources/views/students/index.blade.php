@extends('layouts.hub')
@section('title', 'Direktori & Plotting Mahasiswa — Academic Hub')

@section('content')
<div class="space-y-6" x-data="{ panel: '{{ $errors->has('raw_list') ? 'bulk' : ($errors->any() ? 'single' : 'none') }}' }">
    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-teal-800 border border-teal-200/80">
                    <span class="h-1.5 w-1.5 rounded-full bg-teal-600"></span>
                    Manajemen Peserta Perkuliahan
                </span>
            </div>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Direktori & Plotting Mahasiswa</h1>
            <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-600">
                Kelola daftar mahasiswa pribadi Anda lintas Program Studi dan Angkatan, serta assign mahasiswa secara satuan maupun kolektif ke Mata Kuliah yang sedang diprogramkan.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" @click="panel = panel === 'single' ? 'none' : 'single'"
                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:bg-slate-800 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Mahasiswa</span>
            </button>
            <button type="button" @click="panel = panel === 'bulk' ? 'none' : 'bulk'"
                class="inline-flex items-center gap-2 rounded-xl bg-teal-800 px-4 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:bg-teal-900 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Paste Kolektif (Excel/SIAKAD)</span>
            </button>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="font-bold">Terdapat kesalahan input:</p>
            <ul class="mt-1 list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Statistik Ringkas --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Mahasiswa</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ number_format($totalStudents) }}</span>
                <span class="text-xs font-medium text-slate-500">Terdaftar di Direktori</span>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-teal-800">Aktif Memprogram MK</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-extrabold text-teal-900">{{ number_format($enrolledStudentsCount) }}</span>
                <span class="text-xs font-medium text-teal-700">Mahasiswa Ter-assign</span>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mata Kuliah Tersedia</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $courses->count() }}</span>
                <a href="{{ route('courses.index') }}" class="text-xs font-bold text-teal-800 hover:underline">Lihat Kelas &rarr;</a>
            </div>
        </div>
    </div>

    {{-- PANEL 1: TAMBAH MAHASISWA SATUAN + ASSIGN MK --}}
    <div x-show="panel === 'single'" x-cloak class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-7 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Tambah Mahasiswa Satuan & Plotting Mata Kuliah</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Jika email dikosongkan, sistem otomatis membuatkan email berbasis NIM dan kata sandi default sesuai NIM.</p>
            </div>
            <button type="button" @click="panel = 'none'" class="rounded-lg p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition" title="Tutup form">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form method="post" action="{{ route('students.store') }}" class="mt-5 space-y-5">
            @csrf
            <div class="grid gap-4 sm:grid-cols-4">
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700">NIM Mahasiswa *</label>
                    <input type="text" name="nim" required maxlength="32" placeholder="Contoh: 240105001" value="{{ old('nim') }}"
                        class="mt-1.5 w-full font-mono font-bold">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700">Nama Lengkap Mahasiswa *</label>
                    <input type="text" name="name" required maxlength="150" placeholder="Nama lengkap sesuai absen..." value="{{ old('name') }}"
                        class="mt-1.5 w-full">
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700">Email (Opsional)</label>
                    <input type="email" name="email" maxlength="150" placeholder="Otomatis jika kosong" value="{{ old('email') }}"
                        class="mt-1.5 w-full">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs sm:text-sm font-semibold text-slate-700">Program Studi</label>
                        @if(auth()->user()->isSuperadmin())
                            <a href="{{ route('academic-structure.index') }}" class="text-xs font-semibold text-teal-700 hover:underline">+ Kelola Prodi</a>
                        @endif
                    </div>
                    <select name="study_program_id" class="mt-1.5 w-full">
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach ($studyPrograms as $prodi)
                            <option value="{{ $prodi->id }}" @selected(old('study_program_id') == $prodi->id)>
                                {{ $prodi->name }} ({{ $prodi->level }}) — {{ $prodi->faculty?->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700">Tahun Angkatan</label>
                    <input type="number" name="cohort_year" min="2000" max="2099" placeholder="{{ now()->year }}" value="{{ old('cohort_year', now()->year) }}"
                        class="mt-1.5 w-full">
                </div>
            </div>

            @if ($courses->isNotEmpty())
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 space-y-3">
                    <label class="block text-xs sm:text-sm font-bold text-slate-800">Assign Langsung ke Mata Kuliah yang Diprogramkan (Opsional):</label>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($courses as $course)
                            <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 cursor-pointer hover:border-teal-400 hover:shadow-2xs transition">
                                <input type="checkbox" name="course_ids[]" value="{{ $course->id }}" class="mt-1 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                                <span class="min-w-0 flex-1">
                                    <span class="block text-xs sm:text-sm font-bold text-slate-900 truncate">{{ $course->code }} — {{ $course->name }}</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">{{ $course->class_group }} &middot; {{ $course->academicPeriod?->name }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex justify-end gap-2.5 pt-2">
                <button type="button" @click="panel = 'none'" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:bg-slate-800 transition">
                    Simpan & Assign Mahasiswa
                </button>
            </div>
        </form>
    </div>

    {{-- PANEL 2: IMPOR / PASTE KOLEKTIF CEPAT --}}
    <div x-show="panel === 'bulk'" x-cloak class="rounded-3xl border border-teal-200 bg-teal-50/40 p-6 sm:p-7 shadow-sm">
        <div class="flex items-center justify-between border-b border-teal-200/70 pb-4">
            <div>
                <h2 class="text-lg font-bold text-teal-950">Impor / Paste Kolektif Cepat dari Excel atau SIAKAD</h2>
                <p class="text-xs sm:text-sm text-teal-800 mt-0.5">Salin dan tempel daftar NIM serta Nama Mahasiswa langsung dari tabel Excel/absen kelas.</p>
            </div>
            <button type="button" @click="panel = 'none'" class="rounded-lg p-1.5 text-teal-800 hover:text-teal-950 hover:bg-teal-100 transition" title="Tutup form">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form method="post" action="{{ route('students.bulk-store') }}" class="mt-5 space-y-5">
            @csrf
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-800">
                    Daftar Mahasiswa (1 Mahasiswa per Baris: <code class="rounded bg-white px-2 py-0.5 text-teal-900 border border-teal-200 font-mono text-xs">NIM ; Nama Lengkap ; Email(opsional)</code>) *
                </label>
                <p class="mt-1 text-xs text-slate-600">
                    Mendukung pemisah titik koma (<code class="font-bold">;</code>), koma (<code class="font-bold">,</code>), atau <code class="font-bold">Tab</code> langsung dari copy-paste kolom Excel.
                </p>
                <textarea name="raw_list" rows="6" required
                    placeholder="240105001 ; Siti Aminah&#10;240105002 ; Budi Santoso ; budi@student.ac.id&#10;240105003	Rina Kartika"
                    class="mt-2 w-full rounded-2xl border-slate-300 bg-white font-mono text-sm leading-relaxed">{{ old('raw_list') }}</textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700">Set Program Studi untuk Daftar Ini</label>
                    <select name="study_program_id" class="mt-1.5 w-full bg-white">
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach ($studyPrograms as $prodi)
                            <option value="{{ $prodi->id }}">{{ $prodi->name }} ({{ $prodi->level }}) — {{ $prodi->faculty?->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700">Set Tahun Angkatan</label>
                    <input type="number" name="cohort_year" min="2000" max="2099" value="{{ now()->year }}"
                        class="mt-1.5 w-full bg-white">
                </div>
            </div>

            @if ($courses->isNotEmpty())
                <div class="rounded-2xl border border-teal-200 bg-white p-5 space-y-3">
                    <label class="block text-xs sm:text-sm font-bold text-slate-800">Assign Seluruh Daftar Ini ke Mata Kuliah:</label>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($courses as $course)
                            <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3 cursor-pointer hover:border-teal-500 hover:bg-teal-50/30 transition">
                                <input type="checkbox" name="course_ids[]" value="{{ $course->id }}" class="mt-1 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                                <span class="min-w-0 flex-1">
                                    <span class="block text-xs sm:text-sm font-bold text-slate-900 truncate">{{ $course->code }} — {{ $course->name }}</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">{{ $course->class_group }} &middot; {{ $course->academicPeriod?->name }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex justify-end gap-2.5 pt-2">
                <button type="button" @click="panel = 'none'" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" class="rounded-xl bg-teal-800 px-6 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:bg-teal-900 transition">
                    Proses Impor & Assign ke Kelas
                </button>
            </div>
        </form>
    </div>

    {{-- FILTER & PENCARIAN --}}
    <form method="get" action="{{ route('students.index') }}" class="rounded-3xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-sm">
        <div class="grid gap-4 sm:grid-cols-12 items-end">
            <div class="sm:col-span-4">
                <label class="block text-xs font-semibold text-slate-700">Cari NIM / Nama / Email</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik NIM atau nama mahasiswa..."
                    class="mt-1.5 w-full">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-xs font-semibold text-slate-700">Program Studi</label>
                <select name="study_program_id" class="mt-1.5 w-full">
                    <option value="">Semua Prodi</option>
                    @foreach ($studyPrograms as $prodi)
                        <option value="{{ $prodi->id }}" @selected(request('study_program_id') == $prodi->id)>
                            {{ $prodi->code }} — {{ $prodi->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700">Angkatan</label>
                <select name="cohort_year" class="mt-1.5 w-full">
                    <option value="">Semua</option>
                    @foreach ($cohortYears as $year)
                        <option value="{{ $year }}" @selected(request('cohort_year') == $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-3">
                <label class="block text-xs font-semibold text-slate-700">Mata Kuliah Diprogramkan</label>
                <select name="course_id" class="mt-1.5 w-full">
                    <option value="">Semua Mata Kuliah</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected(request('course_id') == $course->id)>
                            {{ $course->code }} - {{ $course->name }} ({{ $course->class_group }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-end gap-2.5 border-t border-slate-100 pt-3">
            @if (request()->anyFilled(['search', 'study_program_id', 'cohort_year', 'course_id']))
                <a href="{{ route('students.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-xs sm:text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Reset Filter
                </a>
            @endif
            <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2 text-xs sm:text-sm font-bold text-white hover:bg-slate-800 transition">
                Terapkan Filter
            </button>
        </div>
    </form>

    {{-- TABEL DIREKTORI MAHASISWA --}}
    <div class="rounded-3xl border border-slate-200/90 bg-white overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="border-b border-slate-200 bg-slate-50/80 font-bold uppercase tracking-wider text-slate-600 text-xs">
                    <tr>
                        <th class="py-3.5 px-5 w-32">NIM</th>
                        <th class="py-3.5 px-5">Nama Mahasiswa</th>
                        <th class="py-3.5 px-5">Program Studi & Angkatan</th>
                        <th class="py-3.5 px-5">Mata Kuliah yang Diprogramkan</th>
                        <th class="py-3.5 px-5 w-44 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($students as $student)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-5">
                                <span class="font-mono font-bold text-slate-900 bg-slate-100 px-2 py-1 rounded-md text-xs">
                                    {{ $student->nim ?: '-' }}
                                </span>
                            </td>
                            <td class="py-4 px-5">
                                <div class="font-bold text-slate-900 text-sm sm:text-base">{{ $student->name }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $student->email }}</div>
                            </td>
                            <td class="py-4 px-5">
                                @if ($student->studyProgram)
                                    <div class="font-semibold text-slate-800 text-xs sm:text-sm">
                                        {{ $student->studyProgram->name }} ({{ $student->studyProgram->level }})
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        {{ $student->studyProgram->faculty?->code }}
                                        @if ($student->cohort_year)
                                            &middot; Angkatan {{ $student->cohort_year }}
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs">Belum diatur</span>
                                    @if ($student->cohort_year)
                                        <span class="block text-xs text-slate-500 mt-0.5">Angkatan {{ $student->cohort_year }}</span>
                                    @endif
                                @endif
                            </td>
                            <td class="py-4 px-5">
                                @if ($student->enrolledCourses->isEmpty())
                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500">
                                        Belum di-assign ke MK
                                    </span>
                                @else
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($student->enrolledCourses as $enrolledCourse)
                                            <a href="{{ route('courses.show', ['course' => $enrolledCourse, 'tab' => 'students']) }}"
                                                class="inline-flex items-center gap-1 rounded-lg border border-teal-200 bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-900 hover:bg-teal-100 transition shadow-2xs">
                                                <span>{{ $enrolledCourse->code }}</span>
                                                <span class="text-teal-700">({{ $enrolledCourse->class_group }})</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('students.edit', $student) }}"
                                        class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition shadow-2xs">
                                        Edit & Plotting MK
                                    </a>
                                    <form method="post" action="{{ route('students.destroy', $student) }}"
                                        onsubmit="return confirm('Hapus mahasiswa {{ $student->name }} dari direktori?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-xl border border-red-200 px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 transition" title="Hapus mahasiswa">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-sm text-slate-500">
                                Belum ada data mahasiswa yang sesuai dengan pencarian. Gunakan tombol <strong>Tambah Mahasiswa</strong> atau <strong>Paste Kolektif</strong> di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($students->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
