    {{-- PANEL 1: TAMBAH MAHASISWA SATUAN + ASSIGN MK --}}
    <div x-show="panel === 'single'" x-cloak class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-7 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Tambah Mahasiswa Satuan & Plotting Mata Kuliah</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Jika email dikosongkan, sistem otomatis membuatkan email berbasis NIM dan kata sandi default sesuai NIM.</p>
            </div>
            <button type="button" @click="panel = 'none'" class="rounded-lg p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition" title="Tutup form">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form method="post" action="{{ route('students.store') }}" class="mt-5 space-y-5">
            @csrf
            <div class="grid gap-4 sm:grid-cols-4">
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700">NIM Mahasiswa *</label>
                    <input type="text" name="nim" required maxlength="32" placeholder="Contoh: 240105001" value="{{ old('nim') }}"
                        class="mt-1.5 w-full font-mono font-bold">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700">Nama Lengkap Mahasiswa *</label>
                    <input type="text" name="name" required maxlength="150" placeholder="Nama lengkap sesuai absen..." value="{{ old('name') }}"
                        class="mt-1.5 w-full">
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700">Email (Opsional)</label>
                    <input type="email" name="email" maxlength="150" placeholder="Otomatis jika kosong" value="{{ old('email') }}"
                        class="mt-1.5 w-full">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs sm:text-sm font-semibold text-slate-700">Program Studi</label>
                        @if(auth()->user()->isSuperadmin())
                            <a href="{{ route('academic-structure.index') }}" class="text-xs font-semibold text-teal-700 hover:underline">+ Kelola Prodi</a>
                        @endif
                    </div>
                    <select name="study_program_id" class="mt-1.5 w-full">
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach ($studyPrograms as $prodi)
                            <option value="{{ $prodi->id }}" @selected(old('study_program_id') == $prodi->id)>
                                {{ $prodi->name }} ({{ $prodi->level }}) — {{ $prodi->faculty?->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700">Tahun Angkatan</label>
                    <input type="number" name="cohort_year" min="2000" max="2099" placeholder="{{ now()->year }}" value="{{ old('cohort_year', now()->year) }}"
                        class="mt-1.5 w-full">
                </div>
            </div>

            @if ($courses->isNotEmpty())
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 space-y-3">
                    <label class="block text-xs sm:text-sm font-bold text-slate-800">Assign Langsung ke Mata Kuliah yang Diprogramkan (Opsional):</label>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($courses as $course)
                            <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 cursor-pointer hover:border-teal-400 hover:shadow-2xs transition">
                                <input type="checkbox" name="course_ids[]" value="{{ $course->id }}" class="mt-1 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                                <span class="min-w-0 flex-1">
                                    <span class="block text-xs sm:text-sm font-bold text-slate-900 truncate">{{ $course->code }} — {{ $course->name }}</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">{{ $course->class_group }} &middot; {{ $course->academicPeriod?->name }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex justify-end gap-2.5 pt-2">
                <button type="button" @click="panel = 'none'" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:bg-slate-800 transition">
                    Simpan & Assign Mahasiswa
                </button>
            </div>
        </form>
    </div>

