        <div>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Riwayat Pengumpulan &amp; Evaluasi</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar naskah dan catatan evaluasi dari dosen pengampu.</p>
                </div>
                @if($assignment->submissions->isNotEmpty() && $assignment->submissions->first()->revisions->count() > 1)
                    <span class="rounded-full bg-slate-100 text-slate-700 border border-slate-200 px-2.5 py-1 text-xs font-semibold">
                        {{ $assignment->submissions->first()->revisions->count() }} Naskah
                    </span>
                @endif
            </div>

            @forelse($assignment->submissions as $submission)
                <div class="mt-4 space-y-3">
                    @foreach($submission->revisions as $index => $revision)
                        <div x-data="{ open: @js($loop->last) }"
                            class="rounded-2xl border border-slate-200 bg-white shadow-2xs overflow-hidden transition">
                            <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between p-4 sm:p-5 text-left hover:bg-slate-50/80 transition focus:outline-none">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl font-bold text-xs shadow-2xs
                                        {{ $loop->last ? 'bg-teal-700 text-white' : 'bg-slate-200 text-slate-700' }}">
                                        #{{ $revision->revision_number }}
                                    </span>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-bold text-slate-900">
                                                {{ $revision->revision_number === 1 ? 'Pengumpulan Awal (Naskah Pertama)' : 'Revisi ' . ($revision->revision_number - 1) }}
                                            </span>
                                            @if($loop->last && $submission->revisions->count() > 1)
                                                <span class="rounded-full bg-teal-100 text-teal-800 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider">Versi Terbaru</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5 font-medium">
                                            Dikumpulkan pada {{ $revision->submitted_at ? $revision->submitted_at->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2.5 sm:gap-3">
                                    @if($revision->milestone_id)
                                        <span class="hidden sm:inline-flex rounded-full bg-rose-100 text-rose-800 px-2.5 py-0.5 text-xs font-bold">
                                            {{ $revision->milestoneTitle() }}
                                        </span>
                                    @endif

                                    @php
                                        $publishedReport = $revision->feedbackReports->where('status', 'published')->first();
                                        $revScore = $publishedReport?->feedback_json['score'] ?? null;
                                    @endphp
                                    @if($revScore !== null)
                                        <div class="flex items-center gap-1.5 rounded-xl border border-teal-200 bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-900">
                                            <span class="text-[11px] text-slate-500 font-normal">Nilai:</span>
                                            <span class="font-black text-sm text-teal-950">{{ number_format($revScore, 1) }}</span>
                                            <span class="rounded bg-teal-200/80 px-1 py-0.5 text-[10px] font-bold text-teal-950">
                                                Mutu {{ \App\Models\WritingSubmission::gradeToLetter((float) $revScore) }}
                                            </span>
                                        </div>
                                    @endif

                                    <svg class="h-5 w-5 text-slate-400 transition-transform duration-200 shrink-0"
                                        :class="{ 'rotate-180': open }"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </button>

                            <div x-show="open" class="border-t border-slate-100 p-5 sm:p-6 space-y-4">
                                @if($revision->milestone_id)
                                    <div class="sm:hidden">
                                        <span class="rounded-full bg-rose-100 text-rose-800 px-2.5 py-0.5 text-xs font-bold">
                                            {{ $revision->milestoneTitle() }}
                                        </span>
                                    </div>
                                @endif

                                @if($revision->artifact_url)
                                    <div class="flex items-center gap-2 rounded-xl bg-slate-50 p-3 border border-slate-200">
                                        <span class="text-xs font-bold text-slate-700">Tautan Artefak Digital:</span>
                                        <a href="{{ $revision->artifact_url }}" target="_blank" rel="noopener noreferrer"
                                            class="text-xs font-bold text-teal-800 underline hover:text-teal-900 truncate">
                                            {{ $revision->artifact_url }} &nearr;
                                        </a>
                                    </div>
                                @endif

                                @if($revision->notes)
                                    <details class="rounded-xl bg-slate-50 p-3 border border-slate-200">
                                        <summary class="cursor-pointer text-xs font-semibold text-slate-800">
                                            {{ $assignment->isTranslation() ? 'Catatan Padanan & Strategi Penerjemahan' : 'Catatan / Refleksi Mahasiswa' }}
                                        </summary>
                                        <p class="mt-1.5 text-xs sm:text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $revision->notes }}</p>
                                    </details>
                                @endif

                                <details class="rounded-xl border border-slate-200 p-3.5 bg-slate-50/40">
                                    <summary class="cursor-pointer text-sm font-semibold text-teal-800 hover:text-teal-950">Lihat isi naskah yang dikumpulkan &darr;</summary>
                                    <div class="mt-3 pt-3 border-t border-slate-200">
                                        @if(!empty($revision->section_answers))
                                            <div class="space-y-3">
                                                @foreach($revision->section_answers as $answerKey => $answer)
                                                    @php
                                                        $answerSection = collect($assignment->config_json['sections'] ?? [])->firstWhere('key', $answerKey);
                                                    @endphp
                                                    <div class="rounded-xl bg-white p-3 border border-slate-200 shadow-2xs">
                                                        <p class="text-xs font-bold text-slate-700">{{ $answerSection['title'] ?? 'Bagian tugas' }}</p>
                                                        <div class="mt-1 font-serif text-sm text-slate-700 leading-relaxed academic-rendered-content">{!! \App\Models\WritingRevision::renderSafeMarkdown((string) $answer) !!}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="font-serif text-sm sm:text-base leading-relaxed text-slate-700 academic-rendered-content">{!! $revision->renderedBodyHtml() !!}</div>
                                        @endif
                                    </div>
                                </details>

                                @if($index > 0)
                                    @php
                                        $previousRevision = $submission->revisions[$index - 1];
                                    @endphp
                                    <details class="rounded-xl border border-slate-200 p-3.5 bg-slate-50/40">
                                        <summary class="cursor-pointer text-sm font-semibold text-slate-800">Bandingkan {{ $previousRevision->revision_number === 1 ? 'Naskah Awal' : 'Revisi ' . ($previousRevision->revision_number - 1) }} dengan {{ $revision->revision_number === 1 ? 'Naskah Awal' : 'Revisi ' . ($revision->revision_number - 1) }}</summary>
                                        <div class="mt-3 grid gap-3 md:grid-cols-2 pt-3 border-t border-slate-200">
                                            <div class="rounded-xl bg-white p-3.5 border border-slate-200 shadow-2xs">
                                                <p class="mb-2 text-xs font-semibold uppercase text-slate-500">Sebelumnya · {{ $previousRevision->revision_number === 1 ? 'Naskah Awal' : 'Revisi ' . ($previousRevision->revision_number - 1) }}</p>
                                                <div class="font-serif text-sm leading-relaxed academic-rendered-content">{!! $previousRevision->renderedBodyHtml() !!}</div>
                                            </div>
                                            <div class="rounded-xl bg-teal-50 p-3.5 border border-teal-200 shadow-2xs">
                                                <p class="mb-2 text-xs font-semibold uppercase text-teal-800">Terbaru · {{ $revision->revision_number === 1 ? 'Naskah Awal' : 'Revisi ' . ($revision->revision_number - 1) }}</p>
                                                <div class="font-serif text-sm leading-relaxed academic-rendered-content">{!! $revision->renderedBodyHtml() !!}</div>
                                            </div>
                                        </div>
                                    </details>
                                @endif

                                @foreach($revision->feedbackReports->where('status', 'published') as $report)
                                    <div class="rounded-2xl border border-teal-200 bg-teal-50/70 p-4 sm:p-5 space-y-3">
                                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-teal-200/60 pb-3">
                                            <div class="flex items-center gap-2">
                                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-teal-800 text-white font-bold text-xs shadow-2xs">✍️</span>
                                                <p class="text-xs font-bold uppercase tracking-wider text-teal-900">Evaluasi &amp; Catatan Dosen Pengampu</p>
                                            </div>
                                            @php
                                                $feedbackScore = $report->feedback_json['score'] ?? $submission->score;
                                            @endphp
                                            @if($feedbackScore !== null)
                                                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-teal-200 shadow-2xs">
                                                    <span class="text-xs text-slate-600 font-semibold">Nilai Diberikan:</span>
                                                    <span class="text-base font-black text-teal-900">{{ number_format($feedbackScore, 1) }}</span>
                                                    <span class="rounded-md bg-teal-100 px-1.5 py-0.5 text-[10px] font-bold text-teal-900">Mutu {{ \App\Models\WritingSubmission::gradeToLetter($feedbackScore) }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        @if(!empty($report->feedback_json['overall_summary']))
                                            <div>
                                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-1">Catatan &amp; Ulasan:</span>
                                                <p class="whitespace-pre-line text-sm leading-relaxed text-slate-800 bg-white/70 p-3 rounded-xl border border-teal-100 font-sans">{{ $report->feedback_json['overall_summary'] }}</p>
                                            </div>
                                        @endif

                                        <div class="flex flex-wrap items-center justify-between gap-2 pt-1 text-[11px] text-teal-800 border-t border-teal-100/80">
                                            <span>Penilai: <strong>{{ $report->feedback_json['evaluated_by'] ?? ($assignment->course?->lecturer?->name ?? 'Dosen Pengampu') }}</strong></span>
                                            <span>Diterbitkan: {{ $report->published_at ? $report->published_at->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-' }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-600">
                    Belum ada jawaban tugas yang dikumpulkan. Kirim tulisan pertama Anda melalui formulir di samping.
                </div>
            @endforelse

            {{-- Kartu Ringkas Status Submisi & Nilai Mahasiswa --}}
            @if ($mySubmission)
                <div class="mt-4 rounded-2xl border {{ $mySubmission->status === 'graded' ? 'border-emerald-300 bg-emerald-50/50' : ($mySubmission->status === 'submitted' ? 'border-teal-200 bg-teal-50/40' : 'border-amber-200 bg-amber-50/40') }} px-4 py-3.5 shadow-2xs flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="h-9 w-9 rounded-xl flex items-center justify-center font-bold text-sm shadow-2xs shrink-0
                            {{ $mySubmission->status === 'graded' ? 'bg-emerald-600 text-white' : ($mySubmission->status === 'submitted' ? 'bg-teal-700 text-white' : 'bg-amber-600 text-white') }}">
                            @if ($mySubmission->status === 'graded')
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            @elseif($mySubmission->status === 'submitted')
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @else
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs sm:text-sm font-extrabold text-slate-900">
                                    @if ($mySubmission->status === 'graded')
                                        Sudah Dinilai Dosen
                                    @elseif($mySubmission->status === 'submitted')
                                        ⏳ Menunggu Penilaian Dosen
                                    @else
                                        ✍️ Draf Belum Dikirim
                                    @endif
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border
                                    {{ $mySubmission->status === 'graded' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : ($mySubmission->status === 'submitted' ? 'bg-teal-100 text-teal-800 border-teal-300' : 'bg-amber-100 text-amber-800 border-amber-300') }}">
                                    {{ $mySubmission->status === 'graded' ? 'Selesai' : ($mySubmission->status === 'submitted' ? 'Terkirim' : 'Draf') }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                @if ($mySubmission->status === 'graded' && $mySubmission->graded_at)
                                    Dinilai {{ $mySubmission->graded_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                @elseif ($mySubmission->submitted_at)
                                    Dikumpulkan {{ $mySubmission->submitted_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                @else
                                    Simpan atau kirim tugas melalui formulir
                                @endif
                            </p>
                        </div>
                    </div>

                    @if ($mySubmission->score !== null)
                        <div class="flex items-center gap-3 rounded-xl bg-white border border-emerald-200 px-3.5 py-1.5 shadow-2xs shrink-0">
                            <div class="text-right">
                                <span class="block text-[9px] font-bold uppercase tracking-wider text-slate-400">Nilai</span>
                                <span class="text-lg font-black text-teal-900 leading-none">{{ number_format($mySubmission->score, 1) }}</span>
                            </div>
                            <div class="h-6 w-px bg-slate-200"></div>
                            <div class="text-center">
                                <span class="block text-[9px] font-bold uppercase tracking-wider text-slate-400">Mutu</span>
                                <span class="text-base font-black text-emerald-700 leading-none">{{ $mySubmission->letterGrade() }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>