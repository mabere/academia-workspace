@extends('layouts.hub')
@section('title', 'Edit & Plotting Mata Kuliah Mahasiswa — Academic Hub')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <a href="{{ route('students.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
            &larr; Kembali ke Direktori Mahasiswa
        </a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Edit Data & Plotting Mata Kuliah Mahasiswa</h1>
        <p class="text-xs text-slate-500">
            Perbarui identitas akademik <strong>{{ $student->name }}</strong> serta kelola daftar mata kuliah yang diprogramkannya.
        </p>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-xs text-red-800">
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('students.update', $student) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="sync_courses" value="1">

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700">NIM Mahasiswa *</label>
                <input type="text" name="nim" required maxlength="32" value="{{ old('nim', $student->nim) }}"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm font-mono font-bold">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700">Nama Lengkap Mahasiswa *</label>
                <input type="text" name="name" required maxlength="150" value="{{ old('name', $student->name) }}"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700">Email Login *</label>
                <input type="email" name="email" required maxlength="150" value="{{ old('email', $student->email) }}"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-slate-700">Program Studi</label>
                    @if(auth()->user()->isSuperadmin())
                        <a href="{{ route('academic-structure.index') }}" class="text-[11px] font-semibold text-teal-700 hover:underline">+ Kelola Prodi</a>
                    @endif
                </div>
                <select name="study_program_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                    <option value="">-- Pilih Program Studi --</option>
                    @foreach ($studyPrograms as $prodi)
                        <option value="{{ $prodi->id }}" @selected(old('study_program_id', $student->study_program_id) == $prodi->id)>
                            {{ $prodi->name }} ({{ $prodi->level }}) — {{ $prodi->faculty?->code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700">Tahun Angkatan</label>
                <input type="number" name="cohort_year" min="2000" max="2099" value="{{ old('cohort_year', $student->cohort_year) }}"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 border-t border-slate-100 pt-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700">Reset Kata Sandi (Opsional)</label>
                <input type="password" name="password" minlength="6" placeholder="Kosongkan jika tidak mengubah kata sandi"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div class="flex items-center pt-5">
                <label class="inline-flex items-center gap-2.5 text-xs font-semibold text-slate-700 cursor-pointer">
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
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 space-y-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Plotting Mata Kuliah yang Diprogramkan</h2>
                <p class="text-xs text-slate-500">Centang mata kuliah yang diikuti oleh mahasiswa ini. Nilai dan kehadiran pada kelas yang tetap dicentang akan terjaga.</p>
            </div>

            @if ($courses->isEmpty())
                <p class="text-xs text-slate-500">Belum ada mata kuliah tersedia.</p>
            @else
                <div class="grid gap-2.5 sm:grid-cols-2">
                    @foreach ($courses as $course)
                        @php
                            $isEnrolled = in_array($course->id, old('course_ids', $enrolledCourseIds));
                        @endphp
                        <label class="flex items-start gap-3 rounded-xl border {{ $isEnrolled ? 'border-teal-500 bg-teal-50/50' : 'border-slate-200 bg-white' }} p-3 text-xs cursor-pointer hover:border-teal-400 transition">
                            <input type="checkbox" name="course_ids[]" value="{{ $course->id }}" @checked($isEnrolled)
                                class="mt-0.5 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-slate-900">{{ $course->code }} — {{ $course->name }}</div>
                                <div class="mt-0.5 text-[11px] text-slate-500">
                                    {{ $course->class_group }} &middot; {{ $course->credit_hours }} SKS &middot; {{ $course->academicPeriod?->name }}
                                </div>
                                @if ($course->studyProgram)
                                    <div class="text-[10px] font-semibold text-teal-700">
                                        Prodi: {{ $course->studyProgram->name }} ({{ $course->studyProgram->level }})
                                    </div>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
            <a href="{{ route('students.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                Batal
            </a>
            <button type="submit" class="rounded-xl bg-teal-800 px-5 py-2.5 text-xs font-bold text-white hover:bg-teal-900 transition">
                Simpan Perubahan & Plotting MK
            </button>
        </div>
    </form>
</div>
@endsection
