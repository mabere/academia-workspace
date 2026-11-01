@extends('layouts.hub')
@section('title', 'Academia Workspace — Ruang Kerja Tri Dharma Dosen & Pembelajaran')
@section('content')
<div class="space-y-12 py-4 sm:py-8">

    {{-- HERO SECTION MODERN ONLINE COURSE & TRI DHARMA --}}
    <section class="relative overflow-hidden rounded-3xl border border-slate-200/90 bg-white p-7 sm:p-12 lg:p-14 shadow-sm">
        <div class="pointer-events-none absolute -top-24 -right-24 h-80 w-80 rounded-full bg-teal-500/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-[#05285c]/10 blur-3xl"></div>

        <div class="relative grid gap-10 lg:grid-cols-12 lg:items-center">
            <div class="lg:col-span-7">
                {{-- Badge --}}
                <span class="inline-flex items-center gap-2 rounded-full border border-teal-200 bg-teal-50/80 px-3.5 py-1.5 text-xs font-bold tracking-wide text-teal-900">
                    <span class="h-2 w-2 rounded-full bg-teal-600 animate-pulse"></span>
                    <span>Ruang Kerja Mandiri Dosen · Online Course &amp; BKD</span>
                </span>

                {{-- Headline --}}
                <h1 class="mt-5 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl lg:text-5xl leading-[1.15]">
                    Satu Ruang Kerja Akademik untuk <span class="text-[#05285c]">Perkuliahan</span> &amp; <span class="text-[#00a98f]">Tri Dharma</span> Dosen.
                </h1>

                {{-- Deskripsi Singkat --}}
                <p class="mt-5 text-base sm:text-lg leading-relaxed text-slate-600">
                    Kelola kelas perkuliahan interaktif, bahan ajar multi-format (PDF, Office, Audio &amp; Video), asesmen esai &amp; ujian stimulus, sintesis literatur riset, modul pengabdian, serta pelaporan <strong>Beban Kerja Dosen (BKD)</strong> dalam satu ekosistem modern.
                </p>

                {{-- Aksi Cepat --}}
                <div class="mt-8 flex flex-wrap items-center gap-3.5">
                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#05285c] px-6 py-3.5 text-sm font-bold text-white shadow-sm hover:bg-[#041e45] transition">
                            <span>Buka Dashboard</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                        <a href="{{ route('courses.index') }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-6 py-3.5 text-sm font-bold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-slate-900 transition">
                            <svg class="h-4 w-4 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Mata Kuliah &amp; Perkuliahan</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#05285c] px-6 py-3.5 text-sm font-bold text-white shadow-sm hover:bg-[#041e45] transition">
                            <span>Masuk ke Workspace</span>
                            <svg class="h-4 w-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                        </a>
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-6 py-3.5 text-sm font-bold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-slate-900 transition">
                            <span>Daftar</span>
                        </a>
                    @endauth
                </div>
            </div>

            {{-- Preview Card Online Course Highlights --}}
            <div class="lg:col-span-5">
                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-5 sm:p-6 shadow-inner space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200/80 pb-3.5">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#05285c] text-white font-bold text-xs">ELT</span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-teal-800">Learning &amp; Research Hub</p>
                                <p class="text-sm font-bold text-slate-900">Arsitektur Kelas &amp; Kinerja Terpadu</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">Aktif</span>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-2xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-900">Ruang Kuliah Multi-Format</span>
                                <span class="rounded-md bg-teal-50 px-2 py-0.5 text-xs font-bold text-teal-800">16 Sesi</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-600 leading-relaxed">Streaming materi PDF/Office/Audio/Video, presensi 4-digit real-time, dan cetak BAP sah A4.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-2xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-900">Asesmen Multimoda &amp; WriteWise</span>
                                <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-bold text-indigo-800">5 Format</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-600 leading-relaxed">Esai akademik, ujian listening berbatas putar, penerjemahan ST/TT berdampingan, dan proposal bertahap.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-2xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-900">Rekapitulasi PO BKD Otomatis</span>
                                <span class="rounded-md bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-800">≥ 12 SKS</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-600 leading-relaxed">Seluruh aktivitas pengajaran, riset, dan pengabdian terhitung otomatis ke portofolio BKD semester.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 4 BAGIAN INTI APLIKASI (RINGKAS & MUDAH DIBACA) --}}
    <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {{-- MODUL 1: PENGAJARAN --}}
        <article class="flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs hover:border-teal-500 hover:shadow-md transition">
            <div>
                <div class="flex items-center justify-between">
                    <span class="rounded-xl bg-teal-100 p-2.5 text-teal-800">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pilar I</span>
                </div>
                <h2 class="mt-4 text-lg font-bold text-slate-900">Pendidikan &amp; Perkuliahan</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">
                    Manajemen mata kuliah, plotting mahasiswa, jurnal 16 pertemuan &amp; cetak BAP, streaming materi PDF/Office, serta penugasan esai WriteWise.
                </p>
            </div>
            <div class="mt-5 pt-3.5 border-t border-slate-100 text-xs font-bold text-teal-800">
                Kelas, Bahan Ajar &amp; DPNA
            </div>
        </article>

        {{-- MODUL 2: PENELITIAN --}}
        <article class="flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs hover:border-blue-500 hover:shadow-md transition">
            <div>
                <div class="flex items-center justify-between">
                    <span class="rounded-xl bg-blue-100 p-2.5 text-blue-800">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pilar II</span>
                </div>
                <h2 class="mt-4 text-lg font-bold text-slate-900">Penelitian &amp; Publikasi</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">
                    Sintesis literatur LitSynth berjejak halaman bukti, validasi instrumen riset bersama pakar independen via token aman, dan korpus CorpusLab.
                </p>
            </div>
            <div class="mt-5 pt-3.5 border-t border-slate-100 text-xs font-bold text-blue-800">
                LitSynth &amp; Validasi Pakar
            </div>
        </article>

        {{-- MODUL 3: PENGABDIAN --}}
        <article class="flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs hover:border-purple-500 hover:shadow-md transition">
            <div>
                <div class="flex items-center justify-between">
                    <span class="rounded-xl bg-purple-100 p-2.5 text-purple-800">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pilar III</span>
                </div>
                <h2 class="mt-4 text-lg font-bold text-slate-900">Pengabdian Masyarakat</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">
                    GuruBahasa Studio untuk penyusunan materi ajar kontekstual, perangkat lokakarya guru/MGMP, dan alur kurasi bahan pendampingan sekolah.
                </p>
            </div>
            <div class="mt-5 pt-3.5 border-t border-slate-100 text-xs font-bold text-purple-800">
                GuruBahasa &amp; Kurasi Modul
            </div>
        </article>

        {{-- MODUL 4: BKD --}}
        <article class="flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs hover:border-amber-500 hover:shadow-md transition">
            <div>
                <div class="flex items-center justify-between">
                    <span class="rounded-xl bg-amber-100 p-2.5 text-amber-800">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pilar IV</span>
                </div>
                <h2 class="mt-4 text-lg font-bold text-slate-900">Beban Kerja Dosen (BKD)</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">
                    Kalkulasi otomatis SKS pengajaran dari mata kuliah aktif dan ekspor Lembar Evaluasi BKD resmi lengkap dengan eviden luaran Tri Dharma.
                </p>
            </div>
            <div class="mt-5 pt-3.5 border-t border-slate-100 text-xs font-bold text-amber-800">
                1-Klik Ekspor Lembar BKD
            </div>
        </article>
    </section>

    {{-- CATATAN KEDAULATAN AKADEMIK --}}
    <div class="rounded-2xl border border-slate-200/90 bg-white p-5 text-sm text-slate-600 shadow-2xs">
        <p class="leading-relaxed">
            <strong class="text-slate-900">Prinsip Ruang Kerja Akademik:</strong> Bantuan kecerdasan buatan berfungsi sebagai perumus draf awal. Kendali kurasi materi, bimbingan, penilaian mahasiswa, dan keputusan akademik sepenuhnya berada di tangan dosen.
        </p>
    </div>

</div>
@endsection