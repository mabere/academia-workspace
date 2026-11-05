    {{-- BAGIAN 3: PERIODE SEMESTER AKADEMIK --}}
    <section class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-7 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">3. Periode Semester Akademik</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Atur tahun akademik dan semester aktif untuk pengelompokan mata kuliah serta pelaporan BKD.</p>
            </div>
        </div>

        <div class="mt-5 grid gap-6 lg:grid-cols-12">
            {{-- Form Tambah Periode --}}
            <form method="post" action="{{ route('academic-structure.periods.store') }}" class="lg:col-span-5 space-y-3.5 rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                @csrf
                <p class="text-xs sm:text-sm font-bold text-slate-800">+ Tambah Periode Semester Baru</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Nama Periode *</label>
                        <input type="text" name="name" required maxlength="80" placeholder="2026/2027 Genap"
                            class="mt-1 w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Tahun Akademik *</label>
                        <input type="text" name="academic_year" required maxlength="20" placeholder="2026/2027"
                            class="mt-1 w-full">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Semester *</label>
                        <select name="semester" required class="mt-1 w-full">
                            <option value="odd">Ganjil (Odd)</option>
                            <option value="even">Genap (Even)</option>
                            <option value="short">Antara (Short)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Tanggal Mulai *</label>
                        <input type="date" name="start_date" required value="{{ now()->format('Y-m-d') }}"
                            class="mt-1 w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Tanggal Selesai *</label>
                        <input type="date" name="end_date" required value="{{ now()->addMonths(5)->format('Y-m-d') }}"
                            class="mt-1 w-full">
                    </div>
                </div>
                <div class="flex items-center justify-between pt-2">
                    <label class="inline-flex items-center gap-2 text-xs font-medium text-slate-700 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                        <span>Tetapkan sebagai semester aktif</span>
                    </label>
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs sm:text-sm font-bold text-white hover:bg-slate-800 transition">
                        Simpan Periode
                    </button>
                </div>
            </form>

            {{-- Daftar Periode --}}
            <div class="lg:col-span-7 overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="border-b border-slate-200 bg-slate-50/80 text-xs font-bold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="py-3 px-4">Periode Semester</th>
                            <th class="py-3 px-4">Rentang Tanggal</th>
                            <th class="py-3 px-4 text-center">Mata Kuliah</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($academicPeriods as $period)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $period->name }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">TA {{ $period->academic_year }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 text-xs sm:text-sm">
                                    {{ $period->start_date?->format('d/m/Y') }} &ndash; {{ $period->end_date?->format('d/m/Y') }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                    {{ $period->courses_count }} MK
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($period->is_active)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                            Semester Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                            Arsip / Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        @if (! $period->is_active)
                                            <form method="post" action="{{ route('academic-structure.periods.activate', $period) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-xl border border-teal-200 bg-teal-50 px-3 py-1.5 text-xs font-bold text-teal-800 hover:bg-teal-100 transition shadow-2xs">
                                                    Aktifkan
                                                </button>
                                            </form>
                                        @endif
                                        @if ($period->courses_count === 0 && $period->projects_count === 0)
                                            <form method="post" action="{{ route('academic-structure.periods.destroy', $period) }}"
                                                onsubmit="return confirm('Hapus periode {{ $period->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-xl border border-red-200 px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-sm text-slate-500">Belum ada periode akademik.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
