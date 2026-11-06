        {{-- MODAL PERIKSA JAWABAN & SUBMISI MAHASISWA --}}
        @if(auth()->user()->isStaff())
            <div x-show="submissionModalOpen"
                x-cloak
                style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-950/70 backdrop-blur-xs overflow-y-auto"
                @keydown.escape.window="submissionModalOpen = false">
                <div class="relative w-full max-w-4xl rounded-3xl border border-slate-200 bg-white shadow-2xl overflow-hidden flex flex-col max-h-[92vh]"
                    @click.away="submissionModalOpen = false">
                    
                    {{-- Header Modal --}}
                    <div class="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-teal-900 via-slate-900 to-teal-950 text-white px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-2xl bg-teal-500/20 text-teal-300 border border-teal-400/30 flex items-center justify-center font-bold text-base shadow-sm shrink-0">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm sm:text-base font-extrabold text-white" x-text="activeSub?.student_name"></h3>
                                    <span class="rounded-full bg-teal-500/20 text-teal-300 border border-teal-400/30 px-2 py-0.5 text-[10px] font-mono font-bold" x-text="activeSub?.student_nim"></span>
                                </div>
                                <p class="text-xs text-slate-300 mt-0.5">
                                    Submisi Tugas: <span class="font-bold text-white" x-text="activeSub?.assignment_title"></span>
                                    <span class="text-slate-400">·</span>
                                    <span class="text-teal-200 font-semibold" x-text="activeSub?.assignment_type_label"></span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="submissionModalOpen = false"
                                class="rounded-xl p-1.5 text-slate-300 hover:bg-white/10 hover:text-white transition">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Sub-header / Status & Revision Tabs --}}
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-3 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-bold text-slate-500">Status Submisi:</span>
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold"
                                :class="activeSub?.status === 'graded' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : (activeSub?.status === 'submitted' ? 'bg-teal-100 text-teal-800 border border-teal-200' : 'bg-amber-100 text-amber-800 border border-amber-200')"
                                x-text="activeSub?.status_label"></span>

                            <template x-if="activeSub?.score !== null && activeSub?.score !== undefined">
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-200/80 px-2.5 py-0.5 text-xs font-bold text-slate-800">
                                    Nilai: <strong class="text-teal-900" x-text="activeSub?.score"></strong>
                                    <span x-show="activeSub?.letter_grade" x-text="'(' + activeSub?.letter_grade + ')'"></span>
                                </span>
                            </template>
                        </div>

                        {{-- Riwayat Revisi --}}
                        <div x-show="activeSub?.revisions && activeSub.revisions.length > 0" class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-xs font-bold text-slate-500 mr-1">Versi Naskah:</span>
                            <template x-for="(rev, idx) in (activeSub?.revisions || [])" :key="rev.id">
                                <button type="button" @click="activeRevIndex = idx"
                                    :class="activeRevIndex === idx
                                        ? 'bg-teal-800 text-white font-bold shadow-2xs'
                                        : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                    class="inline-flex items-center gap-1 rounded-xl px-2.5 py-1 text-xs transition">
                                    <span x-text="rev.revision_number"></span>
                                    <span x-show="idx === (activeSub.revisions.length - 1)" class="rounded-full bg-teal-400 text-slate-950 px-1 text-[9px] font-extrabold uppercase">Terbaru</span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Body Modal --}}
                    <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-4">

                        {{-- Meta Info Revisi Aktif --}}
                        <div x-show="currentRevision()" class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3.5 flex flex-wrap items-center justify-between gap-3 text-xs">
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-slate-600">
                                <span>Waktu Pengumpulan: <strong class="text-slate-900" x-text="currentRevision()?.submitted_at || '-'"></strong></span>
                                <span>Jumlah Kata: <strong class="text-teal-900 font-mono" x-text="currentWordCount() + ' kata'"></strong></span>
                                <span>Karakter: <strong class="text-slate-900 font-mono" x-text="(currentRevision()?.body_text?.length || 0) + ' karakter'"></strong></span>
                            </div>

                            <div class="flex items-center gap-2">
                                <template x-if="currentRevision()?.artifact_url || activeSub?.artifact_url">
                                    <a :href="currentRevision()?.artifact_url || activeSub?.artifact_url" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 rounded-xl border border-teal-300 bg-white px-2.5 py-1 text-xs font-bold text-teal-800 hover:bg-teal-50 transition shadow-2xs">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        <span>Buka Lampiran/Artefak Eksternal</span>
                                    </a>
                                </template>

                                <button type="button" @click="copyCurrentText()"
                                    class="inline-flex items-center gap-1 rounded-xl border border-slate-300 bg-white px-2.5 py-1 text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                                    <span x-text="activeCopySuccess ? '✓ Tersalin!' : '📋 Salin Teks'"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Catatan Mahasiswa Jika Ada --}}
                        <div x-show="currentRevision()?.notes || activeSub?.notes" class="rounded-2xl border border-amber-200 bg-amber-50/70 p-3.5 text-xs text-amber-950">
                            <span class="font-extrabold uppercase tracking-wider text-[11px] text-amber-800 block mb-1">Catatan Tambahan dari Mahasiswa:</span>
                            <p class="italic text-amber-900" x-text="currentRevision()?.notes || activeSub?.notes"></p>
                        </div>

                        {{-- Jawaban Terstruktur per Bagian & CPMK (Jika Tugas Memiliki Sections) --}}
                        <div x-show="currentRevision()?.section_answers && Object.keys(currentRevision().section_answers).length > 0" class="space-y-3">
                            <span class="block text-xs font-extrabold uppercase tracking-wider text-teal-900">Jawaban per Bagian Tugas</span>
                            <div class="space-y-3">
                                <template x-for="[secKey, secAnswer] in Object.entries(currentRevision()?.section_answers || {})" :key="secKey">
                                    <div class="rounded-2xl border border-teal-200/80 bg-teal-50/30 p-4 space-y-2">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <span class="text-xs font-extrabold text-slate-900"
                                                x-text="(activeSub?.sections || []).find(s => s.key === secKey)?.title || ('Bagian ' + secKey)"></span>
                                            <template x-if="(activeSub?.sections || []).find(s => s.key === secKey)?.weight > 0">
                                                <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-[10px] font-bold text-teal-900"
                                                    x-text="'Bobot: ' + (activeSub?.sections || []).find(s => s.key === secKey)?.weight + '%'"></span>
                                            </template>
                                        </div>
                                        <template x-if="((activeSub?.sections || []).find(s => s.key === secKey)?.cpmk_outcomes || []).length > 0">
                                            <p class="text-[11px] font-semibold text-indigo-800"
                                                x-text="'CPMK: ' + ((activeSub?.sections || []).find(s => s.key === secKey)?.cpmk_outcomes || []).join(' · ')"></p>
                                        </template>
                                        <div class="rounded-xl border border-slate-200 bg-white p-3.5 text-sm font-serif text-slate-900 leading-relaxed academic-rendered-content"
                                            x-html="currentRevision()?.section_answers_html?.[secKey] || secAnswer || '-'"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Lembar Naskah Tulisan / Esai Mahasiswa --}}
                        <div x-show="currentRevision()?.body_text && (!currentRevision()?.section_answers || Object.keys(currentRevision().section_answers).length === 0)">
                            <span class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-2">Isi Naskah Jawaban</span>
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 text-sm sm:text-base font-serif text-slate-900 leading-relaxed select-text max-h-[40vh] overflow-y-auto selection:bg-teal-100 academic-rendered-content"
                                x-html="currentRevision()?.body_html || currentRevision()?.body_text"></div>
                        </div>

                        {{-- Daftar Jawaban Pertanyaan (Jika Format Ujian / Asesmen Stimulus / Hibrida) --}}
                        <details x-show="activeSub?.attempts && activeSub.attempts.length > 1" class="rounded-2xl border border-slate-200 bg-white p-4">
                            <summary class="cursor-pointer text-xs font-extrabold uppercase tracking-wider text-slate-700">Riwayat percobaan ujian</summary>
                            <div class="mt-3 space-y-3">
                                <template x-for="attempt in (activeSub?.attempts || [])" :key="attempt.attempt_number">
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                        <div class="flex flex-wrap justify-between gap-2 text-xs font-bold text-slate-700">
                                            <span x-text="'Percobaan #' + attempt.attempt_number + ' · ' + attempt.status"></span>
                                            <span x-text="(attempt.submitted_at || 'Belum dikirim') + (attempt.score !== null ? ' · Nilai ' + attempt.score : '')"></span>
                                        </div>
                                        <div class="mt-2 space-y-1.5">
                                            <template x-for="answer in (attempt.answers || [])" :key="answer.question_id">
                                                <p class="text-xs text-slate-600"><strong x-text="answer.question"></strong><span x-text="' — ' + (answer.answer || '(kosong)')"></span></p>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </details>

                        <div x-show="activeSub?.answers && activeSub.answers.length > 0" class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700">Daftar Jawaban Butir Soal (<span x-text="activeSub?.answers?.length"></span> Butir):</span>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(ans, aIdx) in (activeSub?.answers || [])" :key="ans.id">
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-2">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex items-center gap-2">
                                                <span class="h-6 w-6 rounded-lg bg-teal-50 border border-teal-200 flex items-center justify-center font-bold text-xs text-teal-800 font-mono" x-text="aIdx + 1"></span>
                                                <span class="text-xs font-bold text-slate-800" x-text="ans.question_text"></span>
                                            </div>
                                            <span class="rounded-md px-2 py-0.5 text-[10px] font-bold uppercase"
                                                :class="ans.question_type === 'mcq' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-700'"
                                                x-text="ans.question_type === 'mcq' ? 'Pilihan Ganda' : 'Esai'"></span>
                                        </div>

                                        <div class="rounded-xl bg-slate-50 p-3 text-xs">
                                            <span class="font-bold text-slate-600 block mb-1">Jawaban Mahasiswa:</span>
                                            <template x-if="ans.question_type === 'mcq'">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-mono font-bold text-slate-900" x-text="'Pilihan: ' + ans.selected_option"></span>
                                                    <template x-if="ans.is_correct !== null">
                                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                                                            :class="ans.is_correct ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                                                            x-text="ans.is_correct ? '✓ Benar' : '✗ Kurang Tepat'"></span>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="ans.question_type !== 'mcq'">
                                                <p class="whitespace-pre-wrap font-serif text-slate-800 leading-relaxed" x-text="ans.answer_text || '(Tidak diisi)'"></p>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Alert Bila Belum Ada Teks Ataupun Jawaban Butir --}}
                        <div x-show="(!currentRevision() || (!currentRevision()?.body_text && (!currentRevision()?.section_answers || Object.keys(currentRevision().section_answers).length === 0))) && (!activeSub?.answers || activeSub.answers.length === 0)"
                            class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                            <p class="text-xs text-slate-600 font-semibold">Tidak ada naskah teks tulisan langsung yang tersimpan pada submisi ini.</p>
                            <template x-if="activeSub?.artifact_url">
                                <div class="mt-3">
                                    <a :href="activeSub?.artifact_url" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-4 py-2 text-xs font-bold text-white hover:bg-teal-700 transition">
                                        <span>Buka Dokumen/Artefak Eksternal Mahasiswa &rarr;</span>
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Footer Modal: Form Penilaian Terintegrasi --}}
                    <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                        <form x-show="activeSub?.can_grade" method="post" :action="activeSub?.grade_action_url" class="space-y-3">
                            @csrf
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                <div class="w-full sm:w-36 shrink-0">
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Nilai (0 - 100) *</label>
                                    <input type="number" step="0.1" min="0" max="100" name="score" required :value="activeSub?.score"
                                        class="w-full rounded-xl border-slate-300 font-bold text-center text-sm py-2 focus:border-teal-700 focus:ring-teal-700">
                                </div>

                                <div class="flex-1">
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Catatan Evaluasi</label>
                                    <input type="text" name="feedback_notes" :value="activeSub?.feedback_notes"
                                        class="w-full rounded-xl border-slate-300 text-xs py-2 focus:border-teal-700 focus:ring-teal-700">
                                </div>

                                <div class="flex items-end gap-2 pt-1 sm:pt-4">
                                    <button type="button" @click="submissionModalOpen = false"
                                        class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                                        Tutup
                                    </button>
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-5 py-2 text-xs font-bold text-white hover:bg-teal-700 shadow-sm transition shrink-0">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Simpan Nilai</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <p x-show="activeSub && !activeSub.can_grade" class="text-sm italic text-slate-500">Draf ini belum dikirim mahasiswa, sehingga belum dapat dinilai.</p>
                    </div>
                </div>
            </div>
        @endif

