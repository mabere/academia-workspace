@extends('layouts.hub')
@section('title', 'Mata Kuliah & Perkuliahan — Academic Hub')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-teal-800">Pilar Pendidikan & Pengajaran (Tri Dharma)</p>
        <h1 class="mt-1 text-3xl font-bold text-slate-900">Mata Kuliah & Perkuliahan</h1>
        <p class="mt-2 text-sm text-slate-600">
            Akses sumber belajar, bahan ajar multi-format (audio, video, PDF, dokumen), jurnal kuliah 16 pertemuan, dan penilaian tugas mahasiswa.
        </p>
    </div>
    @if(auth()->user()->isStaff())
        <a href="{{ route('courses.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Mata Kuliah
        </a>
    @endif
</div>

{{-- Banner KPI Beban Ajar & SKS Pengajaran --}}
@if(auth()->user()->isStaff())
<section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <form method="get" action="{{ route('courses.index') }}" class="flex items-center gap-3">
            <label for="period-select" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Periode Semester:</label>
            <select id="period-select" name="academic_period_id" onchange="this.form.submit()" class="rounded-lg border-slate-300 py-1.5 pl-3 pr-8 text-xs font-semibold text-slate-700 focus:border-teal-700 focus:ring-teal-700">
                @foreach($academicPeriods as $period)
                    <option value="{{ $period->id }}" @selected($selectedPeriodId === $period->id)>
                        {{ $period->name }} {{ $period->is_active ? '· (Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
        </form>

        <div class="flex flex-wrap items-center gap-6 text-sm">
            <div>
                <span class="text-xs text-slate-500">Total MK Diampu:</span>
                <span class="ml-1.5 font-bold text-slate-800">{{ $courses->count() }} Mata Kuliah</span>
            </div>
            <div class="h-4 w-px bg-slate-200"></div>
            <div>
                <span class="text-xs text-slate-500">Akumulasi SKS:</span>
                <span class="ml-1.5 font-extrabold text-teal-800">{{ $totalSks }} SKS</span>
            </div>
            <div class="h-4 w-px bg-slate-200"></div>
            <div>
                <span class="text-xs text-slate-500">Ambang Batas BKD:</span>
                <span class="ml-1.5 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold {{ $totalSks >= 3 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $totalSks >= 3 ? 'MEMENUHI (≥ 3.0 SKS)' : 'KURANG DARI 3.0 SKS' }}
                </span>
            </div>
        </div>
    </div>
</section>
@endif

@if($courses->isEmpty())
    <section class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-teal-50 text-teal-800">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
            </svg>
        </div>
        <h2 class="mt-4 text-lg font-bold text-slate-800">Belum Ada Mata Kuliah pada Periode Ini</h2>
        <p class="mt-1 text-sm text-slate-500">Silakan tambahkan mata kuliah yang Anda ampu atau pilih periode semester lain.</p>
        @if(auth()->user()->isStaff())
            <a href="{{ route('courses.create') }}" class="mt-5 inline-flex rounded-xl bg-teal-800 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                Tambah Mata Kuliah Pertama
            </a>
        @endif
    </section>
@else
    <div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        @foreach($courses as $course)
            <div class="group flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-teal-600 hover:shadow-md">
                <div>
                    {{-- Header Kartu --}}
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="inline-flex rounded-md bg-slate-100 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-slate-700">
                                {{ $course->code }} · {{ $course->class_group }}
                            </span>
                            <span class="ml-1 text-xs text-slate-400">Sem. {{ $course->semester_level }}</span>
                        </div>
                        <span class="shrink-0 rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-extrabold text-teal-800 border border-teal-200">
                            {{ $course->credit_hours }} SKS
                        </span>
                    </div>

                    {{-- Nama Mata Kuliah --}}
                    <h2 class="mt-3 text-lg font-bold text-slate-900 group-hover:text-teal-800 transition">
                        <a href="{{ route('courses.show', $course) }}" class="focus:outline-none">
                            {{ $course->name }}
                        </a>
                    </h2>

                    {{-- Prodi & Jadwal --}}
                    <p class="mt-1 text-xs font-medium text-slate-500">
                        {{ $course->studyProgram->name }} ({{ $course->studyProgram->level }})
                    </p>

                    <div class="mt-3 flex items-center gap-2 text-xs text-slate-600">
                        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="truncate">{{ $course->scheduleLabel() }}</span>
                    </div>

                    {{-- Deskripsi Singkat --}}
                    <p class="mt-3 text-xs leading-relaxed text-slate-600 line-clamp-2">
                        {{ $course->description ?: 'Belum ada deskripsi mata kuliah.' }}
                    </p>
                </div>

                {{-- Footer Kartu & Statistik --}}
                <div class="mt-5 border-t border-slate-100 pt-4">
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="rounded-lg bg-slate-50 py-1.5 px-2">
                            <span class="block font-bold text-slate-800">{{ $course->materials_count }}</span>
                            <span class="text-[10px] text-slate-500">Bahan Ajar</span>
                        </div>
                        <div class="rounded-lg bg-slate-50 py-1.5 px-2">
                            <span class="block font-bold text-slate-800">{{ $course->sessions_count }}/16</span>
                            <span class="text-[10px] text-slate-500">Jurnal Sesi</span>
                        </div>
                        <div class="rounded-lg bg-slate-50 py-1.5 px-2">
                            <span class="block font-bold text-slate-800">{{ $course->students_count }}</span>
                            <span class="text-[10px] text-slate-500">Mahasiswa</span>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-[11px] text-slate-400">
                            Pengampu: {{ $course->lecturer->name }}
                        </span>
                        <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center gap-1 text-xs font-bold text-teal-800 hover:text-teal-900 group-hover:translate-x-0.5 transition">
                            Buka Kelas &rarr;
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
