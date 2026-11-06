        <div>
            <h2 class="text-xl font-bold text-slate-900">Riwayat Pengumpulan &amp; Evaluasi</h2>
            @forelse($assignment->submissions as $submission)
                <div class="mt-4 space-y-4">
                    @foreach($submission->revisions as $index => $revision)
                        <article class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 space-y-3 shadow-2xs">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                        <span>{{ $revision->revision_number === 1 ? 'Pengumpulan Awal (Naskah Pertama)' : 'Revisi ' . ($revision->revision_number - 1) }}</span>
                                        @if($loop->last && $submission->revisions->count() > 1)
                                            <span class="rounded-full bg-teal-100 text-teal-800 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider">Versi Terbaru</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-500 mt-0.5 font-medium">
                                        Dikumpulkan pada {{ $revision->submitted_at ? $revision->submitted_at->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-' }}
                                    </p>
                                </div>
                                @if($revision->milestone_id)
                                    <span class="rounded-full bg-rose-100 text-rose-800 px-2.5 py-0.5 text-xs font-bold">
                                        {{ $revision->milestoneTitle() }}
                                    </span>
                                @endif
                            </div>

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

                            <details class="mt-2">
                                <summary class="cursor-pointer text-sm font-semibold text-teal-800 hover:text-teal-950">Lihat isi naskah yang dikumpulkan &darr;</summary>
                                @if(!empty($revision->section_answers))
                                    <div class="mt-2 space-y-3">
                                        @foreach($revision->section_answers as $answerKey => $answer)
                                            @php
                                                $answerSection = collect($assignment->config_json['sections'] ?? [])->firstWhere('key', $answerKey);
                                            @endphp
                                            <div class="rounded-xl bg-slate-50 p-3">
                                                <p class="text-xs font-bold text-slate-700">{{ $answerSection['title'] ?? 'Bagian tugas' }}</p>
                                                <div class="mt-1 font-serif text-sm text-slate-700 leading-relaxed academic-rendered-content">{!! \App\Models\WritingRevision::renderSafeMarkdown((string) $answer) !!}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mt-2 font-serif text-sm sm:text-base leading-relaxed text-slate-700 academic-rendered-content">{!! $revision->renderedBodyHtml() !!}</div>
                                @endif
                            </details>

                            @if($index > 0)
                                @php
                                    $previousRevision = $submission->revisions[$index - 1];
                                @endphp
                                <details class="mt-4 rounded-xl border border-slate-200 p-3.5">
                                    <summary class="cursor-pointer text-sm font-semibold text-slate-800">Bandingkan {{ $previousRevision->revision_number === 1 ? 'Naskah Awal' : 'Revisi ' . ($previousRevision->revision_number - 1) }} dengan {{ $revision->revision_number === 1 ? 'Naskah Awal' : 'Revisi ' . ($revision->revision_number - 1) }}</summary>
                                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                                        <div class="rounded-xl bg-slate-50 p-3.5">
                                            <p class="mb-2 text-xs font-semibold uppercase text-slate-500">Sebelumnya · {{ $previousRevision->revision_number === 1 ? 'Naskah Awal' : 'Revisi ' . ($previousRevision->revision_number - 1) }}</p>
                                            <div class="font-serif text-sm leading-relaxed academic-rendered-content">{!! $previousRevision->renderedBodyHtml() !!}</div>
                                        </div>
                                        <div class="rounded-xl bg-teal-50 p-3.5">
                                            <p class="mb-2 text-xs font-semibold uppercase text-teal-800">Terbaru · {{ $revision->revision_number === 1 ? 'Naskah Awal' : 'Revisi ' . ($revision->revision_number - 1) }}</p>
                                            <div class="font-serif text-sm leading-relaxed academic-rendered-content">{!! $revision->renderedBodyHtml() !!}</div>
                                        </div>
                                    </div>
                                </details>
                            @endif

                            @foreach($revision->feedbackReports->where('status', 'published') as $report)
                                <div class="mt-4 rounded-2xl border border-teal-200 bg-teal-50/70 p-4 sm:p-5 space-y-3">
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
                                                <span class="rounded-md bg-teal-100 px-1.5 py-0.5 text-[10px] font-bold text-teal-900">Mutu {{ $submission->letterGrade() }}</span>
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
                        </article>
                    @endforeach
                </div>
            @empty
                <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-600">
                    Belum ada jawaban tugas yang dikumpulkan. Kirim tulisan pertama Anda melalui formulir di samping.
                </div>
            @endforelse
        </div>