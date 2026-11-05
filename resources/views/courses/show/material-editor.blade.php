        {{-- MODAL EDIT BAHAN AJAR / DOKUMEN --}}
        @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)))
            <div x-show="editMaterialModalOpen"
                x-cloak
                style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-950/70 backdrop-blur-xs overflow-y-auto"
                @keydown.escape.window="editMaterialModalOpen = false">
                <div class="relative w-full max-w-2xl rounded-3xl border border-teal-200/90 bg-white shadow-2xl overflow-hidden flex flex-col max-h-[92vh]"
                    @click.away="editMaterialModalOpen = false">

                    {{-- Header Modal --}}
                    <div class="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-teal-900 via-slate-900 to-teal-950 text-white px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-2xl bg-teal-800 text-teal-200 border border-teal-700 flex items-center justify-center font-bold text-lg shadow-sm">
                                ✏️
                            </div>
                            <div>
                                <h3 class="text-sm sm:text-base font-extrabold text-white">Edit Bahan Ajar Perkuliahan</h3>
                                <p class="text-xs text-teal-200">Koreksi judul, pertemuan sesi, berkas materi, tautan, atau catatan mahasiswa.</p>
                            </div>
                        </div>
                        <button type="button" @click="editMaterialModalOpen = false"
                            class="rounded-xl p-1.5 text-slate-300 hover:bg-white/10 hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Form Edit --}}
                    <form :action="editMaterialData.update_url" method="post" enctype="multipart/form-data" class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-4 text-xs sm:text-sm">
                        @csrf
                        @method('PUT')

                        {{-- Judul & Penempatan Sesi --}}
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700">Judul Bahan Ajar *</label>
                                <input type="text" name="title" x-model="editMaterialData.title" required
                                    class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700 shadow-2xs font-semibold">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700">Penempatan Pertemuan</label>
                                <select name="session_number" x-model="editMaterialData.session_number"
                                    class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700 shadow-2xs">
                                    <option value="">Materi Umum / Silabus RPS</option>
                                    @for($i = 1; $i <= 16; $i++)
                                        <option value="{{ $i }}">Pertemuan Ke-{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        {{-- Format Tipe Bahan Ajar --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tipe Format Bahan Ajar *</label>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-6">
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="editMaterialData.material_type === 'document' ? 'border-teal-700 bg-teal-50 text-teal-900 ring-1 ring-teal-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="document" x-model="editMaterialData.material_type" class="sr-only">
                                    <span>📄 Dokumen</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="editMaterialData.material_type === 'audio' ? 'border-purple-600 bg-purple-50 text-purple-900 ring-1 ring-purple-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="audio" x-model="editMaterialData.material_type" class="sr-only">
                                    <span>🎧 Audio</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="editMaterialData.material_type === 'video' ? 'border-rose-600 bg-rose-50 text-rose-900 ring-1 ring-rose-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="video" x-model="editMaterialData.material_type" class="sr-only">
                                    <span>🎥 Video</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="editMaterialData.material_type === 'link' ? 'border-blue-600 bg-blue-50 text-blue-900 ring-1 ring-blue-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="link" x-model="editMaterialData.material_type" class="sr-only">
                                    <span>🔗 Tautan</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="editMaterialData.material_type === 'text' ? 'border-amber-600 bg-amber-50 text-amber-900 ring-1 ring-amber-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="text" x-model="editMaterialData.material_type" class="sr-only">
                                    <span>📝 Teks</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="editMaterialData.material_type === 'archive' ? 'border-teal-700 bg-teal-50 text-teal-900 ring-1 ring-teal-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="archive" x-model="editMaterialData.material_type" class="sr-only">
                                    <span>📦 Arsip ZIP</span>
                                </label>
                            </div>
                        </div>

                        {{-- File Upload Section (with Current File Info) --}}
                        <div x-show="['document', 'audio', 'video', 'archive'].includes(editMaterialData.material_type)" class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-2">
                            <div x-show="editMaterialData.file_name" class="flex items-center justify-between rounded-xl bg-white border border-slate-200 p-2.5 text-xs">
                                <div class="flex items-center gap-2 truncate">
                                    <span class="text-base">📁</span>
                                    <span class="font-bold text-slate-800 truncate" x-text="editMaterialData.file_name"></span>
                                    <span class="text-slate-400 font-mono text-[11px]" x-show="editMaterialData.file_size" x-text="'(' + editMaterialData.file_size + ')'"></span>
                                </div>
                                <span class="rounded bg-teal-100 text-teal-800 px-2 py-0.5 text-[10px] font-bold shrink-0">Berkas Eksisting</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-800">
                                    <span x-text="editMaterialData.file_name ? 'Ganti Berkas Materi (Opsional, Kosongkan jika berkas tetap)' : 'Unggah Berkas Materi (Maksimal 100 MB)'"></span>
                                </label>
                                <input type="file" name="file" class="mt-1.5 block w-full text-xs text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-teal-800 file:px-4 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-teal-700 cursor-pointer">
                                <p class="mt-1 text-[11px] text-slate-500">Format didukung: PDF, DOCX, PPTX, XLSX, TXT, MP3, WAV, M4A, OGG, MP4, WebM, ZIP.</p>
                            </div>
                        </div>

                        {{-- External Link Input --}}
                        <div x-show="editMaterialData.material_type === 'link'" x-cloak class="rounded-2xl border border-blue-200 bg-blue-50/50 p-4">
                            <label class="block text-xs font-bold text-blue-950">Tautan / URL Eksternal *</label>
                            <input type="url" name="external_url" x-model="editMaterialData.external_url" placeholder="https://www.youtube.com/watch?v=... atau Google Drive"
                                class="mt-1.5 w-full rounded-xl border-blue-300 text-xs sm:text-sm focus:border-blue-600 focus:ring-blue-600 bg-white">
                        </div>

                        {{-- Direct Text Input --}}
                        <div x-show="editMaterialData.material_type === 'text'" x-cloak class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4">
                            <label class="block text-xs font-bold text-amber-950">Isi Catatan / Bahan Bacaan Kuliah *</label>
                            <textarea name="content_text" x-model="editMaterialData.content_text" rows="5" placeholder="Tuliskan materi, rangkuman teori, atau instruksi perkuliahan langsung..."
                                class="mt-1.5 w-full rounded-xl border-amber-300 text-xs sm:text-sm focus:border-amber-600 focus:ring-amber-600 bg-white"></textarea>
                        </div>

                        {{-- Petunjuk Pembelajaran / Catatan Mahasiswa --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700">Petunjuk Pembelajaran / Catatan untuk Mahasiswa</label>
                            <input type="text" name="description" x-model="editMaterialData.description" placeholder="Misal: Pelajari sebelum pertemuan dimulai untuk diskusi kelompok."
                                class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700">
                        </div>

                        {{-- Footer Modal --}}
                        <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                            <button type="button" @click="editMaterialModalOpen = false"
                                class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="rounded-xl bg-teal-800 px-6 py-2.5 text-xs font-bold text-white hover:bg-teal-700 shadow-sm transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

