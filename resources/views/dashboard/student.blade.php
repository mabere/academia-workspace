    <div class="space-y-10">
        {{-- STATISTIK BELAJAR MAHASISWA (4 KPI CARDS) --}}
        <section aria-labelledby="student-kpi-heading">
            <h2 id="student-kpi-heading" class="sr-only">Statistik Aktivitas Pembelajaran</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-teal-200/80 bg-white p-5 shadow-2xs">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Mata Kuliah Diikuti</p>
                    <p class="mt-2 text-3xl font-extrabold text-[#05285c]">{{ $dashboardStats['courses_count'] }}</p>
                    <p class="mt-1 text-xs text-teal-800 font-semibold">Kelas aktif semester berjalan</p>
                </div>
                <div class="rounded-2xl border border-blue-200/80 bg-white p-5 shadow-2xs">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Beban SKS</p>
                    <p class="mt-2 text-3xl font-extrabold text-blue-900">{{ $dashboardStats['total_sks'] }} SKS</p>
                    <p class="mt-1 text-xs text-slate-500">Beban kredit perkuliahan</p>
                </div>
                <div class="rounded-2xl border border-amber-200/80 bg-white p-5 shadow-2xs">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tugas Akademik</p>
                    <p class="mt-2 text-3xl font-extrabold text-amber-900">{{ $dashboardStats['assignments'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">Tugas tersedia di WriteWise</p>
                </div>
                <div class="rounded-2xl border border-emerald-200/80 bg-white p-5 shadow-2xs">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tugas Dikumpulkan</p>
                    <p class="mt-2 text-3xl font-extrabold text-emerald-900">{{ $dashboardStats['submissions'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">Riwayat draf &amp; submisi</p>
                </div>
            </div>
        </section>

        {{-- MATA KULIAH SAYA SEMESTER INI --}}
        <section aria-labelledby="student-courses-heading">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="student-courses-heading" class="text-xl font-bold text-slate-900">Mata Kuliah Semester Ini</h2>
                    <p class="mt-1 text-sm text-slate-600">Akses langsung ruang kelas, bahan ajar, dan jadwal pertemuan Anda.</p>
                </div>
                <a href="{{ route('courses.index') }}" class="text-sm font-bold text-teal-800 hover:text-teal-950 transition">
                    Lihat Semua Mata Kuliah &rarr;
                </a>
            </div>

            @if($enrolledCourses->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-teal-50 text-teal-800">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="mt-3 text-base font-bold text-slate-800">Belum Ada Mata Kuliah yang Terdaftar</h3>
                    <p class="mt-1 text-sm text-slate-500 max-w-md mx-auto">
                        Akun Anda belum ditambahkan ke kelas perkuliahan semester ini. Silakan hubungi dosen pengampu mata kuliah Anda agar ditambahkan ke daftar kelas.
                    </p>
                </div>
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($enrolledCourses as $course)
                        <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs hover:border-teal-600 hover:shadow-md transition">
                            <div class=" inset-x-0 top-0 h-1.5 w-full rounded-full bg-linear-to-r from-[#05285c] to-[#00a98f] mb-4"></div>
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="rounded-lg bg-teal-50 px-2.5 py-1 text-xs font-bold text-teal-900 border border-teal-200/60">
                                        {{ $course->code }} &middot; {{ $course->class_group }}
                                    </span>
                                    <span class="rounded-lg bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700">
                                        {{ $course->credit_hours }} SKS
                                    </span>
                                </div>

                                <h3 class="mt-3.5 text-lg font-bold text-slate-900 group-hover:text-teal-800 transition line-clamp-1">
                                    <a href="{{ route('courses.show', $course) }}" title="{{ $course->name }}">
                                        {{ $course->name }}
                                    </a>
                                </h3>

                                <p class="mt-1.5 text-xs sm:text-sm font-medium text-slate-600">
                                    Dosen: <strong class="text-slate-800">{{ $course->lecturer->name }}</strong>
                                </p>

                                <div class="mt-3 flex items-center gap-1.5 text-xs text-slate-600">
                                    <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="truncate">{{ $course->scheduleLabel() }} ({{ $course->room ?: 'Daring' }})</span>
                                </div>

                                {{-- Progress Bar Pertemuan --}}
                                <div class="mt-4 pt-3 border-t border-slate-100">
                                    <div class="flex items-center justify-between text-xs text-slate-600 mb-1.5">
                                        <span><strong>{{ $course->materials_count }}</strong> Bahan Ajar</span>
                                        <span><strong>{{ $course->sessions_count }}/16</strong> Sesi Berjalan</span>
                                    </div>
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-teal-600 transition-all" style="width: {{ min(100, round(($course->sessions_count / 16) * 100)) }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-400">
                                    {{ $course->academicPeriod?->name }}
                                </span>
                                <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center gap-1 text-xs sm:text-sm font-bold text-teal-800 hover:text-teal-950 transition">
                                    Masuk Kelas &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- TUGAS & REVISI SAYA (WRITEWISE) --}}
        <section aria-labelledby="student-work-heading" class="scroll-mt-24">
            <div class="mb-4">
                <h2 id="student-work-heading" class="text-xl font-bold text-slate-900">Tugas dan Revisi Saya</h2>
                <p class="mt-1 text-sm text-slate-600">Tugas penulisan akademik dan asesmen yang ditugaskan kepada akun Anda di WriteWise.</p>
            </div>

            @if($assignments->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">
                    Belum ada tugas akademik yang ditugaskan kepada akun Anda saat ini.
                </div>
            @else
                <div class="divide-y divide-slate-100 rounded-2xl border border-slate-200/90 bg-white overflow-hidden shadow-2xs">
                    @foreach($assignments as $assignment)
                        @php
                            $dashAssignmentUrl = ($assignment->isStimulusExam() || $assignment->isHybrid())
                                ? route('assignments.exam.take', $assignment)
                                : route('assignments.show', $assignment);
                        @endphp
                        <div class="flex flex-wrap items-center justify-between gap-3 p-5 hover:bg-slate-50/70 transition">
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-900 text-base">
                                    <a href="{{ $dashAssignmentUrl }}" class="hover:text-teal-800">
                                        {{ $assignment->title }}
                                    </a>
                                </h3>
                                <p class="mt-1 text-xs sm:text-sm text-slate-500">
                                    {{ $assignment->project->name }}
                                    @if($assignment->due_at)
                                        &middot; <span class="text-amber-700 font-semibold">Batas Waktu: {{ $assignment->due_at->translatedFormat('d F Y, H:i') }}</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <a href="{{ $dashAssignmentUrl }}" class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-4 py-2 text-xs font-bold text-white hover:bg-teal-700 transition">
                                    <span>Buka Tugas</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

{{-- TAMPILAN 3: PENGGUNA UMUM --}}
