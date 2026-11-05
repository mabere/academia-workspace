            {{-- Form Unggah Bahan Ajar Baru (Khusus Dosen / Staf) --}}
            @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)))
                <div class="rounded-3xl border border-teal-200 bg-white p-5 shadow-xs" x-data="{ mOpen: false, mType: 'document' }">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h3 class="text-sm font-extrabold text-slate-900">Bahan Ajar Perkuliahan</h3>

                        <button type="button" @click="mOpen = !mOpen"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-4 py-2 text-xs font-bold text-white hover:bg-teal-700 shadow-2xs transition">
                            <span x-text="mOpen ? 'Tutup Formulir' : '+ Unggah Bahan Ajar'"></span>
                            <svg class="h-4 w-4 transition-transform duration-200" :class="mOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>

                    <form x-show="mOpen" x-cloak method="post" action="{{ route('courses.materials.store', $course) }}" enctype="multipart/form-data" class="mt-4 space-y-4 border-t border-slate-100 pt-4">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700">Judul Bahan Ajar *</label>
                                <input type="text" name="title" required
                                    class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700 shadow-2xs">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700">Pertemuan</label>
                                <select name="session_number" class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700 shadow-2xs">
                                    <option value="">Materi Umum / RPS</option>
                                    @for($i = 1; $i <= 16; $i++)
                                        <option value="{{ $i }}">Pertemuan {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Format *</label>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-6">
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="mType === 'document' ? 'border-teal-700 bg-teal-50 text-teal-900 ring-1 ring-teal-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="document" x-model="mType" class="sr-only">
                                    <span>📄 Dokumen</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="mType === 'audio' ? 'border-purple-600 bg-purple-50 text-purple-900 ring-1 ring-purple-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="audio" x-model="mType" class="sr-only">
                                    <span>🎧 Audio</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="mType === 'video' ? 'border-rose-600 bg-rose-50 text-rose-900 ring-1 ring-rose-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="video" x-model="mType" class="sr-only">
                                    <span>🎥 Video</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="mType === 'link' ? 'border-blue-600 bg-blue-50 text-blue-900 ring-1 ring-blue-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="link" x-model="mType" class="sr-only">
                                    <span>🔗 Tautan</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="mType === 'text' ? 'border-amber-600 bg-amber-50 text-amber-900 ring-1 ring-amber-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="text" x-model="mType" class="sr-only">
                                    <span>📝 Teks</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="mType === 'archive' ? 'border-teal-700 bg-teal-50 text-teal-900 ring-1 ring-teal-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="archive" x-model="mType" class="sr-only">
                                    <span>📦 Arsip ZIP</span>
                                </label>
                            </div>
                        </div>

                        {{-- File Input --}}
                        <div x-show="['document', 'audio', 'video', 'archive'].includes(mType)" class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                            <label class="block text-xs font-bold text-slate-800">Berkas Materi *</label>
                            <input type="file" name="file" class="mt-2 block w-full text-xs text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-teal-800 file:px-4 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-teal-700 cursor-pointer">
                        </div>

                        {{-- External Link Input --}}
                        <div x-show="mType === 'link'" x-cloak class="rounded-2xl border border-blue-200 bg-blue-50/50 p-4">
                            <label class="block text-xs font-bold text-blue-950">URL Eksternal *</label>
                            <input type="url" name="external_url"
                                class="mt-1.5 w-full rounded-xl border-blue-300 text-xs sm:text-sm focus:border-blue-600 focus:ring-blue-600 bg-white">
                        </div>

                        {{-- Direct Text Input --}}
                        <div x-show="mType === 'text'" x-cloak class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4">
                            <label class="block text-xs font-bold text-amber-950">Isi Materi Teks *</label>
                            <textarea name="content_text" rows="5"
                                class="mt-1.5 w-full rounded-xl border-amber-300 text-xs sm:text-sm focus:border-amber-600 focus:ring-amber-600 bg-white"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700">Catatan (Opsional)</label>
                            <input type="text" name="description"
                                class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700">
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="rounded-xl bg-teal-800 px-6 py-2.5 text-xs font-bold text-white hover:bg-teal-700 shadow-sm transition">
                                Simpan Bahan Ajar
                            </button>
                        </div>
                    </form>
                </div>
            @endif

