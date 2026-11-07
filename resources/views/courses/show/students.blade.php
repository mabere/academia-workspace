        {{-- TAB 4: PESERTA KELAS (MAHASISWA) --}}
        {{-- ======================================================== --}}
        <div x-show="tab === 'students'" x-cloak class="space-y-4">
            @php
                $canManageStudents = auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true));
                $myEnrollment = $students->firstWhere('student_id', auth()->id());
            @endphp

            {{-- Rekap Capaian Akademik Mahasiswa Pribadi --}}
            @if(!auth()->user()->isStaff() && $myEnrollment)
                <div class="rounded-3xl border border-teal-200 bg-gradient-to-r from-teal-50/80 via-white to-emerald-50/70 p-6 shadow-xs">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-extrabold uppercase tracking-wider text-teal-800">Rekap Capaian Akademik Saya</span>
                            <h3 class="text-base font-extrabold text-slate-900 mt-0.5">{{ auth()->user()->name }} ({{ auth()->user()->nim ?: $myEnrollment->nim ?: '-' }})</h3>
                            <p class="text-xs text-slate-500">Nilai akhir dan kehadiran dikalkulasi secara otomatis dari jurnal perkuliahan dan tugas kelas.</p>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="text-center">
                                <span class="block text-[10px] font-bold uppercase text-slate-500">Presensi</span>
                                <span class="text-xl font-black text-slate-900">{{ number_format($myEnrollment->attendance_rate, 0) }}%</span>
                            </div>
                            <div class="text-center border-l border-teal-200 pl-6">
                                <span class="block text-[10px] font-bold uppercase text-slate-500">Nilai Akhir</span>
                                <span class="text-xl font-black text-teal-800">{{ $myEnrollment->final_grade ? number_format($myEnrollment->final_grade, 1) : '-' }}</span>
                            </div>
                            <div class="text-center border-l border-teal-200 pl-6">
                                <span class="block text-[10px] font-bold uppercase text-slate-500">Huruf Mutu</span>
                                <span class="inline-flex rounded-full bg-teal-100 px-3.5 py-1 text-sm font-black text-teal-950">
                                    {{ $myEnrollment->letter_grade ?: 'Belum Ada' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Form Penambahan / Assign Mahasiswa ke Kelas (Khusus Dosen / Staf) --}}
            @if($canManageStudents)
                <div x-show="enrollOpen" x-cloak class="rounded-3xl border border-teal-200 bg-white p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-sm font-extrabold text-slate-900">Pendaftaran Mahasiswa ke Kelas</h3>
                        <button type="button" @click="enrollOpen = false" class="text-xs font-bold text-slate-400 hover:text-slate-700">&times; Tutup</button>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="enrollMode = 'directory'"
                                :class="enrollMode === 'directory' ? 'bg-teal-800 text-white' : 'bg-slate-100 text-slate-700 border border-slate-200'"
                                class="rounded-xl px-3.5 py-1.5 text-xs font-bold transition">
                                Direktori Mahasiswa ({{ ($availableStudents ?? collect())->count() }})
                            </button>
                            <button type="button" @click="enrollMode = 'paste'"
                                :class="enrollMode === 'paste' ? 'bg-teal-800 text-white' : 'bg-slate-100 text-slate-700 border border-slate-200'"
                                class="rounded-xl px-3.5 py-1.5 text-xs font-bold transition">
                                + Input / Paste Cepat Absen
                            </button>
                            <a href="{{ route('students.index', ['course_id' => $course->id]) }}"
                                class="rounded-xl border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                                Direktori Lengkap &rarr;
                            </a>
                        </div>

                        <form method="post" action="{{ route('courses.students.enroll', $course) }}" class="space-y-4">
                            @csrf
                            {{-- Mode 1: Direktori Checklist --}}
                            <div x-show="enrollMode === 'directory'" class="space-y-3">
                                @if(($availableStudents ?? collect())->isEmpty())
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center text-xs text-slate-500">
                                        Seluruh mahasiswa di direktori sudah terdaftar di kelas ini. Gunakan tab <strong>+ Input / Paste Cepat Absen</strong> di samping untuk mahasiswa baru.
                                    </div>
                                @else
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-slate-700">Pilih mahasiswa yang memprogram mata kuliah ini:</span>
                                        <button type="button"
                                            onclick="document.querySelectorAll('.dir-student-checkbox').forEach(cb => cb.checked = true)"
                                            class="font-bold text-teal-800 hover:underline">
                                            Pilih Semua
                                        </button>
                                    </div>
                                    <div class="max-h-60 overflow-y-auto grid gap-2 sm:grid-cols-2 lg:grid-cols-3 rounded-2xl border border-slate-200 bg-slate-50/50 p-3">
                                        @foreach($availableStudents as $avail)
                                            <label class="flex items-start gap-2.5 rounded-xl border border-slate-200 bg-white p-2.5 text-xs cursor-pointer hover:bg-teal-50/50 hover:border-teal-300 transition shadow-2xs">
                                                <input type="checkbox" name="student_ids[]" value="{{ $avail->id }}"
                                                    class="dir-student-checkbox mt-0.5 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                                                <span class="min-w-0">
                                                    <span class="block font-bold text-slate-900 truncate">{{ $avail->name }}</span>
                                                    <span class="block font-mono text-[11px] text-slate-500">
                                                        NIM: {{ $avail->nim ?: '-' }}
                                                        @if($avail->studyProgram)
                                                            &middot; {{ $avail->studyProgram->code }}
                                                        @endif
                                                        @if($avail->cohort_year)
                                                            ({{ $avail->cohort_year }})
                                                        @endif
                                                    </span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Mode 2: Paste Cepat --}}
                            <div x-show="enrollMode === 'paste'" x-cloak class="space-y-3">
                                <div class="grid gap-3 sm:grid-cols-4">
                                    <div class="sm:col-span-3">
                                        <label class="block text-xs font-bold text-slate-800">
                                            Daftar Mahasiswa Tambahan (<code class="rounded bg-slate-100 px-1.5 py-0.5 text-teal-900">NIM ; Nama Lengkap ; Email(opsional)</code>)
                                        </label>
                                        <textarea name="raw_list" rows="4"
                                            placeholder="240105010 ; Andi Saputra&#10;240105011 ; Nadia Putri ; nadia@student.ac.id"
                                            class="mt-1.5 w-full rounded-2xl border-slate-300 bg-white font-mono text-xs focus:border-teal-700 focus:ring-teal-700"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-800">Tahun Angkatan</label>
                                        <input type="number" name="cohort_year" min="2000" max="2099" value="{{ now()->year }}"
                                            class="mt-1.5 w-full rounded-xl border-slate-300 bg-white text-xs font-bold">
                                        <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                                            Mahasiswa otomatis terdaftar ke Prodi <strong>{{ $course->studyProgram->name }}</strong> dan di-assign ke tugas kelas ini.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="rounded-xl bg-teal-800 px-6 py-2.5 text-xs font-bold text-white shadow-2xs hover:bg-teal-700 transition">
                                    Assign Mahasiswa ke Kelas
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Realtime Search Bar & Tabel Peserta Kelas --}}
            <div class="rounded-3xl border border-slate-200/90 bg-white overflow-hidden shadow-xs">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-800">
                            {{ auth()->user()->isStaff() ? 'Daftar Mahasiswa Terdaftar' : 'Teman Sekelas' }} ({{ $students->count() }} Peserta)
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if($canManageStudents)
                            <button type="button" @click="enrollOpen = !enrollOpen"
                                :class="enrollOpen ? 'bg-slate-800 text-white' : 'bg-teal-800 text-white hover:bg-teal-700'"
                                class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs font-bold shadow-2xs transition">
                                <span x-text="enrollOpen ? 'Tutup Form' : '+ Tambah Mahasiswa'"></span>
                            </button>
                        @endif

                        {{-- Realtime Search Input untuk Mahasiswa --}}
                        <div class="relative w-44 sm:w-56">
                            <input type="text" x-model="studentSearch" placeholder="Cari Nama / NIM..."
                                class="w-full rounded-xl border-slate-300 pl-8 pr-3 py-1.5 text-xs focus:border-teal-700 focus:ring-teal-700 bg-white">
                            <svg class="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        @if(auth()->user()->isStaff())
                            <span class="text-slate-300 hidden sm:inline">·</span>
                            <a href="{{ route('courses.grades.export', $course) }}" class="inline-flex items-center gap-1 text-xs font-bold text-emerald-800 hover:text-emerald-950 px-2 py-1 transition">
                                <span>Ekspor</span>
                            </a>
                            <span class="text-slate-300 hidden sm:inline">·</span>
                            <a href="{{ route('courses.grades.print', $course) }}" target="_blank" class="text-xs font-bold text-teal-800 hover:text-teal-950 px-2 py-1 transition">
                                Cetak Nilai &rarr;
                            </a>
                        @endif
                    </div>
                </div>

                @if($students->isEmpty())
                    <div class="p-12 text-center text-sm text-slate-500">Belum ada mahasiswa yang terdaftar di kelas ini.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="border-b border-slate-200 bg-slate-50/70 font-bold uppercase tracking-wider text-slate-600">
                                <tr>
                                    <th class="py-3.5 px-4 w-14 text-center">No</th>
                                    <th class="py-3.5 px-4 w-36">NIM</th>
                                    <th class="py-3.5 px-4">Nama & Program Studi</th>
                                    @if(auth()->user()->isStaff())
                                        <th class="py-3.5 px-4 w-44 text-center">Kehadiran (%)</th>
                                        <th class="py-3.5 px-4 w-24 text-center">Nilai Akhir</th>
                                        <th class="py-3.5 px-4 w-20 text-center">Mutu</th>
                                        @if($canManageStudents)
                                            <th class="py-3.5 px-4 w-24 text-right">Aksi</th>
                                        @endif
                                    @else
                                        <th class="py-3.5 px-4 w-28 text-center">Status</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($students as $idx => $cs)
                                    <tr x-show="studentSearch === '' || '{{ strtolower(addslashes($cs->student->name)) }}'.includes(studentSearch.toLowerCase()) || '{{ strtolower(addslashes($cs->nim ?: $cs->student->nim ?: '')) }}'.includes(studentSearch.toLowerCase())"
                                        class="hover:bg-slate-50/80 transition">
                                        <td class="py-3.5 px-4 text-center text-slate-400 font-bold">{{ $idx + 1 }}</td>
                                        <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $cs->nim ?: ($cs->student->nim ?: '-') }}</td>
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-2.5">
                                                <div class="h-7 w-7 rounded-lg bg-teal-50 border border-teal-200 text-teal-800 flex items-center justify-center font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr($cs->student->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900">{{ $cs->student->name }}</div>
                                                    <div class="text-[11px] text-slate-500">
                                                        {{ $cs->student->email }}
                                                        @if($cs->student->studyProgram)
                                                            &middot; <span class="font-bold text-teal-800">{{ $cs->student->studyProgram->code }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        @if(auth()->user()->isStaff())
                                            <td class="py-3.5 px-4 text-center">
                                                @if($canManageStudents)
                                                    <form method="post" action="{{ route('courses.students.update', [$course, $cs]) }}" class="inline-flex items-center justify-center gap-1.5">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="nim" value="{{ $cs->nim ?: $cs->student->nim }}">
                                                        <input type="number" name="attendance_rate" min="0" max="100" step="1" value="{{ number_format($cs->attendance_rate, 0, '.', '') }}"
                                                            class="w-16 rounded-xl border-slate-300 py-1 px-2 text-center text-xs font-bold focus:border-teal-700 focus:ring-teal-700">
                                                        <button type="submit" class="rounded-xl bg-slate-800 px-2.5 py-1 text-xs font-bold text-white hover:bg-slate-700 transition" title="Simpan perubahan kehadiran">
                                                            OK
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="font-bold text-slate-800">{{ number_format($cs->attendance_rate, 0) }}%</span>
                                                @endif
                                            </td>
                                            <td class="py-3.5 px-4 text-center font-extrabold text-teal-800">{{ $cs->final_grade ? number_format($cs->final_grade, 1) : '-' }}</td>
                                            <td class="py-3.5 px-4 text-center">
                                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-0.5 text-xs font-bold text-slate-800">
                                                    {{ $cs->letter_grade ?: '-' }}
                                                </span>
                                            </td>
                                            @if($canManageStudents)
                                                <td class="py-3.5 px-4 text-right">
                                                    <form method="post" action="{{ route('courses.students.unenroll', [$course, $cs]) }}"
                                                        onsubmit="return confirm('Apakah Anda yakin ingin mengeluarkan {{ $cs->student->name }} dari kelas ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="rounded-xl border border-red-200 px-2.5 py-1 text-xs font-bold text-red-600 hover:bg-red-50 transition">
                                                            Keluarkan
                                                        </button>
                                                    </form>
                                                </td>
                                            @endif
                                        @else
                                            <td class="py-3.5 px-4 text-center">
                                                @if($cs->student_id === auth()->id())
                                                    <span class="inline-flex rounded-full bg-teal-100 px-3 py-0.5 text-xs font-black text-teal-950">
                                                        Anda
                                                    </span>
                                                @else
                                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs text-slate-500">
                                                        Mahasiswa
                                                    </span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

