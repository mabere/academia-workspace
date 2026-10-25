@extends('layouts.hub')
@section('title', 'Dashboard — ELT Academic Hub')
@section('content')
@php
    $roleLabels = ['lecturer' => 'Dosen', 'admin' => 'Administrator', 'superadmin' => 'Superadmin', 'student' => 'Mahasiswa', 'public' => 'Pengguna umum'];
    $modules = [
        ['pillar' => 'Pendidikan & Pengajaran', 'name' => 'WriteWise ELT', 'description' => 'Tugas menulis, revisi mahasiswa, dan feedback dosen.', 'status' => 'Pilot tersedia', 'tone' => 'green'],
        ['pillar' => 'Penelitian', 'name' => 'LitSynth ELT', 'description' => 'Sumber privat, matriks kajian, dan blueprint instrumen.', 'status' => 'Tersedia · lokal', 'tone' => 'green'],
        ['pillar' => 'Penelitian', 'name' => 'Validasi pakar LitSynth', 'description' => 'Undangan per versi, respons ahli, dan ringkasan Aiken’s V/CVI.', 'status' => 'Tersedia', 'tone' => 'green'],
        ['pillar' => 'Pengabdian', 'name' => 'GuruBahasa AI', 'description' => 'Studio draf dan kurasi materi bahasa.', 'status' => 'Manual tersedia · AI nonaktif', 'tone' => 'amber'],
        ['pillar' => 'Lintas pilar', 'name' => 'Output & Evidence', 'description' => 'Temukan, pratinjau, dan unduh artefak modul aktif sesuai akses proyek.', 'status' => 'Tersedia untuk staf', 'tone' => 'green'],
        ['pillar' => 'Penelitian', 'name' => 'IndoEFL CorpusLab', 'description' => 'Korpus lokal dengan gerbang protokol etik, persetujuan, anonimisasi, anotasi, KWIC, dan ekspor terbatas.', 'status' => 'Tersedia setelah protokol disetujui', 'tone' => 'amber'],
    ];
@endphp

<section class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div><p class="text-sm font-medium text-teal-800">Ruang kerja Tri Dharma</p><h1 class="mt-1 text-3xl font-bold tracking-tight">Halo, {{ auth()->user()->name }}</h1><p class="mt-2 text-slate-600">{{ $roleLabels[auth()->user()->role] ?? 'Pengguna' }} · Ringkasan aktivitas akun Anda.</p></div>
    @if(auth()->user()->isStaff())<a href="{{ route('projects.create') }}" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">Buat proyek</a>@endif
</section>

<section aria-labelledby="modules-heading">
    <div class="mb-4"><h2 id="modules-heading" class="text-xl font-bold">Modul Academic Hub</h2><p class="mt-1 text-sm text-slate-600">Status menandai fungsi yang tersedia saat ini dan rencana pengembangan.</p></div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($modules as $module)
            <article class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-teal-800">{{ $module['pillar'] }}</p><h3 class="mt-2 text-lg font-bold">{{ $module['name'] }}</h3><p class="mt-2 min-h-12 text-sm leading-6 text-slate-600">{{ $module['description'] }}</p>
                <span class="mt-4 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $module['tone'] === 'green' ? 'bg-emerald-50 text-emerald-800' : ($module['tone'] === 'amber' ? 'bg-amber-50 text-amber-800' : 'bg-slate-100 text-slate-700') }}">{{ $module['status'] }}</span>
                @if($module['name'] === 'WriteWise ELT')<p class="mt-3 text-xs text-slate-500">Feedback AI nonaktif; feedback manual dosen tersedia.</p>@endif
                @if($module['name'] === 'GuruBahasa AI')<p class="mt-3 text-xs text-slate-500">Materi disusun dan ditinjau manusia. Target CEFR adalah perkiraan, bukan sertifikasi.</p>@endif
                @if($module['name'] === 'IndoEFL CorpusLab')<p class="mt-3 text-xs text-slate-500">Tidak menerima data sampai metadata persetujuan etik direview. Teks tidak dikirim ke layanan eksternal.</p>@endif
                @if($module['name'] === 'Output & Evidence' && auth()->user()->isStaff())<a href="{{ route('outputs.index') }}" class="mt-3 inline-flex text-sm font-semibold text-teal-800 hover:underline">Buka Output & Evidence →</a>@endif
            </article>
        @endforeach
    </div>
</section>

@if(auth()->user()->isStaff())
    <section class="mt-10" aria-labelledby="work-summary-heading">
        <div class="mb-4 flex items-end justify-between gap-4"><div><h2 id="work-summary-heading" class="text-xl font-bold">Ringkasan kerja</h2><p class="mt-1 text-sm text-slate-600">Dihitung dari proyek aktif yang Anda miliki atau kelola sebagai editor.</p></div><a href="{{ route('projects.index') }}" class="text-sm font-semibold text-teal-800 hover:underline">Lihat semua proyek</a></div>
        <div class="grid gap-3 sm:grid-cols-3">
            @foreach([['Proyek aktif', $dashboardStats['projects']], ['Tugas WriteWise', $dashboardStats['assignments']], ['Submission', $dashboardStats['submissions']]] as [$label, $value])<div class="rounded-xl border bg-white p-5"><p class="text-sm text-slate-600">{{ $label }}</p><p class="mt-1 text-3xl font-bold">{{ $value }}</p></div>@endforeach
        </div>
    </section>
    <section class="mt-9" aria-labelledby="projects-heading">
        <div class="mb-4 flex items-center justify-between"><div><h2 id="projects-heading" class="text-xl font-bold">Proyek terbaru</h2><p class="mt-1 text-sm text-slate-600">Proyek aktif milik Anda dan proyek yang membagikan akses editor.</p></div></div>
        @if($projects->isEmpty())<div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8"><h3 class="font-semibold">Belum ada proyek aktif</h3><p class="mt-1 text-sm text-slate-600">Buat proyek Tri Dharma pertama Anda untuk mulai mengelola aktivitas.</p><a href="{{ route('projects.create') }}" class="mt-4 inline-flex rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Buat proyek</a></div>
        @else<div class="grid gap-3 md:grid-cols-2">@foreach($projects as $project)<a href="{{ route('projects.show', $project) }}" class="rounded-xl border bg-white p-5 transition hover:border-teal-500"><div class="flex items-start justify-between gap-3"><h3 class="font-semibold">{{ $project->name }}</h3><span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700">{{ $project->owner_id === auth()->id() ? 'Pemilik' : 'Editor' }}</span></div><p class="mt-2 text-sm text-slate-600">{{ $project->assignments_count }} tugas · {{ $project->members_count }} editor</p><div class="mt-3 flex flex-wrap gap-2">@foreach($project->pillars as $pillar)<span class="rounded-full bg-teal-50 px-2.5 py-1 text-xs text-teal-900">{{ ['teaching'=>'Pengajaran','research'=>'Penelitian','community'=>'Pengabdian'][$pillar] ?? $pillar }}</span>@endforeach</div></a>@endforeach</div>@endif
    </section>
    <section class="mt-9" aria-labelledby="assignments-heading"><h2 id="assignments-heading" class="text-xl font-bold">Tugas terbaru</h2>@if($assignments->isEmpty())<p class="mt-3 rounded-xl border bg-white p-5 text-sm text-slate-600">Belum ada tugas di proyek aktif yang Anda kelola.</p>@else<div class="mt-4 divide-y rounded-xl border bg-white">@foreach($assignments as $assignment)<a href="{{ route('assignments.show', $assignment) }}" class="flex flex-wrap items-center justify-between gap-2 p-4 hover:bg-slate-50"><span><span class="font-semibold">{{ $assignment->title }}</span><span class="ml-2 text-sm text-slate-500">{{ $assignment->project->name }}</span></span><span class="text-sm text-slate-600">{{ $assignment->status === 'published' ? 'Terbit' : 'Draf' }} · {{ $assignment->submissions_count }} submission</span></a>@endforeach</div>@endif</section>
@elseif(auth()->user()->role === 'student')
    <section class="mt-10" aria-labelledby="student-work-heading"><div class="mb-4"><h2 id="student-work-heading" class="text-xl font-bold">Tugas dan revisi saya</h2><p class="mt-1 text-sm text-slate-600">Tugas terbit yang ditetapkan kepada akun Anda.</p></div><div class="mb-4 grid gap-3 sm:grid-cols-2"><div class="rounded-xl border bg-white p-5"><p class="text-sm text-slate-600">Tugas tersedia</p><p class="mt-1 text-3xl font-bold">{{ $dashboardStats['assignments'] }}</p></div><div class="rounded-xl border bg-white p-5"><p class="text-sm text-slate-600">Submission saya</p><p class="mt-1 text-3xl font-bold">{{ $dashboardStats['submissions'] }}</p></div></div>@if($assignments->isEmpty())<div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-sm text-slate-600">Belum ada tugas terbit yang ditautkan ke akun Anda.</div>@else<div class="divide-y rounded-xl border bg-white">@foreach($assignments as $assignment)<a href="{{ route('assignments.show', $assignment) }}" class="flex flex-wrap items-center justify-between gap-2 p-4 hover:bg-slate-50"><span><span class="font-semibold">{{ $assignment->title }}</span><span class="ml-2 text-sm text-slate-500">{{ $assignment->project->name }}</span></span><span class="text-sm text-teal-800">Buka tugas →</span></a>@endforeach</div>@endif</section>
@else
    <section class="mt-10 rounded-2xl border border-slate-200 bg-white p-7"><h2 class="text-xl font-bold">Selamat datang di ELT Academic Hub</h2><p class="mt-2 max-w-2xl text-slate-600">Akun umum dapat mengakses halaman publik. Data proyek, tugas, dan submission hanya tersedia bagi pengguna yang memperoleh akses sesuai perannya.</p><a href="{{ route('home') }}" class="mt-4 inline-block font-semibold text-teal-800 hover:underline">Buka halaman utama →</a></section>
@endif
@endsection
