    <div class="grid gap-6 lg:grid-cols-12">
        {{-- KOLOM KIRI: FAKULTAS (5 Kolom) --}}
        <section class="lg:col-span-5 space-y-4">
            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 shadow-sm">
                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-bold text-slate-900">1. Daftar Fakultas</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Tambahkan fakultas yang menaungi program studi pengajaran Anda.</p>
                </div>

                {{-- Form Tambah Fakultas --}}
                <form method="post" action="{{ route('academic-structure.faculties.store') }}" class="mt-5 space-y-3.5 rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                    @csrf
                    <p class="text-xs sm:text-sm font-bold text-slate-800">+ Tambah Fakultas Baru</p>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Kode Singkat *</label>
                            <input type="text" name="code" required maxlength="20" placeholder="FKIP" value="{{ old('code') }}"
                                class="mt-1 w-full uppercase font-bold">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700">Nama Fakultas *</label>
                            <input type="text" name="name" required maxlength="150" placeholder="Fakultas Keguruan dan Ilmu Pendidikan" value="{{ old('name') }}"
                                class="mt-1 w-full">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Deskripsi / Keterangan (Opsional)</label>
                        <input type="text" name="description" maxlength="500" placeholder="Keterangan singkat fakultas..." value="{{ old('description') }}"
                            class="mt-1 w-full">
                    </div>
                    <div class="flex justify-end pt-1">
                        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs sm:text-sm font-bold text-white hover:bg-slate-800 transition">
                            Simpan Fakultas
                        </button>
                    </div>
                </form>

                {{-- List Fakultas --}}
                <div class="mt-5 divide-y divide-slate-100">
                    @forelse ($faculties as $faculty)
                        <div class="py-4 first:pt-0 last:pb-0">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-md bg-slate-900 px-2 py-0.5 font-mono text-xs font-bold text-white">{{ $faculty->code }}</span>
                                        <h3 class="text-sm sm:text-base font-bold text-slate-900">{{ $faculty->name }}</h3>
                                    </div>
                                    @if ($faculty->description)
                                        <p class="mt-1 text-xs text-slate-500">{{ $faculty->description }}</p>
                                    @endif
                                    <p class="mt-1 text-xs font-semibold text-teal-700">{{ $faculty->study_programs_count }} Program Studi</p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    @if ($faculty->study_programs_count === 0)
                                        <form method="post" action="{{ route('academic-structure.faculties.destroy', $faculty) }}"
                                            onsubmit="return confirm('Hapus fakultas {{ $faculty->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            {{-- Edit Fakultas Inline --}}
                            <details class="mt-2 group">
                                <summary class="cursor-pointer text-xs font-semibold text-slate-500 hover:text-teal-700">
                                    Edit Fakultas
                                </summary>
                                <form method="post" action="{{ route('academic-structure.faculties.update', $faculty) }}"
                                    class="mt-2 space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid gap-3 sm:grid-cols-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700">Kode</label>
                                            <input type="text" name="code" required maxlength="20" value="{{ $faculty->code }}"
                                                class="mt-1 w-full uppercase font-bold">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block text-xs font-semibold text-slate-700">Nama Fakultas</label>
                                            <input type="text" name="name" required maxlength="150" value="{{ $faculty->name }}"
                                                class="mt-1 w-full">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700">Deskripsi</label>
                                        <input type="text" name="description" maxlength="500" value="{{ $faculty->description }}"
                                            class="mt-1 w-full">
                                    </div>
                                    <div class="flex justify-end pt-1">
                                        <button type="submit" class="rounded-xl bg-teal-800 px-4 py-1.5 text-xs font-bold text-white hover:bg-teal-900 transition">
                                            Perbarui Fakultas
                                        </button>
                                    </div>
                                </form>
                            </details>
                        </div>
                    @empty
                        <p class="py-6 text-center text-sm text-slate-500">Belum ada data fakultas. Silakan tambahkan fakultas terlebih dahulu.</p>
                    @endforelse
                </div>
            </div>
        </section>

