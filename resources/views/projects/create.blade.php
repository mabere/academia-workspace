@extends('layouts.hub')
@section('title', 'Buat Proyek — ELT Academic Hub')
@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-teal-800 hover:underline">← Proyek Saya</a>
        <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">Buat proyek</h1>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
        <form method="post" action="{{ route('projects.store') }}" class="space-y-5">
            @csrf
            <label class="block text-sm font-semibold text-slate-700">
                Nama proyek
                <input required maxlength="160" name="name" value="{{ old('name') }}" class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 px-3.5 text-sm focus:border-teal-700 focus:ring-teal-700">
            </label>

            <label class="block text-sm font-semibold text-slate-700">
                Periode Akademik / Semester (opsional)
                <select name="academic_period_id" class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 px-3.5 text-sm focus:border-teal-700 focus:ring-teal-700">
                    <option value="">Pilih periode semester...</option>
                    @foreach($academicPeriods as $period)
                        <option value="{{ $period->id }}" @selected(old('academic_period_id') == $period->id || ($period->is_active && !old('academic_period_id')))>
                            {{ $period->name }} {{ $period->is_active ? '· (Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="block text-sm font-semibold text-slate-700">
                Deskripsi
                <textarea name="description" rows="4" maxlength="5000" class="mt-1.5 w-full rounded-xl border-slate-300 p-3.5 text-sm leading-relaxed focus:border-teal-700 focus:ring-teal-700">{{ old('description') }}</textarea>
            </label>

            <fieldset class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                <legend class="px-1 text-sm font-semibold text-slate-800">Fokus Tri Dharma (pilih satu atau lebih)</legend>
                <div class="mt-2 flex flex-wrap gap-4">
                    @foreach(['teaching'=>'Pendidikan & pengajaran','research'=>'Penelitian','community'=>'Pengabdian kepada masyarakat'] as $key=>$label)
                        <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                            <input type="checkbox" name="pillars[]" value="{{ $key }}" @checked(in_array($key,old('pillars',[]))) class="rounded border-slate-300 text-teal-700 focus:ring-teal-700">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="flex justify-end pt-2">
                <button class="rounded-xl bg-slate-900 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-slate-800 transition">
                    Simpan proyek
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
