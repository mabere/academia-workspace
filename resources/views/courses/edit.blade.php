@extends('layouts.hub')
@section('title', 'Edit Mata Kuliah ' . $course->name . ' — Academic Hub')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <a href="{{ route('courses.show', $course) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">&larr; Kembali ke detail mata kuliah</a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Edit Informasi Mata Kuliah</h1>
    </div>

    <form method="post" action="{{ route('courses.update', $course) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
        @csrf
        @method('PUT')

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-xs font-semibold text-slate-700">Periode Semester *</label>
                <select name="academic_period_id" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                    @foreach($academicPeriods as $period)
                        <option value="{{ $period->id }}" @selected(old('academic_period_id', $course->academic_period_id) === $period->id)>
                            {{ $period->name }} {{ $period->is_active ? '· (Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Program Studi *</label>
                <select name="study_program_id" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                    @foreach($studyPrograms as $prodi)
                        <option value="{{ $prodi->id }}" @selected(old('study_program_id', $course->study_program_id) === $prodi->id)>
                            {{ $prodi->name }} ({{ $prodi->level }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700">Kode Mata Kuliah *</label>
                <input type="text" name="code" required value="{{ old('code', $course->code) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700">Nama Mata Kuliah *</label>
                <input type="text" name="name" required value="{{ old('name', $course->name) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700">Bobot SKS *</label>
                <input type="number" name="credit_hours" required min="1" max="8" value="{{ old('credit_hours', $course->credit_hours) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Kelas / Kelompok *</label>
                <input type="text" name="class_group" required value="{{ old('class_group', $course->class_group) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Tingkat Semester</label>
                <input type="number" name="semester_level" min="1" max="8" value="{{ old('semester_level', $course->semester_level) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700">Hari Kuliah</label>
                <input type="text" name="schedule_day" value="{{ old('schedule_day', $course->schedule_day) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Jam Perkuliahan</label>
                <input type="text" name="schedule_time" value="{{ old('schedule_time', $course->schedule_time) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Ruangan / Tempat</label>
                <input type="text" name="room" value="{{ old('room', $course->room) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700">Deskripsi Ringkas Mata Kuliah</label>
            <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">{{ old('description', $course->description) }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700">Capaian Pembelajaran (CPMK / Silabus)</label>
            <textarea name="syllabus" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">{{ old('syllabus', $course->syllabus) }}</textarea>
        </div>

        <div class="flex items-center justify-between border-t border-slate-100 pt-4">
            <button type="button" onclick="if(confirm('Apakah Anda yakin ingin menghapus mata kuliah ini?')) document.getElementById('delete-course-form').submit();" class="text-xs font-bold text-red-600 hover:text-red-700">
                Hapus Mata Kuliah
            </button>

            <div class="flex items-center gap-3">
                <a href="{{ route('courses.show', $course) }}" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
                <button type="submit" class="rounded-xl bg-teal-800 px-5 py-2 text-xs font-bold text-white hover:bg-teal-700">Simpan Perubahan</button>
            </div>
        </div>
    </form>

    <form id="delete-course-form" method="post" action="{{ route('courses.destroy', $course) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
