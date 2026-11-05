        {{-- MODAL AI GENERATOR PENUGASAN (DeepSeek, Gemini, ChatGPT) --}}
        @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)))
            @php
                $aiServiceInstance = app(\App\Services\Ai\AssignmentAiService::class);
                $detectedDomainKey = $aiServiceInstance->resolveCourseDomain($course);
                $detectedDomainTitle = $aiServiceInstance->domainLabel($detectedDomainKey);
            @endphp
            <div x-show="aiModalOpen"
                x-cloak
                style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-950/70 backdrop-blur-xs overflow-y-auto"
                @keydown.escape.window="if(!aiGenerating) aiModalOpen = false">
                <div class="relative w-full max-w-3xl rounded-3xl border border-teal-200/90 bg-white shadow-2xl overflow-hidden flex flex-col max-h-[92vh]"
                    @click.away="if(!aiGenerating) aiModalOpen = false">
                    
                    {{-- Header Modal --}}
                    <div class="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-teal-900 via-slate-900 to-teal-950 text-white px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-teal-400 to-emerald-500 text-slate-950 flex items-center justify-center font-black text-lg shadow-sm animate-pulse">
                                ✨
                            </div>
                            <div>
                                <h3 class="text-sm sm:text-base font-extrabold text-white">Asisten AI Generator Penugasan</h3>
                                <p class="text-xs text-teal-200">Perancang tugas berbasis materi pertemuan aktual, CPMK kurikulum, dan domain keilmuan prodi.</p>
                            </div>
                        </div>
                        <button type="button" @click="if(!aiGenerating) aiModalOpen = false"
                            class="rounded-xl p-1.5 text-slate-300 hover:bg-white/10 hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Body Modal --}}
                    <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-5 text-xs sm:text-sm">
                        {{-- Pemilihan Provider AI --}}
                        <div class="rounded-2xl bg-slate-50 border border-slate-200 p-3.5">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Pilih AI Provider Engine:</span>
                                <span class="text-[11px] font-semibold text-slate-500">Mendukung multi-provider</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="aiProvider = 'deepseek'"
                                    :class="aiProvider === 'deepseek'
                                        ? 'border-purple-600 bg-purple-50/90 text-purple-950 ring-2 ring-purple-600 font-extrabold shadow-2xs'
                                        : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-bold'"
                                    class="rounded-xl border p-2.5 text-center transition flex flex-col items-center gap-1">
                                    <span class="text-sm">🟣 DeepSeek AI</span>
                                    <span class="text-[10px] text-purple-700 font-mono">deepseek-chat (Default)</span>
                                </button>

                                <button type="button" @click="aiProvider = 'gemini'"
                                    :class="aiProvider === 'gemini'
                                        ? 'border-teal-600 bg-teal-50/90 text-teal-950 ring-2 ring-teal-600 font-extrabold shadow-2xs'
                                        : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-bold'"
                                    class="rounded-xl border p-2.5 text-center transition flex flex-col items-center gap-1">
                                    <span class="text-sm">🔵 Google Gemini</span>
                                    <span class="text-[10px] text-teal-700 font-mono">gemini-1.5-flash</span>
                                </button>

                                <button type="button" @click="aiProvider = 'openai'"
                                    :class="aiProvider === 'openai'
                                        ? 'border-emerald-600 bg-emerald-50/90 text-emerald-950 ring-2 ring-emerald-600 font-extrabold shadow-2xs'
                                        : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-bold'"
                                    class="rounded-xl border p-2.5 text-center transition flex flex-col items-center gap-1">
                                    <span class="text-sm">🟢 ChatGPT</span>
                                    <span class="text-[10px] text-emerald-700 font-mono">gpt-4o-mini</span>
                                </button>
                            </div>
                        </div>

                        {{-- Konteks Mata Kuliah & Domain Terdeteksi --}}
                        <div class="rounded-2xl border border-teal-200/80 bg-teal-50/50 p-4 space-y-2">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2 text-teal-900 font-bold text-xs uppercase tracking-wider">
                                    <svg class="h-4 w-4 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Konteks Akademik Kelas Terdeteksi</span>
                                </div>
                                <span class="rounded-full bg-teal-800 text-white px-2.5 py-0.5 text-[10px] font-bold tracking-wide">
                                    Domain: {{ $detectedDomainTitle }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-700 flex flex-wrap items-center gap-x-4 gap-y-1">
                                <span>Mata Kuliah: <strong class="text-slate-900">{{ $course->name }} ({{ $course->code }})</strong></span>
                                <span>SKS: <strong class="text-slate-900">{{ $course->credit_hours }} SKS</strong></span>
                                <span>CPMK: <strong class="text-teal-900">{{ count($course->cpmkList()) }} Butir Terdaftar</strong></span>
                                <span>Jurnal Pertemuan: <strong class="text-slate-900">{{ $sessions->count() }} Sesi Tercatat</strong></span>
                            </div>
                        </div>

                        {{-- Form Input Parameter Dosen --}}
                        <div class="space-y-4">
                            {{-- 1. Pilihan Sesi Pertemuan (Sumber Pokok Bahasan) --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-800 mb-1">
                                    Pilih Pertemuan Perkuliahan (Sumber Materi Tugas) *
                                </label>
                                <select x-model="aiSessionId" class="w-full rounded-xl border-slate-300 text-xs sm:text-sm font-semibold focus:border-teal-700 focus:ring-teal-700 shadow-2xs">
                                    <option value="">-- Buat Penugasan Mandiri (Ketik Topik Manual) --</option>
                                    <template x-for="sess in sessionsData" :key="sess.id">
                                        <option :value="sess.id" x-text="'Pertemuan Ke-' + sess.session_number + ': ' + sess.topic"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Info Card Sesi Terpilih --}}
                            <div x-show="selectedSessionInfo()" x-cloak class="rounded-2xl border border-indigo-200 bg-indigo-50/70 p-3.5 space-y-1.5 text-xs text-indigo-950">
                                <div class="flex items-center justify-between">
                                    <span class="font-extrabold uppercase tracking-wider text-[11px] text-indigo-800"
                                        x-text="'Pertemuan Ke-' + selectedSessionInfo()?.session_number + ' (' + (selectedSessionInfo()?.date_formatted || 'Sesuai Jurnal') + ')'"></span>
                                    <span class="rounded-md bg-indigo-100 text-indigo-800 px-2 py-0.5 text-[10px] font-bold"
                                        x-text="selectedSessionInfo()?.teaching_method || 'Metode Pembelajaran'"></span>
                                </div>
                                <p class="font-bold text-slate-900 text-xs sm:text-sm" x-text="selectedSessionInfo()?.topic"></p>
                                <p class="text-[11px] text-slate-600 line-clamp-2" x-show="selectedSessionInfo()?.notes" x-text="'Catatan Jurnal: ' + selectedSessionInfo()?.notes"></p>
                            </div>

                            {{-- Input Topik Manual (Jika Tanpa Sesi) --}}
                            <div x-show="!aiSessionId">
                                <label class="block text-xs font-bold text-slate-800 mb-1">
                                    Topik / Pokok Bahasan Penugasan *
                                </label>
                                <input type="text" x-model="aiTopic" placeholder="Contoh: Analisis Kritis Koherensi Paragraf pada Artikel Jurnal Internasional"
                                    class="w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700 font-semibold shadow-2xs">
                            </div>

                            {{-- Fokus Khusus Penugasan (Opsional) --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-800 mb-1">
                                    Fokus Khusus Penugasan (Opsional)
                                </label>
                                <input type="text" x-model="aiFocus" placeholder="Contoh: Mempersempit analisis pada penggunaan connectors & transitional signals"
                                    class="w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700 shadow-2xs">
                                <p class="text-[10px] text-slate-500 mt-1">Gunakan untuk mempersempit materi pertemuan ke sub-pokok bahasan tertentu tanpa mengubah esensi topik.</p>
                            </div>

                            {{-- Format Model Tugas & Target Bloom --}}
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-bold text-slate-800 mb-1">Format Model Penugasan</label>
                                    <select x-model="aiType" class="w-full rounded-xl border-slate-300 text-xs sm:text-sm font-semibold focus:border-teal-700 focus:ring-teal-700">
                                        <option value="writing">Esai Akademik (WriteWise)</option>
                                        <option value="stimulus_exam">Ujian / Asesmen Stimulus (Audio/Passage)</option>
                                        <option value="hybrid">Asesmen Hibrida (PG + Esai)</option>
                                        <option value="translation">Penerjemahan & Review Teks</option>
                                        <option value="milestone_artifact">Proposal & Proyek Bertahap</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-800 mb-1">Target Taksonomi Bloom (Level Kognitif)</label>
                                    <select x-model="aiBloom" class="w-full rounded-xl border-slate-300 text-xs sm:text-sm font-semibold focus:border-teal-700 focus:ring-teal-700">
                                        <option value="Mengingat (C1)">C1: Mengingat (Remembering)</option>
                                        <option value="Memahami (C2)">C2: Memahami (Understanding)</option>
                                        <option value="Penerapan (C3)">C3: Penerapan (Application)</option>
                                        <option value="Analisis (C4)" selected>C4: Analisis Kritis (Analysis)</option>
                                        <option value="Evaluasi (C5)">C5: Evaluasi & Sintesis (Evaluation)</option>
                                        <option value="Kreasi (C6)">C6: Kreasi & Desain Karya (Creation)</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Rubrik Checkbox Switch --}}
                            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 flex items-center justify-between">
                                <div>
                                    <span class="block text-xs font-bold text-slate-800">Sertakan Rubrik Penilaian Terukur</span>
                                    <span class="block text-[11px] text-slate-500">AI merumuskan kriteria capaian dengan total bobot persentase tepat 100%.</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="aiIncludeRubric" class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-teal-700"></div>
                                </label>
                            </div>

                            {{-- Catatan Dosen --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-800 mb-1">
                                    Catatan Tambahan / Arahan Khusus Dosen (Opsional)
                                </label>
                                <textarea x-model="aiNotes" rows="2" placeholder="Masukkan arahan khusus dosen jika ada (misal: batasan jumlah kata, format berkas, atau fokus analisis khusus). Kosongkan jika tidak ada."
                                    class="w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700 shadow-2xs"></textarea>
                            </div>
                        </div>

                        {{-- Tombol Generate --}}
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-xs text-slate-500">AI menyusun instruksi operasional sesuai parameter & materi kelas.</span>
                            <button type="button" @click="generateAssignmentWithAi()" :disabled="aiGenerating"
                                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-teal-800 via-teal-700 to-emerald-700 px-6 py-2.5 text-xs font-bold text-white hover:opacity-95 shadow-md transition disabled:opacity-50 cursor-pointer">
                                <span x-show="!aiGenerating">⚡ Generate Draf Tugas</span>
                                <span x-show="aiGenerating" class="flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>AI Sedang Merumuskan...</span>
                                </span>
                            </button>
                        </div>

                        {{-- Error Alert --}}
                        <div x-show="aiError" x-cloak class="rounded-2xl border border-rose-300 bg-rose-50 p-4 text-xs text-rose-900 shadow-2xs flex items-start gap-3">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-rose-600 font-bold text-white text-xs">!</span>
                            <div class="flex-1">
                                <p class="font-bold">Gagal Menghasilkan Rancangan Tugas:</p>
                                <p class="mt-0.5 leading-relaxed" x-text="aiError"></p>
                            </div>
                        </div>

                        {{-- Preview Hasil AI Generator --}}
                        <div x-show="aiResult" x-cloak class="space-y-4 border-t border-slate-200 pt-5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-teal-900 flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full bg-teal-600 animate-pulse"></span>
                                    Pratinjau Rancangan Tugas AI
                                </span>
                                <span class="rounded-full bg-teal-100 px-3 py-0.5 text-[11px] font-bold text-teal-950 font-mono" x-text="'Engine: ' + aiProvider"></span>
                            </div>

                            {{-- Judul Hasil --}}
                            <div class="rounded-2xl bg-teal-50/70 border border-teal-200 p-4">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-teal-800">Judul Tugas yang Dirumuskan:</span>
                                    <span class="rounded-md bg-white border border-teal-200 px-2 py-0.5 text-[10px] font-bold text-teal-900 uppercase" x-text="aiResult?.assignment_type"></span>
                                </div>
                                <h4 class="text-base font-extrabold text-slate-900" x-text="aiResult?.title"></h4>
                                <p class="text-xs text-slate-600 mt-1" x-text="aiResult?.summary"></p>
                            </div>

                            {{-- Instruksi Langkah demi Langkah --}}
                            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 block mb-2">Instruksi Pengerjaan Langkah demi Langkah:</span>
                                <div class="text-xs leading-relaxed text-slate-800 whitespace-pre-wrap max-h-64 overflow-y-auto font-sans" x-text="aiResult?.instructions"></div>
                            </div>

                            {{-- Rubrik Penilaian Berkriteria --}}
                            <div x-show="aiResult?.rubric && aiResult?.rubric.length > 0" class="rounded-2xl border border-slate-200 overflow-hidden">
                                <div class="bg-slate-100 px-4 py-2 border-b border-slate-200 flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Kriteria Rubrik Penilaian:</span>
                                    <span class="text-[11px] font-bold text-teal-800">Total Bobot: 100%</span>
                                </div>
                                <div class="divide-y divide-slate-100 text-xs">
                                    <template x-for="(item, idx) in (aiResult?.rubric || [])" :key="idx">
                                        <div class="p-3 bg-white flex items-start justify-between gap-3">
                                            <div>
                                                <span class="font-bold text-slate-900" x-text="(idx + 1) + '. ' + item.criterion"></span>
                                                <p class="text-slate-500 text-[11px] mt-0.5" x-text="item.description"></p>
                                            </div>
                                            <span class="rounded-lg bg-teal-50 border border-teal-200 px-2.5 py-1 text-xs font-extrabold text-teal-900 font-mono shrink-0"
                                                x-text="item.weight_percentage + '%'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Modal --}}
                    <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-6 py-4">
                        <button type="button" @click="if(!aiGenerating) aiModalOpen = false"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                            Tutup
                        </button>

                        <div class="flex items-center gap-2">
                            <button x-show="aiResult" x-cloak type="button" @click="generateAssignmentWithAi()" :disabled="aiGenerating"
                                class="rounded-xl border border-teal-300 bg-white px-4 py-2 text-xs font-bold text-teal-800 hover:bg-teal-50 transition">
                                🔄 Generate Ulang
                            </button>

                            <button x-show="aiResult" x-cloak type="button" @click="applyAiToForm()"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-5 py-2 text-xs font-bold text-white hover:bg-teal-700 shadow-sm transition">
                                <span>✅ Terapkan ke Formulir Tugas</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

