@extends('layouts.hub')
@section('title', 'Output & Evidence · ELT Academic Hub')
@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-medium text-teal-800">Lintas modul · Bukti Kinerja Tri Dharma Perguruan Tinggi</p>
        <h1 class="mt-1 text-3xl font-bold">Output & Evidence</h1>
        <p class="mt-2 max-w-3xl text-slate-600">Repositori bukti kinerja terpadu untuk pengajaran, penelitian, dan pengabdian. Lengkap dengan estimasi beban SKS berdasarkan Pedoman Operasional Beban Kerja Dosen (PO BKD).</p>
    </div>
</div>

<section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Rekapitulasi Kinerja Beban Kerja Dosen (BKD)</h2>
            <p class="mt-1 text-xs text-slate-500">Estimasi beban SKS dari artefak yang terfilter di bawah dibandingkan target standar BKD (minimal {{ $bkdSummary['target_sks'] }} SKS/semester).</p>
        </div>
        <div>
            <span class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1 text-xs font-bold {{ $bkdSummary['status_tone'] === 'green' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                <span class="h-2 w-2 rounded-full {{ $bkdSummary['status_tone'] === 'green' ? 'bg-emerald-600' : 'bg-amber-600' }}"></span>
                Status: {{ $bkdSummary['status_label'] }}
            </span>
        </div>
    </div>

    <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Total Kinerja</p>
            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($bkdSummary['total_sks'], 1) }} <span class="text-sm font-normal text-slate-600">/ {{ $bkdSummary['target_sks'] }} SKS</span></p>
            <p class="mt-1 text-xs text-slate-500">Target minimum terpenuhi: {{ $bkdSummary['total_sks'] >= $bkdSummary['target_sks'] ? 'Ya' : 'Belum' }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Pendidikan</p>
                <span class="text-xs font-semibold {{ $bkdSummary['teaching_met'] ? 'text-emerald-700' : 'text-amber-700' }}">{{ $bkdSummary['teaching_met'] ? '✓ Min 3.0' : 'Min 3.0' }}</span>
            </div>
            <p class="mt-2 text-2xl font-bold text-teal-900">{{ number_format($bkdSummary['teaching_sks'], 1) }} <span class="text-xs font-normal text-slate-500">SKS</span></p>
            <p class="mt-1 text-xs text-slate-500">Tugas & feedback WriteWise</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Penelitian</p>
                <span class="text-xs font-semibold {{ $bkdSummary['research_met'] ? 'text-emerald-700' : 'text-amber-700' }}">{{ $bkdSummary['research_met'] ? '✓ Min 1.0' : 'Min 1.0' }}</span>
            </div>
            <p class="mt-2 text-2xl font-bold text-cyan-900">{{ number_format($bkdSummary['research_sks'], 1) }} <span class="text-xs font-normal text-slate-500">SKS</span></p>
            <p class="mt-1 text-xs text-slate-500">LitSynth, Blueprint, Korpus</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Pengabdian</p>
                <span class="text-xs font-semibold {{ $bkdSummary['community_met'] ? 'text-emerald-700' : 'text-amber-700' }}">{{ $bkdSummary['community_met'] ? '✓ Min 0.5' : 'Min 0.5' }}</span>
            </div>
            <p class="mt-2 text-2xl font-bold text-violet-900">{{ number_format($bkdSummary['community_sks'], 1) }} <span class="text-xs font-normal text-slate-500">SKS</span></p>
            <p class="mt-1 text-xs text-slate-500">Modul GuruBahasa terkurasi</p>
        </div>
    </div>
</section>

<form method="get" action="{{ route('outputs.index') }}" class="mt-6 grid gap-3 rounded-2xl border bg-white p-5 sm:grid-cols-2 lg:grid-cols-6 lg:items-end">
    <label class="text-sm font-medium">Periode Semester
        <select name="academic_period_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
            <option value="">Semua periode</option>
            @foreach($academicPeriods as $period)
                <option value="{{ $period->id }}" @selected((int)($filters['academic_period_id'] ?? 0) === $period->id)>
                    {{ $period->name }} {{ $period->is_active ? '· (Aktif)' : '' }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium">Proyek
        <select name="project_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
            <option value="">Semua proyek</option>
            @foreach($projects as $project)
                <option value="{{ $project->id }}" @selected((int)($filters['project_id'] ?? 0) === $project->id)>
                    {{ $project->name }}{{ $project->archived_at ? ' · arsip' : '' }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium">Modul
        <select name="type" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
            <option value="">Semua modul</option>
            @foreach($types as $key => $label)
                <option value="{{ $key }}" @selected(($filters['type'] ?? '') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium">Status
        <select name="status" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
            <option value="">Semua status</option>
            @foreach($statuses as $key => $label)
                <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium lg:col-span-1">Cari
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" placeholder="Judul, topik, proyek…" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
    </label>

    <div class="flex gap-2">
        <button class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Filter</button>
        @if(!empty($filters['academic_period_id']) || !empty($filters['project_id']) || !empty($filters['type']) || !empty($filters['status']) || !empty($filters['q']))
            <a href="{{ route('outputs.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</a>
        @endif
    </div>
</form>

<section class="mt-7">
    <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
        <h2 class="text-xl font-bold">Daftar Bukti Kinerja</h2>
        <p class="text-sm text-slate-500">{{ $items->total() }} artefak tercatat</p>
    </div>

    @if($items->isEmpty())
        <div class="rounded-xl border border-dashed bg-white p-8 text-sm text-slate-600">
            Belum ada artefak bukti kinerja yang cocok dengan kriteria filter. Pastikan tugas WriteWise telah diterbitkan atau materi GuruBahasa telah dikurasi.
        </div>
    @else
        <div class="overflow-hidden rounded-xl border bg-white shadow-xs">
            <div class="divide-y">
                @foreach($items as $item)
                    <article class="flex flex-wrap items-center justify-between gap-4 p-5 hover:bg-slate-50/70 transition">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    {{ $item['pillar_label'] }}
                                </span>
                                <span class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-900">
                                    {{ $item['type_label'] }}
                                </span>
                                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-800">
                                    +{{ number_format($item['sks'], 1) }} SKS
                                </span>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                    {{ $item['status_label'] }}
                                </span>
                            </div>

                            <h3 class="mt-2 break-words text-base font-semibold text-slate-900">{{ $item['title'] }}</h3>
                            <p class="mt-1 text-sm text-slate-600">{{ $item['project_name'] }} · {{ $item['description'] }}</p>
                            <p class="mt-1 text-xs text-slate-400">Tanggal rekaman: {{ $item['created_at']?->format('d M Y H:i') }}</p>
                        </div>

                        <div class="flex shrink-0 gap-2">
                            <a href="{{ $item['show_url'] }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold hover:bg-slate-50">Pratinjau</a>
                            <a href="{{ $item['download_url'] }}" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">Unduh</a>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="border-t p-4">{{ $items->links() }}</div>
        </div>
    @endif
</section>
@endsection
