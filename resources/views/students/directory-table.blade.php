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
