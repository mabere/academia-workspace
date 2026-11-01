@extends('layouts.hub')
@section('title', 'Edit & Plotting Mata Kuliah Mahasiswa — Academic Hub')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <a href="{{ route('students.index') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-teal-800 hover:underline">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Direktori Mahasiswa
        </a>
        <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Edit Data & Plotting Mata Kuliah Mahasiswa</h1>
        <p class="mt-1 text-sm text-slate-600">
            Perbarui identitas akademik <strong>{{ $student->name }}</strong> serta kelola daftar mata kuliah yang diprogramkannya.
        </p>
    </div>

    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('students.update', $student) }}" class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-sm space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="sync_courses" value="1">

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-slate-700">NIM Mahasiswa *</label>
                <input type="text" name="nim" required maxlength="32" value="{{ old('nim', $student->nim) }}"
                    class="mt-1.5 w-full font-mono font-bold">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs sm:text-sm font-semibold text-slate-700">Nama Lengkap Mahasiswa *</label>
                <input type="text" name="name" required maxlength="150" value="{{ old('name', $student->name) }}"
                    class="mt-1.5 w-full">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-slate-700">Email Login *</label>
                <input type="email" name="email" required maxlength="150" value="{{ old('email', $student->email) }}"
                    class="mt-1.5 w-full">
            </div>
            <div>
                <div class="flex items-center justify-between">
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700">Program Studi</label>
                    @if(auth()->user()->isSuperadmin())
                        <a href="{{ route('academic-structure.index') }}" class="text-xs font-semibold text-teal-700 hover:underline">+ Kelola Prodi</a>
                    @endif
                </div>
                <select name="study_program_id" class="mt-1.5 w-full">
                    <option value="">-- Pilih Program Studi --</option>
                    @foreach ($studyPrograms as $prodi)
                        <option value="{{ $prodi->id }}" @selected(old('study_program_id', $student->study_program_id) == $prodi->id)>
                            {{ $prodi->name }} ({{ $prodi->level }}) — {{ $prodi->faculty?->code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-slate-700">Tahun Angkatan</label>
                <input type="number" name="cohort_year" min="2000" max="2099" value="{{ old('cohort_year', $student->cohort_year) }}"
                    class="mt-1.5 w-full">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 border-t border-slate-100 pt-5">
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-slate-700">Reset Kata Sandi (Opsional)</label>
                <input type="password" name="password" minlength="6" placeholder="Kosongkan jika tidak mengubah kata sandi"
                    class="mt-1.5 w-full">
            </div>
            <div class="flex items-center pt-6">
                <label class="inline-flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $student->isActive()))
                        class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                    <span>Akun Mahasiswa Aktif (Dapat mengakses kelas & mengumpulkan tugas)</span>
                </label>
            </div>
        </div>

        {{-- Plotting Mata Kuliah --}}
        @php
            $enrolledCourseIds = $student->enrolledCourses->pluck('id')->all();
        @endphp
        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 sm:p-6 space-y-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Plotting Mata Kuliah yang Diprogramkan</h2>
                <p class="text-xs sm:text-sm text-slate-500">Centang mata kuliah yang diikuti oleh mahasiswa ini. Nilai dan kehadiran pada kelas yang tetap dicentang akan terjaga.</p>
            </div>

            @if ($courses->isEmpty())
                <p class="text-sm text-slate-500">Belum ada mata kuliah tersedia.</p>
            @else
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($courses as $course)
                        @php
                            $isEnrolled = in_array($course->id, old('course_ids', $enrolledCourseIds));
                        @endphp
                        <label class="flex items-start gap-3.5 rounded-2xl border {{ $isEnrolled ? 'border-teal-500 bg-teal-50/60 ring-1 ring-teal-500/20' : 'border-slate-200 bg-white hover:border-slate-300' }} p-4 cursor-pointer transition shadow-2xs">
                            <input type="checkbox" name="course_ids[]" value="{{ $course->id }}" @checked($isEnrolled)
                                class="mt-1 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-bold text-slate-900">{{ $course->code }} — {{ $course->name }}</div>
                                <div class="mt-1 text-xs text-slate-500 flex flex-wrap items-center gap-1.5">
                                    <span class="font-medium text-slate-700">{{ $course->class_group }}</span>
                                    <span>&middot;</span>
                                    <span>{{ $course->credit_hours }} SKS</span>
                                    <span>&middot;</span>
                                    <span>{{ $course->academicPeriod?->name }}</span>
                                </div>
                                @if ($course->studyProgram)
                                    <div class="mt-1.5 inline-block text-[11px] font-semibold text-teal-800 bg-teal-100/60 px-2 py-0.5 rounded-md">
                                        Prodi: {{ $course->studyProgram->name }} ({{ $course->studyProgram->level }})
                                    </div>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
            <a href="{{ route('students.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Batal
            </a>
            <button type="submit" class="rounded-xl bg-teal-800 px-6 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:bg-teal-900 transition">
                Simpan Perubahan & Plotting MK
            </button>
        </div>
    </form>
</div>
@endsection
