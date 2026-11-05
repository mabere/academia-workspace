        {{-- KOLOM KANAN: PROGRAM STUDI (7 Kolom) --}}
        <section class="lg:col-span-7 space-y-4">
            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 shadow-sm">
                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-bold text-slate-900">2. Daftar Program Studi (Prodi)</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Program studi tempat mata kuliah diselenggarakan dan homebase mahasiswa.</p>
                </div>

                {{-- Form Tambah Prodi --}}
                @if ($faculties->isEmpty())
                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs sm:text-sm text-amber-900">
                        Tambahkan minimal satu <strong>Fakultas</strong> di kolom sebelah kiri terlebih dahulu sebelum membuat Program Studi.
                    </div>
                @else
                    <form method="post" action="{{ route('academic-structure.study-programs.store') }}" class="mt-5 space-y-3.5 rounded-2xl border border-teal-200 bg-teal-50/40 p-5">
                        @csrf
                        <p class="text-xs sm:text-sm font-bold text-teal-950">+ Tambah Program Studi Baru</p>
                        <div class="grid gap-3 sm:grid-cols-12">
                            <div class="sm:col-span-5">
                                <label class="block text-xs font-semibold text-slate-700">Fakultas Induk *</label>
                                <select name="faculty_id" required class="mt-1 w-full">
                                    @foreach ($faculties as $fac)
                                        <option value="{{ $fac->id }}">{{ $fac->code }} — {{ $fac->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-semibold text-slate-700">Kode Prodi *</label>
                                <input type="text" name="code" required maxlength="20" placeholder="PBI-S1"
                                    class="mt-1 w-full uppercase font-bold">
                            </div>
                            <div class="sm:col-span-4">
                                <label class="block text-xs font-semibold text-slate-700">Jenjang *</label>
                                <select name="level" required class="mt-1 w-full font-semibold">
                                    @foreach (['S1', 'S2', 'S3', 'D4', 'D3', 'Profesi'] as $lvl)
                                        <option value="{{ $lvl }}">{{ $lvl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-end gap-3 pt-1">
                            <div class="flex-1 min-w-[200px]">
                                <label class="block text-xs font-semibold text-slate-700">Nama Program Studi *</label>
                                <input type="text" name="name" required maxlength="150" placeholder="Pendidikan Bahasa Inggris"
                                    class="mt-1 w-full">
                            </div>
                            <button type="submit" class="rounded-xl bg-teal-800 px-5 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:bg-teal-900 transition">
                                Simpan Prodi
                            </button>
                        </div>
                    </form>
                @endif

                {{-- Tabel Program Studi --}}
                <div class="mt-5 divide-y divide-slate-100">
                    @forelse ($studyPrograms as $prodi)
                        <div class="py-4 first:pt-0 last:pb-0">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-md bg-teal-100 px-2 py-0.5 font-mono text-xs font-bold text-teal-900">{{ $prodi->code }}</span>
                                        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700">{{ $prodi->level }}</span>
                                        <h3 class="text-sm sm:text-base font-bold text-slate-900">{{ $prodi->name }}</h3>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Fakultas: <strong class="text-slate-700">{{ $prodi->faculty?->name ?? '-' }} ({{ $prodi->faculty?->code ?? '-' }})</strong>
                                    </p>
                                    <div class="mt-1.5 flex items-center gap-3 text-xs font-semibold text-slate-600">
                                        <span>{{ $prodi->courses_count }} Mata Kuliah</span>
                                        <span>&middot;</span>
                                        <a href="{{ route('students.index', ['study_program_id' => $prodi->id]) }}" class="text-teal-700 hover:underline">
                                            {{ $prodi->students_count }} Mahasiswa Terdaftar
                                        </a>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    @if ($prodi->courses_count === 0)
                                        <form method="post" action="{{ route('academic-structure.study-programs.destroy', $prodi) }}"
                                            onsubmit="return confirm('Hapus Program Studi {{ $prodi->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            {{-- Edit Prodi Inline --}}
                            <details class="mt-2 group">
                                <summary class="cursor-pointer text-xs font-semibold text-slate-500 hover:text-teal-700">
                                    Edit Program Studi
                                </summary>
                                <form method="post" action="{{ route('academic-structure.study-programs.update', $prodi) }}"
                                    class="mt-2 space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid gap-3 sm:grid-cols-12">
                                        <div class="sm:col-span-5">
                                            <label class="block text-xs font-semibold text-slate-700">Fakultas</label>
                                            <select name="faculty_id" required class="mt-1 w-full">
                                                @foreach ($faculties as $fac)
                                                    <option value="{{ $fac->id }}" @selected($prodi->faculty_id === $fac->id)>{{ $fac->code }} — {{ $fac->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="sm:col-span-3">
                                            <label class="block text-xs font-semibold text-slate-700">Kode Prodi</label>
                                            <input type="text" name="code" required maxlength="20" value="{{ $prodi->code }}"
                                                class="mt-1 w-full uppercase font-bold">
                                        </div>
                                        <div class="sm:col-span-4">
                                            <label class="block text-xs font-semibold text-slate-700">Jenjang</label>
                                            <select name="level" required class="mt-1 w-full">
                                                @foreach (['S1', 'S2', 'S3', 'D4', 'D3', 'Profesi'] as $lvl)
                                                    <option value="{{ $lvl }}" @selected($prodi->level === $lvl)>{{ $lvl }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-end gap-3 pt-1">
                                        <div class="flex-1">
                                            <label class="block text-xs font-semibold text-slate-700">Nama Program Studi</label>
                                            <input type="text" name="name" required maxlength="150" value="{{ $prodi->name }}"
                                                class="mt-1 w-full">
                                        </div>
                                        <button type="submit" class="rounded-xl bg-teal-800 px-4 py-2 text-xs font-bold text-white hover:bg-teal-900 transition">
                                            Perbarui Prodi
                                        </button>
                                    </div>
                                </form>
                            </details>
                        </div>
                    @empty
                        <p class="py-6 text-center text-sm text-slate-500">Belum ada Program Studi yang terdaftar.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

