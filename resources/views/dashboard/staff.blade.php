    {{-- RINGKASAN KERJA DOSEN --}}
    <section class="mb-10" aria-labelledby="work-summary-heading">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 id="work-summary-heading" class="text-xl font-bold text-slate-900">Ringkasan Kerja Dosen</h2>
                <p class="mt-1 text-sm text-slate-600">Dihitung dari proyek aktif yang Anda miliki atau kelola sebagai editor.</p>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('outputs.index') }}" class="font-bold text-teal-800 hover:underline">
                    Portofolio BKD &rarr;
                </a>
                <a href="{{ route('projects.index') }}" class="font-bold text-[#05285c] hover:underline">
                    Lihat Semua Proyek &rarr;
                </a>
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach([
                ['Proyek Aktif', $dashboardStats['projects'], 'Wadah Tri Dharma berjalan', 'border-teal-200/80'],
                ['Tugas WriteWise', $dashboardStats['assignments'], 'Penugasan akademik aktif', 'border-blue-200/80'],
                ['Submisi Mahasiswa', $dashboardStats['submissions'], 'Draf & tugas terkumpul', 'border-amber-200/80'],
            ] as [$label, $value, $subtext, $borderClass])
                <div class="rounded-2xl border {{ $borderClass }} bg-white p-6 shadow-2xs">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $label }}</p>
                    <p class="mt-2 text-3xl font-extrabold text-[#05285c]">{{ $value }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $subtext }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- MODUL ACADEMIC HUB (KHUSUS DOSEN / STAFF) --}}
    <section aria-labelledby="modules-heading" class="mb-10 scroll-mt-24">
        <div class="mb-4">
            <h2 id="modules-heading" class="text-xl font-bold text-slate-900">Modul Ruang Kerja Tri Dharma</h2>
            <p class="mt-1 text-sm text-slate-600">Alat bantu pengajaran, sintesis riset literatur, dan diseminasi pengabdian masyarakat.</p>
        </div>
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($modules as $module)
                <article class="flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs hover:border-teal-500 hover:shadow-sm transition">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-teal-800">{{ $module['pillar'] }}</p>
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{ $module['tone'] === 'green' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/60' : 'bg-amber-50 text-amber-800 border border-amber-200/60' }}">
                                {{ $module['status'] }}
                            </span>
                        </div>
                        <h3 class="mt-3 text-lg font-bold text-slate-900">{{ $module['name'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $module['description'] }}</p>
                    </div>
                    @if($module['name'] === 'Output & Evidence')
                        <div class="mt-4 pt-3 border-t border-slate-100">
                            <a href="{{ route('outputs.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-teal-800 hover:text-teal-950">
                                <span>Buka Output &amp; Evidence</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    {{-- PROYEK TERBARU --}}
    <section class="mt-10" aria-labelledby="projects-heading">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 id="projects-heading" class="text-xl font-bold text-slate-900">Proyek Tri Dharma Terbaru</h2>
                <p class="mt-1 text-sm text-slate-600">Wadah aktivitas penelitian, pengajaran, dan pengabdian.</p>
            </div>
        </div>
        @if($projects->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
                <h3 class="text-base font-bold text-slate-800">Belum Ada Proyek Aktif</h3>
                <p class="mt-1 text-sm text-slate-500">Mulai buat proyek Tri Dharma untuk mengorganisir penugasan dan riset Anda.</p>
                <a href="{{ route('projects.create') }}" class="mt-4 inline-flex rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-800 transition">
                    Buat Proyek Baru
                </a>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($projects as $project)
                    <a href="{{ route('projects.show', $project) }}" class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs transition hover:border-teal-500 hover:shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-base font-bold text-slate-900">{{ $project->name }}</h3>
                            <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700">
                                {{ $project->owner_id === auth()->id() ? 'Pemilik' : 'Editor' }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs font-medium text-slate-500">{{ $project->assignments_count }} tugas &middot; {{ $project->members_count }} editor</p>
                        <div class="mt-3.5 flex flex-wrap gap-1.5">
                            @foreach($project->pillars as $pillar)
                                <span class="rounded-lg bg-teal-50 px-2.5 py-1 text-xs font-bold text-teal-900 border border-teal-200/60">
                                    {{ ['teaching'=>'Pengajaran', 'research'=>'Penelitian', 'community'=>'Pengabdian'][$pillar] ?? $pillar }}
                                </span>
                            @endforeach
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    {{-- TUGAS TERBARU DOSEN --}}
    <section class="mt-10" aria-labelledby="assignments-heading">
        <h2 id="assignments-heading" class="text-xl font-bold text-slate-900">Tugas Perkuliahan Terbaru</h2>
        @if($assignments->isEmpty())
            <p class="mt-3 rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-500">Belum ada tugas di proyek aktif yang Anda kelola.</p>
        @else
            <div class="mt-4 divide-y divide-slate-100 rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-2xs">
                @foreach($assignments as $assignment)
                    <a href="{{ route('assignments.show', $assignment) }}" class="flex flex-wrap items-center justify-between gap-3 p-4.5 text-sm hover:bg-slate-50/80 transition">
                        <div>
                            <span class="font-bold text-slate-900">{{ $assignment->title }}</span>
                            <span class="ml-2 text-xs text-slate-500 font-medium">{{ $assignment->project->name }}</span>
                        </div>
                        <div class="text-xs text-slate-600 font-medium">
                            <span class="font-bold text-teal-800">{{ $assignment->submissions_count }}</span> submisi dikumpulkan &middot;
                            <span class="font-bold">{{ $assignment->status === 'published' ? 'Terbit' : 'Draf' }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

{{-- TAMPILAN 2: MAHASISWA --}}
