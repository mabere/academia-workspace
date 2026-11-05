    {{-- PANEL 2: IMPOR / PASTE KOLEKTIF CEPAT --}}
    <div x-show="panel === 'bulk'" x-cloak class="rounded-3xl border border-teal-200 bg-teal-50/40 p-6 sm:p-7 shadow-sm">
        <div class="flex items-center justify-between border-b border-teal-200/70 pb-4">
            <div>
                <h2 class="text-lg font-bold text-teal-950">Impor / Paste Kolektif Cepat dari Excel atau SIAKAD</h2>
                <p class="text-xs sm:text-sm text-teal-800 mt-0.5">Salin dan tempel daftar NIM serta Nama Mahasiswa langsung dari tabel Excel/absen kelas.</p>
            </div>
            <button type="button" @click="panel = 'none'" class="rounded-lg p-1.5 text-teal-800 hover:text-teal-950 hover:bg-teal-100 transition" title="Tutup form">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form method="post" action="{{ route('students.bulk-store') }}" class="mt-5 space-y-5">
            @csrf
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-800">
                    Daftar Mahasiswa (1 Mahasiswa per Baris: <code class="rounded bg-white px-2 py-0.5 text-teal-900 border border-teal-200 font-mono text-xs">NIM ; Nama Lengkap ; Email(opsional)</code>) *
                </label>
                <p class="mt-1 text-xs text-slate-600">
                    Mendukung pemisah titik koma (<code class="font-bold">;</code>), koma (<code class="font-bold">,</code>), atau <code class="font-bold">Tab</code> langsung dari copy-paste kolom Excel.
                </p>
                <textarea name="raw_list" rows="6" required
                    placeholder="240105001 ; Siti Aminah&#10;240105002 ; Budi Santoso ; budi@student.ac.id&#10;240105003	Rina Kartika"
                    class="mt-2 w-full rounded-2xl border-slate-300 bg-white font-mono text-sm leading-relaxed">{{ old('raw_list') }}</textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700">Set Program Studi untuk Daftar Ini</label>
                    <select name="study_program_id" class="mt-1.5 w-full bg-white">
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach ($studyPrograms as $prodi)
                            <option value="{{ $prodi->id }}">{{ $prodi->name }} ({{ $prodi->level }}) — {{ $prodi->faculty?->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700">Set Tahun Angkatan</label>
                    <input type="number" name="cohort_year" min="2000" max="2099" value="{{ now()->year }}"
                        class="mt-1.5 w-full bg-white">
                </div>
            </div>

            @if ($courses->isNotEmpty())
                <div class="rounded-2xl border border-teal-200 bg-white p-5 space-y-3">
                    <label class="block text-xs sm:text-sm font-bold text-slate-800">Assign Seluruh Daftar Ini ke Mata Kuliah:</label>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($courses as $course)
                            <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3 cursor-pointer hover:border-teal-500 hover:bg-teal-50/30 transition">
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
                <button type="submit" class="rounded-xl bg-teal-800 px-6 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:bg-teal-900 transition">
                    Proses Impor & Assign ke Kelas
                </button>
            </div>
        </form>
    </div>

