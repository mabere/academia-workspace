            {{-- 1. SISI MAHASISWA: STATUS PRESENSI HARI INI --}}
            @if(!auth()->user()->isStaff() && $activeSession)
                @php
                    $myAttendance = $activeSession->studentAttendance(auth()->id());
                @endphp
                @if($myAttendance)
                    <div class="rounded-3xl border border-emerald-200 bg-emerald-50/80 p-5 shadow-xs flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="h-11 w-11 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-xl shadow-2xs">
                                ✓
                            </div>
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Presensi Berhasil Terverifikasi</span>
                                <h3 class="text-sm font-extrabold text-slate-900">Pertemuan Ke-{{ $activeSession->session_number }}: {{ $activeSession->topic }}</h3>
                                <p class="text-xs text-slate-600">
                                    Kehadiran Anda telah dicatat pada pukul {{ $myAttendance->checked_in_at?->format('H:i') ?? '-' }} WIB.
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex rounded-full bg-emerald-200/80 px-4 py-1 text-xs font-black text-emerald-950 border border-emerald-300">
                            Status: Hadir di Kelas
                        </span>
                    </div>
                @else
                    <div id="quick-attendance" class="rounded-3xl border-2 border-teal-500 bg-gradient-to-r from-teal-50 via-emerald-50 to-teal-50 p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="space-y-1">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-800 px-3 py-0.5 text-[11px] font-bold text-white uppercase tracking-wider animate-pulse">
                                    ● Presensi Kuliah Sedang Dibuka
                                </span>
                                <h3 class="text-base font-extrabold text-slate-900">Pertemuan Ke-{{ $activeSession->session_number }}: {{ $activeSession->topic }}</h3>
                                <p class="text-xs text-slate-600">
                                    Silakan masukkan 4-digit PIN presensi yang ditampilkan oleh dosen pengampu di layar proyektor kelas:
                                </p>
                            </div>
                            <form method="post" action="{{ route('courses.sessions.check-in', [$course, $activeSession]) }}" class="flex items-center gap-3">
                                @csrf
                                <input type="text" name="passcode" required maxlength="4" pattern="[0-9]{4}" placeholder="0000"
                                    class="w-32 text-center font-mono font-black tracking-widest text-2xl rounded-2xl border-2 border-teal-400 focus:border-teal-700 focus:ring-teal-700 shadow-xs bg-white py-2.5">
                                <button type="submit" class="rounded-2xl bg-teal-800 px-5 py-3 text-xs sm:text-sm font-bold text-white hover:bg-teal-700 shadow-sm transition">
                                    Kirim Presensi
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endif

            {{-- 2. SISI DOSEN: LAYAR PROYEKTOR KELAS KODE PIN ATAU KONTROL BUKA CEPAT --}}
            @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)))
                @if($activeSession)
                    {{-- A. LAYAR PROYEKTOR PIN KELAS (AKTIF) --}}
                    <div class="rounded-3xl border-2 border-teal-600 bg-gradient-to-br from-slate-950 via-teal-950 to-slate-950 text-white p-6 sm:p-8 shadow-xl" x-data="{ showAttendees: false }">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/20 text-emerald-300 border border-emerald-400/40 px-3.5 py-1 text-xs font-extrabold uppercase tracking-wider">
                                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                                        Presensi Kelas Sedang Berlangsung
                                    </span>
                                    <span class="text-xs font-bold text-teal-200">
                                        Pertemuan Ke-{{ $activeSession->session_number }}
                                    </span>
                                </div>
                                <h2 class="text-xl sm:text-2xl font-black text-white mt-2">{{ $activeSession->topic }}</h2>
                                <p class="text-xs text-slate-300 mt-1">
                                    Tampilkan kode 4-digit ini di layar kelas / proyektor agar mahasiswa dapat melakukan presensi mandiri.
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <form method="post" action="{{ route('courses.sessions.regenerate-code', [$course, $activeSession]) }}">
                                    @csrf
                                    <button type="submit" class="rounded-xl border border-slate-700 bg-slate-800/80 px-3.5 py-2 text-xs font-bold text-slate-200 hover:bg-slate-700 hover:text-white transition shadow-sm" title="Acak ulang PIN jika bocor">
                                        🔄 Acak Ulang PIN
                                    </button>
                                </form>

                                <form method="post" action="{{ route('courses.sessions.close-attendance', [$course, $activeSession]) }}" onsubmit="return confirm('Tutup sesi presensi pertemuan ini?')">
                                    @csrf
                                    <button type="submit" class="rounded-xl bg-rose-600 hover:bg-rose-500 px-4 py-2 text-xs font-bold text-white transition shadow-sm">
                                        ✕ Tutup Presensi Sesi
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Display Kode Monospace Raksasa & Statistik Realtime --}}
                        <div class="mt-6 flex flex-wrap items-center justify-between gap-6 border-t border-slate-800 pt-6">
                            <div class="flex items-center gap-6">
                                <div class="rounded-3xl bg-white/10 p-4 backdrop-blur border border-white/20 text-center shadow-inner">
                                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-teal-300 block mb-1">KODE PRESENSI PIN</span>
                                    <div class="text-4xl sm:text-5xl font-mono font-black tracking-widest text-emerald-300 select-all px-4">
                                        {{ $activeSession->passcode }}
                                    </div>
                                </div>

                                <div class="text-xs text-slate-300 space-y-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Aktif s.d: <strong class="text-white">{{ $activeSession->passcode_expires_at?->format('H:i') ?? 'Selesai' }} WIB</strong></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span>Sudah Hadir: <strong class="text-emerald-300 font-extrabold text-sm">{{ $activeSession->attendances->count() }}</strong> dari {{ $students->count() }} Mahasiswa ({{ round(($activeSession->attendances->count() / ($students->count() ?: 1)) * 100) }}%)</span>
                                    </div>
                                </div>
                            </div>

                            <button type="button" @click="showAttendees = !showAttendees"
                                class="rounded-xl border border-teal-400/40 bg-teal-800/60 px-4 py-2.5 text-xs font-bold text-teal-200 hover:bg-teal-700 hover:text-white transition">
                                <span x-text="showAttendees ? 'Sembunyikan Daftar Mahasiswa' : 'Lihat Mahasiswa Hadir (' + {{ $activeSession->attendances->count() }} + ')'"></span>
                            </button>
                        </div>

                        {{-- Attendee Drawer --}}
                        <div x-show="showAttendees" x-cloak class="mt-5 rounded-2xl bg-slate-900/90 p-4 border border-slate-800 max-h-60 overflow-y-auto">
                            @if($activeSession->attendances->isEmpty())
                                <p class="text-xs text-slate-400 text-center py-4">Belum ada mahasiswa yang memasukkan kode presensi.</p>
                            @else
                                <div class="grid gap-2 sm:grid-cols-2 md:grid-cols-3 text-xs">
                                    @foreach($activeSession->attendances as $att)
                                        <div class="flex items-center justify-between rounded-xl bg-slate-800/80 px-3 py-2 border border-slate-700/60">
                                            <div class="truncate mr-2">
                                                <div class="font-bold text-slate-100 truncate">{{ $att->student->name }}</div>
                                                <div class="text-[10px] text-slate-400 font-mono">{{ $att->student->nim ?: 'NIM -' }}</div>
                                            </div>
                                            <span class="rounded-lg bg-emerald-500/20 text-emerald-300 text-[10px] px-2 py-0.5 font-mono whitespace-nowrap">
                                                {{ $att->checked_in_at?->format('H:i') }} WIB
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    {{-- B. KONTROL BUKA PRESENSI & CATAT JURNAL MANUAL (Hanya muncul saat tombol di toolbar diklik) --}}
                    <div x-show="sessionFormMode !== null" x-cloak class="rounded-3xl border border-teal-200 bg-white p-6 shadow-xs">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <h3 class="text-sm font-extrabold text-slate-900" x-text="sessionFormMode === 'pin' ? 'Buka Presensi Kelas dengan Kode PIN' : 'Pencatatan Jurnal Perkuliahan Manual'"></h3>
                            <button type="button" @click="sessionFormMode = null" class="text-xs font-bold text-slate-400 hover:text-slate-700">&times; Tutup</button>
                        </div>

                        {{-- Form 1: Buka Presensi Cepat (PIN 4-Digit) --}}
                        <form x-show="sessionFormMode === 'pin'" method="post" action="{{ route('courses.sessions.quick-open', $course) }}" class="mt-4 space-y-3">
                            @csrf
                            <div class="grid gap-3 sm:grid-cols-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Pertemuan</label>
                                    <select name="session_number" class="mt-1 w-full rounded-xl border-slate-300 text-xs font-bold focus:border-teal-700 focus:ring-teal-700">
                                        @for($i = 1; $i <= 16; $i++)
                                            <option value="{{ $i }}" @selected($sessions->count() + 1 === $i)>Pertemuan {{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-slate-700">Pokok Bahasan *</label>
                                    <input type="text" name="topic" required class="mt-1 w-full rounded-xl border-slate-300 text-xs focus:border-teal-700 focus:ring-teal-700">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Durasi PIN</label>
                                    <select name="duration_minutes" class="mt-1 w-full rounded-xl border-slate-300 text-xs font-semibold focus:border-teal-700 focus:ring-teal-700">
                                        <option value="30">30 Menit</option>
                                        <option value="60" selected>60 Menit</option>
                                        <option value="90">90 Menit</option>
                                        <option value="120">120 Menit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex justify-end pt-1">
                                <button type="submit" class="rounded-xl bg-teal-800 py-2 px-5 text-xs font-bold text-white hover:bg-teal-700 transition shadow-sm">
                                    Buka &amp; Tampilkan PIN
                                </button>
                            </div>
                        </form>

                        {{-- Form 2: Pencatatan Jurnal Manual --}}
                        <form x-show="sessionFormMode === 'manual'" method="post" action="{{ route('courses.sessions.store', $course) }}" class="mt-4 space-y-3">
                            @csrf
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Pertemuan *</label>
                                    <select name="session_number" required class="mt-1 w-full rounded-xl border-slate-300 text-xs">
                                        @for($i = 1; $i <= 16; $i++)
                                            <option value="{{ $i }}" @selected($sessions->count() + 1 === $i)>Pertemuan {{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Tanggal *</label>
                                    <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="mt-1 w-full rounded-xl border-slate-300 text-xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Mahasiswa Hadir *</label>
                                    <input type="number" name="attendance_count" required value="{{ $students->count() ?: 1 }}" min="0" class="mt-1 w-full rounded-xl border-slate-300 text-xs">
                                    <input type="hidden" name="total_students" value="{{ $students->count() ?: 1 }}">
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Pokok Bahasan *</label>
                                    <input type="text" name="topic" required class="mt-1 w-full rounded-xl border-slate-300 text-xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Metode Pembelajaran</label>
                                    <input type="text" name="teaching_method" class="mt-1 w-full rounded-xl border-slate-300 text-xs">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700">Catatan Dosen</label>
                                <textarea name="notes" rows="2" class="mt-1 w-full rounded-xl border-slate-300 text-xs"></textarea>
                            </div>

                            <div class="flex justify-end pt-1">
                                <button type="submit" class="rounded-xl bg-teal-800 px-5 py-2 text-xs font-bold text-white hover:bg-teal-700 transition">
                                    Simpan Jurnal
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            @endif

