            {{-- 3. REKAPITULASI TABEL 16 PERTEMUAN (DENGAN SLIDE-OVER ATTENDEE MODAL) --}}
            <div class="rounded-3xl border border-slate-200/90 bg-white overflow-hidden shadow-xs" x-data="{ activeAttendeeModal: null }">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-800">
                            Jurnal & Linimasa 16 Pertemuan Perkuliahan
                        </span>
                        <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-[10px] font-bold text-teal-900">
                            {{ $sessions->count() }} Tercatat
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        @if(auth()->user()->isStaff())
                            <form method="post" action="{{ route('courses.sessions.sync-attendance', $course) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-teal-900 transition" title="Sinkronkan persentase presensi seluruh mahasiswa">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span>Sinkronkan Presensi</span>
                                </button>
                            </form>
                            <span class="text-slate-300">·</span>
                        @endif
                        <a href="{{ route('courses.sessions.print', $course) }}" target="_blank"
                            class="inline-flex items-center gap-1 text-xs font-bold text-teal-800 hover:text-teal-950 transition">
                            <span>Cetak Laporan Format A4 &rarr;</span>
                        </a>
                    </div>
                </div>

                @if($sessions->isEmpty())
                    <div class="p-12 text-center text-sm text-slate-500">
                        Belum ada riwayat pertemuan perkuliahan yang dicatat untuk kelas ini.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="border-b border-slate-200 bg-slate-50/70 font-bold uppercase tracking-wider text-slate-600">
                                <tr>
                                    <th class="py-3.5 px-4 w-16 text-center">Sesi</th>
                                    <th class="py-3.5 px-4 w-28">Tanggal</th>
                                    <th class="py-3.5 px-4">Pokok Bahasan / Materi</th>
                                    <th class="py-3.5 px-4 w-36">Metode</th>
                                    <th class="py-3.5 px-4 w-36 text-center">Kehadiran</th>
                                    <th class="py-3.5 px-4 w-28 text-center">Status Sesi</th>
                                    <th class="py-3.5 px-4">Catatan Dosen</th>
                                    @if(auth()->user()->isStaff())
                                        <th class="py-3.5 px-4 w-32 text-center">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($sessions as $session)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-teal-50 font-mono font-bold text-teal-900 border border-teal-200/70 shadow-2xs">
                                                #{{ $session->session_number }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-slate-600">
                                            {{ $session->date?->format('d/m/Y') ?? '-' }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="font-bold text-slate-900 block text-xs sm:text-sm">{{ $session->topic }}</span>
                                            @php
                                                $sessionMaterialsCount = $materials->where('session_number', $session->session_number)->count();
                                                $sessionAssignmentsCount = $assignments->filter(fn ($a) => (int) ($a->config_json['session_number'] ?? 0) === (int) $session->session_number)->count();
                                            @endphp
                                            @if($sessionMaterialsCount > 0 || $sessionAssignmentsCount > 0)
                                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                                    @if($sessionMaterialsCount > 0)
                                                        <button type="button" @click="tab = 'materials'"
                                                            class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-800 border border-blue-200 hover:bg-blue-100 transition">
                                                            <span>{{ $sessionMaterialsCount }} Materi</span>
                                                        </button>
                                                    @endif
                                                    @if($sessionAssignmentsCount > 0)
                                                        <button type="button" @click="tab = 'assignments'; $dispatch('filter-assignments-session', {{ $session->session_number }})"
                                                            class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-800 border border-indigo-200 hover:bg-indigo-100 transition">
                                                            <span>{{ $sessionAssignmentsCount }} Tugas</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-600">
                                            {{ $session->teaching_method ?: 'Tatap Muka' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            @if(auth()->user()->isStaff())
                                                <button type="button" @click="activeAttendeeModal = {{ $session->id }}"
                                                    class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold text-emerald-800 border border-emerald-200 hover:bg-emerald-100 transition shadow-2xs"
                                                    title="Klik untuk melihat daftar presensi mahasiswa">
                                                    {{ $session->attendance_count }}/{{ $session->total_students }} ({{ $session->attendancePercentage() }}%)
                                                </button>
                                            @else
                                                @if($session->hasStudentAttended(auth()->id()))
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-0.5 text-xs font-bold text-emerald-900 border border-emerald-200">
                                                        ✓ Hadir
                                                    </span>
                                                @elseif($session->isOpenForAttendance())
                                                    <a href="#quick-attendance" class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-0.5 text-xs font-bold text-amber-900 border border-amber-300 animate-pulse">
                                                        Isi Presensi &rarr;
                                                    </a>
                                                @else
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-400">
                                                        Tidak Hadir
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            @if($session->isOpenForAttendance())
                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 border border-amber-300 px-2.5 py-0.5 text-[11px] font-mono font-bold text-amber-900 animate-pulse">
                                                    ● {{ $session->passcode }}
                                                </span>
                                            @else
                                                <span class="rounded-lg bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold text-slate-600">
                                                    Selesai
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 italic max-w-xs truncate">
                                            {{ $session->notes ?: '-' }}
                                        </td>
                                        @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)))
                                            <td class="py-3.5 px-4 text-center">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    @if($session->isOpenForAttendance())
                                                        <form method="post" action="{{ route('courses.sessions.close-attendance', [$course, $session]) }}">
                                                            @csrf
                                                            <button class="rounded-lg px-2.5 py-1 text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition shadow-2xs" title="Tutup sesi presensi">
                                                                Tutup
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form method="post" action="{{ route('courses.sessions.open-attendance', [$course, $session]) }}">
                                                            @csrf
                                                            <input type="hidden" name="duration_minutes" value="60">
                                                            <button class="rounded-lg px-2.5 py-1 text-[11px] font-bold bg-teal-50 text-teal-800 border border-teal-200 hover:bg-teal-100 transition shadow-2xs" title="Buka presensi dengan kode PIN baru">
                                                                Buka Presensi
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <form method="post" action="{{ route('courses.sessions.destroy', [$course, $session]) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jurnal pertemuan ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="rounded-lg p-1 text-slate-400 hover:bg-red-50 hover:text-red-600 transition" title="Hapus sesi">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>

                                    {{-- Slide-over / Modal Detail Presensi Sesi --}}
                                    @if(auth()->user()->isStaff())
                                        <div x-show="activeAttendeeModal === {{ $session->id }}" x-cloak
                                            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                                            <div @click.away="activeAttendeeModal = null" class="w-full max-w-xl rounded-3xl bg-white shadow-2xl border border-slate-200 overflow-hidden">
                                                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                                                    <div>
                                                        <h3 class="font-extrabold text-slate-900 text-sm">Daftar Kehadiran Pertemuan Ke-{{ $session->session_number }}</h3>
                                                        <p class="text-xs text-slate-500">{{ $session->topic }} · {{ $session->date?->format('d/m/Y') }}</p>
                                                    </div>
                                                    <button type="button" @click="activeAttendeeModal = null" class="text-slate-400 hover:text-slate-700 font-bold text-xl">&times;</button>
                                                </div>

                                                <div class="p-6 max-h-96 overflow-y-auto divide-y divide-slate-100 text-xs">
                                                    @forelse($students as $cs)
                                                        @php
                                                            $studentAtt = $session->studentAttendance($cs->student_id);
                                                        @endphp
                                                        <div class="py-3 flex items-center justify-between gap-3">
                                                            <div>
                                                                <div class="font-bold text-slate-800">{{ $cs->student->name }}</div>
                                                                <div class="text-[11px] text-slate-500 font-mono">{{ $cs->nim ?: $cs->student->nim ?: 'NIM -' }} · {{ $cs->student->studyProgram->name ?? 'Prodi' }}</div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                @if($studentAtt)
                                                                    <span class="rounded-full bg-emerald-100 text-emerald-800 px-3 py-0.5 text-[10px] font-bold">
                                                                        Hadir ({{ $studentAtt->checked_in_at?->format('H:i') ?? '-' }})
                                                                    </span>
                                                                    <form method="post" action="{{ route('courses.sessions.attendances.toggle', [$course, $session]) }}">
                                                                        @csrf
                                                                        <input type="hidden" name="student_id" value="{{ $cs->student_id }}">
                                                                        <input type="hidden" name="status" value="absent">
                                                                        <button type="submit" class="text-[11px] text-rose-600 font-bold hover:underline">Ubah ke Alpa</button>
                                                                    </form>
                                                                @else
                                                                    <span class="rounded-full bg-slate-100 text-slate-400 px-3 py-0.5 text-[10px]">
                                                                        Tidak Hadir
                                                                    </span>
                                                                    <form method="post" action="{{ route('courses.sessions.attendances.toggle', [$course, $session]) }}">
                                                                        @csrf
                                                                        <input type="hidden" name="student_id" value="{{ $cs->student_id }}">
                                                                        <input type="hidden" name="status" value="present">
                                                                        <button type="submit" class="text-[11px] text-teal-800 font-bold hover:underline">Tandai Hadir</button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <p class="text-center text-slate-400 py-4">Belum ada mahasiswa terdaftar di kelas ini.</p>
                                                    @endforelse
                                                </div>

                                                <div class="border-t border-slate-100 bg-slate-50/80 px-6 py-3.5 flex justify-end">
                                                    <button type="button" @click="activeAttendeeModal = null" class="rounded-xl bg-slate-200 px-5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-300 transition">
                                                        Tutup
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- ======================================================== --}}
