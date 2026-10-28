@extends('layouts.hub')
@section('title', (auth()->user()->isStaff() ? 'Mata Kuliah & Perkuliahan' : 'Mata Kuliah Saya') . ' — Academia Workspace')

@section('content')
<div x-data="{ view: localStorage.getItem('course_view') || 'grid' }" x-init="$watch('view', val => localStorage.setItem('course_view', val))">
    {{-- Header Halaman --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            @if(auth()->user()->isStaff())
                <p class="text-xs font-bold uppercase tracking-wider text-teal-800">Pilar Pendidikan & Pengajaran (Tri Dharma)</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Mata Kuliah & Perkuliahan</h1>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-600 max-w-2xl leading-relaxed">
                    Akses bahan ajar multi-format (audio, video, PDF, dokumen), jurnal 16 pertemuan, evaluasi tugas mahasiswa, dan pemenuhan SKS BKD.
                </p>
            @else
                <p class="text-xs font-bold uppercase tracking-wider text-teal-800">Portal Pembelajaran Mahasiswa</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Mata Kuliah Saya</h1>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-600 max-w-2xl leading-relaxed">
                    Daftar mata kuliah yang Anda ikuti pada semester ini. Akses materi perkuliahan, pantau topik pertemuan, dan kumpulkan tugas akademik Anda.
                </p>
            @endif
        </div>
        @if(auth()->user()->isStaff())
            <a href="{{ route('courses.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Mata Kuliah
            </a>
        @endif
    </div>

    {{-- Banner Filter Periode & Ringkasan SKS --}}
    <section class="mt-6 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-4">
            {{-- Filter Semester --}}
            <form method="get" action="{{ route('courses.index') }}" class="flex items-center gap-2">
                <label for="period-select" class="text-xs font-bold uppercase tracking-wider text-slate-500">Semester:</label>
                <div class="relative">
                    <select id="period-select" name="academic_period_id" onchange="this.form.submit()" class="rounded-lg border-slate-300 py-1.5 pl-3 pr-8 text-xs font-semibold text-slate-700 focus:border-teal-700 focus:ring-teal-700 bg-slate-50/50">
                        @foreach($academicPeriods as $period)
                            <option value="{{ $period->id }}" @selected($selectedPeriodId === $period->id)>
                                {{ $period->name }} {{ $period->is_active ? '· (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            {{-- Ringkasan SKS & Toggle Tampilan --}}
            <div class="flex flex-wrap items-center gap-4 sm:gap-6 text-xs">
                @if(auth()->user()->isStaff())
                    <div class="flex items-center gap-4">
                        <div>
                            <span class="text-slate-500">Total MK:</span>
                            <span class="ml-1 font-bold text-slate-800">{{ $courses->count() }} Kelas</span>
                        </div>
                        <div class="h-3.5 w-px bg-slate-200"></div>
                        <div>
                            <span class="text-slate-500">Beban Pengajaran:</span>
                            <span class="ml-1 font-extrabold text-teal-800 text-sm">{{ $totalSks }} SKS</span>
                        </div>
                        <div class="h-3.5 w-px bg-slate-200"></div>
                        <div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $totalSks >= 3 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                {{ $totalSks >= 3 ? '✓ MEMENUHI (≥ 3.0 SKS)' : 'KURANG DARI 3.0 SKS' }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-4">
                        <div>
                            <span class="text-slate-500">Mata Kuliah Diikuti:</span>
                            <span class="ml-1 font-bold text-slate-800">{{ $courses->count() }} Kelas</span>
                        </div>
                        <div class="h-3.5 w-px bg-slate-200"></div>
                        <div>
                            <span class="text-slate-500">Total SKS Diambil:</span>
                            <span class="ml-1 font-extrabold text-teal-800 text-sm">{{ $totalSks }} SKS</span>
                        </div>
                    </div>
                @endif

                {{-- Tombol Toggle Kartu vs Tabel --}}
                <div class="flex items-center rounded-lg border border-slate-200 p-0.5 bg-slate-100/80">
                    <button type="button" @click="view = 'grid'" :class="view === 'grid' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900'" class="flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-semibold transition" title="Tampilan Kartu">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span>Kartu</span>
                    </button>
                    <button type="button" @click="view = 'table'" :class="view === 'table' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900'" class="flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-semibold transition" title="Tampilan Tabel">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        <span>Tabel</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- State Jika Belum Ada Mata Kuliah --}}
    @if($courses->isEmpty())
        <section class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-teal-50 text-teal-800">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
            @if(auth()->user()->isStaff())
                <h2 class="mt-4 text-lg font-bold text-slate-800">Belum Ada Mata Kuliah pada Periode Ini</h2>
                <p class="mt-1 text-sm text-slate-500">Silakan tambahkan mata kuliah yang Anda ampu atau pilih periode semester lain.</p>
                <a href="{{ route('courses.create') }}" class="mt-5 inline-flex rounded-xl bg-teal-800 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                    Tambah Mata Kuliah Pertama
                </a>
            @else
                <h2 class="mt-4 text-lg font-bold text-slate-800">Belum Terdaftar pada Mata Kuliah</h2>
                <p class="mt-1 text-sm text-slate-500 max-w-md mx-auto">
                    Anda belum terdaftar pada mata kuliah di periode semester ini. Silakan hubungi dosen pengampu mata kuliah terkait untuk memasukkan akun Anda ke dalam kelas.
                </p>
            @endif
        </section>
    @else
        {{-- TAMPILAN 1: GRID KARTU --}}
        <div x-show="view === 'grid'" class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($courses as $course)
                <div class="group flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs hover:border-teal-600 hover:shadow-md transition-all duration-200">
                    <div>
                        {{-- Header Kartu: Kode & Kelas + SKS Badge --}}
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center rounded-lg bg-teal-50 px-2.5 py-1 text-xs font-bold text-teal-900 border border-teal-200/60">
                                {{ $course->code }} · {{ $course->class_group }}
                            </span>
                            <span class="inline-flex items-center rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700">
                                {{ $course->credit_hours }} SKS
                            </span>
                        </div>

                        {{-- Nama Mata Kuliah --}}
                        <h2 class="mt-3 text-base font-bold text-slate-900 group-hover:text-teal-800 transition line-clamp-1">
                            <a href="{{ route('courses.show', $course) }}" class="focus:outline-none" title="{{ $course->name }}">
                                {{ $course->name }}
                            </a>
                        </h2>

                        {{-- Dosen Pengampu & Prodi --}}
                        <p class="mt-1 text-xs font-medium text-slate-600">
                            Dosen: <strong class="text-slate-800">{{ $course->lecturer->name }}</strong>
                        </p>
                        <p class="text-[11px] text-slate-400">
                            {{ $course->studyProgram->name }} ({{ $course->studyProgram->level }}) · Smt {{ $course->semester_level }}
                        </p>

                        {{-- Jadwal & Ruang --}}
                        <div class="mt-2.5 flex items-center gap-1.5 text-xs text-slate-600">
                            <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="truncate">{{ $course->scheduleLabel() }} ({{ $course->room ?: 'Daring' }})</span>
                        </div>

                        {{-- Progress Jurnal Sesi (16 Pertemuan) --}}
                        <div class="mt-3.5 pt-3 border-t border-slate-100">
                            <div class="flex items-center justify-between text-[11px] mb-1.5">
                                <span class="text-slate-500 font-medium">Sesi Perkuliahan</span>
                                <span class="font-bold text-slate-700">{{ $course->sessions_count }}/16 Sesi ({{ round(($course->sessions_count / 16) * 100) }}%)</span>
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-teal-600 transition-all" style="width: {{ min(100, round(($course->sessions_count / 16) * 100)) }}%"></div>
                            </div>
                        </div>

                        {{-- Metrik Bahan Ajar & Mahasiswa --}}
                        <div class="mt-2.5 flex items-center justify-between text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <strong class="text-slate-700">{{ $course->materials_count }}</strong> Bahan Ajar
                            </span>
                            @if(auth()->user()->isStaff())
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <strong class="text-slate-700">{{ $course->students_count }}</strong> Mahasiswa
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-teal-800 font-semibold">
                                    <span>{{ $course->assignments_count }}</span> Tugas Aktif
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Footer Card: Tombol Buka Kelas --}}
                    <div class="mt-4 border-t border-slate-100 pt-3 flex items-center justify-between text-xs">
                        <span class="text-[11px] text-slate-400 truncate max-w-[140px]">
                            {{ $course->academicPeriod?->name }}
                        </span>
                        <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center gap-1 font-bold text-teal-800 hover:text-teal-900 group-hover:translate-x-0.5 transition">
                            {{ auth()->user()->isStaff() ? 'Buka Kelas' : 'Masuk Kelas' }} &rarr;
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- TAMPILAN 2: TABEL RINGKAS --}}
        <div x-show="view === 'table'" class="mt-6 rounded-2xl border border-slate-200/90 bg-white overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="border-b border-slate-200 bg-slate-50 font-bold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4 w-36">Kode & Kelas</th>
                            <th class="py-3 px-4">Mata Kuliah & Dosen Pengampu</th>
                            <th class="py-3 px-4 w-20 text-center">Bobot</th>
                            <th class="py-3 px-4 w-44">Jadwal & Ruang</th>
                            <th class="py-3 px-4 w-36">Sesi Berjalan</th>
                            <th class="py-3 px-4 w-32 text-center">{{ auth()->user()->isStaff() ? 'Bahan & Mhs' : 'Bahan & Tugas' }}</th>
                            <th class="py-3 px-4 w-28 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($courses as $idx => $course)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex rounded-md bg-teal-50 px-2.5 py-1 font-mono font-bold text-teal-900 border border-teal-200/60">
                                        {{ $course->code }} · {{ $course->class_group }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('courses.show', $course) }}" class="font-bold text-slate-900 hover:text-teal-800 transition block text-sm">
                                        {{ $course->name }}
                                    </a>
                                    <span class="text-slate-500 text-[11px]">
                                        Dosen: <strong class="text-slate-700">{{ $course->lecturer->name }}</strong> &middot; {{ $course->studyProgram->name }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 font-bold text-slate-700">
                                        {{ $course->credit_hours }} SKS
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    <div class="font-medium">{{ $course->scheduleLabel() }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $course->room ?: 'Daring' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center justify-between text-[11px] mb-1">
                                        <span class="font-bold text-slate-700">{{ $course->sessions_count }}/16</span>
                                        <span class="text-slate-400">{{ round(($course->sessions_count / 16) * 100) }}%</span>
                                    </div>
                                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-teal-600" style="width: {{ min(100, round(($course->sessions_count / 16) * 100)) }}%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="font-semibold text-slate-700">{{ $course->materials_count }} materi</span>
                                    <span class="text-slate-400">·</span>
                                    @if(auth()->user()->isStaff())
                                        <span class="font-semibold text-slate-700">{{ $course->students_count }} mhs</span>
                                    @else
                                        <span class="font-semibold text-teal-800">{{ $course->assignments_count }} tugas</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <a href="{{ route('courses.show', $course) }}" class="rounded-lg bg-teal-800 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-700 transition">
                                        {{ auth()->user()->isStaff() ? 'Buka Kelas' : 'Masuk Kelas' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection