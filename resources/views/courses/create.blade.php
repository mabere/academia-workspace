@extends('layouts.hub')
@section('title', 'Tambah Mata Kuliah Baru — Academic Hub')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <a href="{{ route('courses.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">&larr; Kembali ke daftar mata kuliah</a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Tambah Mata Kuliah Baru</h1>
        <p class="text-xs text-slate-500">Mata kuliah yang Anda ampu pada semester berjalan akan otomatis dihitung ke beban SKS BKD Pengajaran.</p>
    </div>

    <form method="post" action="{{ route('courses.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
        @csrf

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-slate-700">Periode Semester *</label>
                    @if(auth()->user()->isSuperadmin())
                        <a href="{{ route('academic-structure.index') }}" class="text-[11px] font-semibold text-teal-700 hover:underline">+ Kelola Periode</a>
                    @endif
                </div>
                <select name="academic_period_id" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                    @foreach($academicPeriods as $period)
                        <option value="{{ $period->id }}" @selected($period->is_active)>
                            {{ $period->name }} {{ $period->is_active ? '· (Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-slate-700">Program Studi *</label>
                    @if(auth()->user()->isSuperadmin())
                        <a href="{{ route('academic-structure.index') }}" class="text-[11px] font-semibold text-teal-700 hover:underline">+ Kelola Fakultas & Prodi</a>
                    @endif
                </div>
                <select name="study_program_id" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                    @foreach($studyPrograms as $prodi)
                        <option value="{{ $prodi->id }}">{{ $prodi->name }} ({{ $prodi->level }}) — {{ $prodi->faculty?->code }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700">Kode Mata Kuliah *</label>
                <input type="text" name="code" required placeholder="Contoh: ENG201" value="{{ old('code') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700">Nama Mata Kuliah *</label>
                <input type="text" name="name" required placeholder="Contoh: Academic Writing" value="{{ old('name') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700">Bobot SKS *</label>
                <input type="number" name="credit_hours" required min="1" max="8" value="{{ old('credit_hours', 2) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Kelas / Kelompok *</label>
                <input type="text" name="class_group" required placeholder="Contoh: Kelas A" value="{{ old('class_group', 'Kelas A') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Tingkat Semester</label>
                <input type="number" name="semester_level" min="1" max="8" value="{{ old('semester_level', 1) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700">Hari Kuliah</label>
                <input type="text" name="schedule_day" placeholder="Contoh: Senin" value="{{ old('schedule_day') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Jam Perkuliahan</label>
                <input type="text" name="schedule_time" placeholder="Contoh: 08:00 - 10:30 WIB" value="{{ old('schedule_time') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Ruangan / Tempat</label>
                <input type="text" name="room" placeholder="Contoh: Gedung B R.302" value="{{ old('room') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700">Deskripsi Ringkas Mata Kuliah</label>
            <textarea name="description" rows="3" placeholder="Uraian garis besar mata kuliah..." class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700">Capaian Pembelajaran (CPMK / Silabus)</label>
            <textarea name="syllabus" rows="3" placeholder="Capaian pembelajaran lulusan mata kuliah..." class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">{{ old('syllabus') }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
            <a href="{{ route('courses.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
            <button type="submit" class="rounded-xl bg-teal-800 px-5 py-2 text-xs font-bold text-white hover:bg-teal-700">Simpan Mata Kuliah</button>
        </div>
    </form>
</div>
@endsection
