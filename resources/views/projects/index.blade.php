@extends('layouts.hub')
@section('title', 'Proyek — ELT Academic Hub')
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4 rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-teal-800">Ruang kerja Tri Dharma</p>
            <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Proyek yang saya kelola</h1>
            <p class="mt-1.5 text-sm text-slate-600">Proyek aktif milik Anda atau yang memberi Anda akses editor.</p>
        </div>
        <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-slate-800 transition">
            Buat proyek
        </a>
    </div>

    @if($projects->isEmpty())
        <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center">
            <h2 class="text-base font-bold text-slate-800">Belum ada proyek aktif</h2>
            <p class="mt-1.5 text-sm text-slate-600">Buat proyek untuk mulai mengelola aktivitas Tri Dharma.</p>
            <a href="{{ route('projects.create') }}" class="mt-5 inline-flex rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-800 transition">Buat proyek</a>
        </section>
    @else
        <div class="grid gap-5 md:grid-cols-2">
            @foreach($projects as $project)
                <a href="{{ route('projects.show', $project) }}" class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-500 hover:shadow-md">
                    <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-teal-700 via-teal-500 to-emerald-500"></div>
                    <div>
                        <div class="flex items-start justify-between gap-3 pt-1">
                            <h2 class="text-lg font-bold text-slate-900 group-hover:text-teal-800 transition">{{ $project->name }}</h2>
                            <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                {{ $project->owner_id === auth()->id() ? 'Pemilik' : 'Editor' }}
                            </span>
                        </div>
                        <p class="mt-1.5 text-xs font-medium text-slate-500">Pemilik: {{ $project->owner->name }}</p>
                        <p class="mt-2.5 text-sm text-slate-600 leading-relaxed line-clamp-2">{{ $project->description ?: 'Belum ada deskripsi.' }}</p>
                    </div>
                    <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                        @foreach($project->pillars as $pillar)
                            <span class="rounded-full bg-teal-50 border border-teal-200 px-3 py-0.5 text-xs font-semibold text-teal-800">
                                {{ ['teaching'=>'Pengajaran','research'=>'Penelitian','community'=>'Pengabdian'][$pillar] ?? $pillar }}
                            </span>
                        @endforeach
                        <span class="ml-auto text-xs font-semibold text-slate-500">{{ $project->assignments_count }} tugas</span>
                    </div>
                </a>
            @endforeach
        </div>
        <div>
            {{ $projects->links() }}
        </div>
    @endif
</div>
@endsection
