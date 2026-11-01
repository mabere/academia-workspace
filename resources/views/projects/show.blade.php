@extends('layouts.hub')
@section('title', $project->name.' — ELT Academic Hub')
@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-teal-800 hover:underline">← Proyek Saya</a>
        <div class="relative mt-3 overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-teal-700 via-teal-500 to-emerald-500"></div>
            <div class="flex flex-wrap items-start justify-between gap-4 pt-1">
                <div>
                    <p class="text-xs sm:text-sm text-slate-500">
                        Pemilik: <strong class="text-slate-800">{{ $project->owner->name }}</strong>
                        @if($project->academicPeriod)
                            · Semester: <span class="font-semibold text-teal-800">{{ $project->academicPeriod->name }}</span>
                        @endif
                    </p>
                    <h1 class="mt-1.5 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">{{ $project->name }}</h1>
                    <p class="mt-2 max-w-3xl text-sm sm:text-base text-slate-600 leading-relaxed">{{ $project->description ?: 'Belum ada deskripsi.' }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($project->pillars as $pillar)
                            <span class="rounded-full bg-teal-50 border border-teal-200 px-3 py-1 text-xs font-semibold text-teal-800">
                                {{ ['teaching'=>'Pengajaran','research'=>'Penelitian','community'=>'Pengabdian'][$pillar] }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @if($project->isOwnedBy(auth()->user()))
                    <form method="post" action="{{ route('projects.archive',$project) }}" onsubmit="return confirm('Arsipkan proyek ini?')">
                        @csrf
                        <button class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Arsipkan</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if($project->isOwnedBy(auth()->user()))
        <details class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs">
            <summary class="cursor-pointer text-sm sm:text-base font-bold text-slate-900">Ubah detail proyek</summary>
            <form method="post" action="{{ route('projects.update',$project) }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <label class="block text-sm font-semibold text-slate-700">
                    Nama
                    <input name="name" required maxlength="160" value="{{ $project->name }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    Periode Akademik / Semester
                    <select name="academic_period_id" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                        <option value="">Pilih periode semester...</option>
                        @foreach($academicPeriods as $period)
                            <option value="{{ $period->id }}" @selected(old('academic_period_id', $project->academic_period_id) == $period->id)>
                                {{ $period->name }} {{ $period->is_active ? '· (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    Deskripsi
                    <textarea name="description" rows="3" maxlength="5000" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed">{{ $project->description }}</textarea>
                </label>
                <fieldset>
                    <legend class="text-sm font-semibold text-slate-700">Fokus Tri Dharma</legend>
                    <div class="mt-2 flex flex-wrap gap-4">
                        @foreach(['teaching'=>'Pengajaran','research'=>'Penelitian','community'=>'Pengabdian'] as $key=>$label)
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" name="pillars[]" value="{{ $key }}" @checked(in_array($key,$project->pillars)) class="rounded border-slate-300 text-teal-700">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <button class="rounded-xl border border-slate-300 bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 transition">Simpan perubahan</button>
            </form>
        </details>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs">
            <h2 class="text-base font-bold text-slate-900">Editor proyek</h2>
            <p class="mt-1 text-sm text-slate-600">Editor dosen/admin dapat melihat proyek dan mengelola tugas WriteWise. Pemilik mengendalikan detail, anggota, dan pengarsipan proyek.</p>
            <form method="post" action="{{ route('projects.members.store',$project) }}" class="mt-4 flex flex-wrap gap-3">
                @csrf
                <label class="sr-only" for="user_id">Pilih editor</label>
                <select id="user_id" name="user_id" required class="min-w-64 flex-1 rounded-xl border-slate-300 text-sm">
                    <option value="">Pilih dosen/admin</option>
                    @foreach($eligibleMembers->whereNotIn('id',$project->members->pluck('id')) as $member)
                        <option value="{{ $member->id }}">{{ $member->name }} — {{ $member->email }}</option>
                    @endforeach
                </select>
                <button class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Tambah editor</button>
            </form>
            @if($project->members->isNotEmpty())
                <ul class="mt-4 divide-y divide-slate-100">
                    @foreach($project->members as $member)
                        <li class="flex items-center justify-between gap-3 py-3 text-sm">
                            <span class="font-medium text-slate-900">{{ $member->name }} <span class="text-xs text-slate-500">{{ $member->email }}</span></span>
                            <form method="post" action="{{ route('projects.members.destroy',[$project,$member]) }}" onsubmit="return confirm('Hapus editor ini dari proyek?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-xs sm:text-sm font-semibold text-red-700 hover:underline">Hapus</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif

    @if($project->archived_at)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            Proyek ini diarsipkan pada {{ $project->archived_at->format('d M Y') }}. Detail dan tugas tersedia untuk dibaca; perubahan dinonaktifkan.
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-3">
        <section class="flex flex-col justify-between rounded-2xl border border-teal-200 bg-teal-50/70 p-5 shadow-2xs">
            <div>
                <h2 class="text-base font-bold text-teal-950">LitSynth ELT</h2>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-600 leading-relaxed">Kelola sumber literatur privat, matriks kajian, dan draf blueprint instrumen.</p>
            </div>
            <div class="mt-4">
                <a href="{{ route('litsynth.index', $project) }}" class="inline-flex rounded-xl bg-teal-800 px-4 py-2 text-xs sm:text-sm font-bold text-white hover:bg-teal-700 transition">Buka LitSynth</a>
            </div>
        </section>

        @if(in_array('research', $project->pillars ?? [], true))
            <section class="flex flex-col justify-between rounded-2xl border border-cyan-200 bg-cyan-50/70 p-5 shadow-2xs">
                <div>
                    <h2 class="text-base font-bold text-cyan-950">IndoEFL CorpusLab</h2>
                    <p class="mt-1.5 text-xs sm:text-sm text-slate-600 leading-relaxed">Korpus privat dengan persetujuan protokol, catatan consent, pembersihan pola PII, anotasi, pencarian KWIC, dan ekspor CSV. Pengumpulan terkunci sampai protokol memiliki rujukan persetujuan etik.</p>
                </div>
                <div class="mt-4">
                    <a href="{{ route('corpuslab.index', $project) }}" class="inline-flex rounded-xl bg-cyan-800 px-4 py-2 text-xs sm:text-sm font-bold text-white hover:bg-cyan-700 transition">Buka CorpusLab</a>
                </div>
            </section>
        @endif

        <section class="flex flex-col justify-between rounded-2xl border border-violet-200 bg-violet-50/70 p-5 shadow-2xs">
            <div>
                <h2 class="text-base font-bold text-violet-950">GuruBahasa · Pengabdian</h2>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-600 leading-relaxed">Rancang dan kurasi draf materi bahasa. Penyusunan saat ini manual; AI nonaktif dan target CEFR hanya perkiraan.</p>
            </div>
            <div class="mt-4">
                <a href="{{ route('gurubahasa.index', $project) }}" class="inline-flex rounded-xl bg-violet-800 px-4 py-2 text-xs sm:text-sm font-bold text-white hover:bg-violet-700 transition">Buka GuruBahasa</a>
            </div>
        </section>
    </div>

    <div class="grid gap-8 lg:grid-cols-[.85fr_1.15fr]">
        @if($project->archived_at === null)
            <section class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-7 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900">Buat tugas WriteWise</h2>
                <p class="mt-1 text-sm text-slate-600">Mahasiswa harus sudah memiliki akun dengan peran Mahasiswa.</p>
                <form method="post" action="{{ route('projects.assignments.store',$project) }}" class="mt-5 space-y-4">
                    @csrf
                    <label class="block text-sm font-semibold text-slate-700">
                        Judul
                        <input name="title" required maxlength="180" value="{{ old('title') }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">
                        Instruksi
                        <textarea name="instructions" required rows="4" maxlength="10000" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed">{{ old('instructions') }}</textarea>
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">
                        Email mahasiswa (pisahkan dengan koma/baris baru)
                        <textarea name="student_emails" rows="2" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">{{ old('student_emails') }}</textarea>
                    </label>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="block text-sm font-semibold text-slate-700">
                            Tingkat feedback
                            <select name="feedback_level" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                                <option value="indirect">Indirect</option>
                                <option value="direct">Direct</option>
                                <option value="socratic">Socratic</option>
                            </select>
                        </label>
                        <label class="block text-sm font-semibold text-slate-700">
                            Bahasa feedback
                            <select name="feedback_language" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                                <option value="id">Indonesia</option>
                                <option value="en">English</option>
                            </select>
                        </label>
                    </div>
                    <label class="block text-sm font-semibold text-slate-700">
                        Tenggat (opsional)
                        <input type="datetime-local" name="due_at" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">
                        Status
                        <select name="status" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                            <option value="draft">Simpan sebagai draf</option>
                            <option value="published">Terbitkan</option>
                        </select>
                    </label>
                    <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-800 transition">Simpan tugas</button>
                </form>
            </section>
        @else
            <section class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-7 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900">Proyek diarsipkan</h2>
                <p class="mt-2 text-sm text-slate-600">Buat tugas baru tidak tersedia. Tugas yang sudah ada tetap dapat ditinjau.</p>
            </section>
        @endif

        <section>
            <h2 class="text-xl font-bold text-slate-900">Tugas dalam proyek</h2>
            @if($project->assignments->isEmpty())
                <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-sm text-slate-600">Belum ada tugas.</div>
            @else
                <div class="mt-4 space-y-3">
                    @foreach($project->assignments as $assignment)
                        <a href="{{ route('assignments.show',$assignment) }}" class="block rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs transition hover:border-teal-500 hover:shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <h3 class="text-base font-bold text-slate-900">{{ $assignment->title }}</h3>
                                <span class="rounded-full {{ $assignment->status === 'published' ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-100 text-slate-600' }} px-2.5 py-0.5 text-xs font-semibold">{{ $assignment->status === 'published' ? 'Terbit' : 'Draf' }}</span>
                            </div>
                            <p class="mt-2 text-xs sm:text-sm text-slate-600">{{ $assignment->students_count }} mahasiswa · {{ $assignment->submissions_count }} submission</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
