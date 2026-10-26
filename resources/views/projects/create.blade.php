@extends('layouts.hub')
@section('title', 'Buat Proyek — ELT Academic Hub')
@section('content')
<section class="mx-auto max-w-2xl rounded-2xl border bg-white p-7"><a href="{{ route('projects.index') }}" class="text-sm text-teal-800">← Proyek Saya</a><h1 class="mt-3 text-2xl font-bold">Buat proyek</h1><form method="post" action="{{ route('projects.store') }}" class="mt-6 space-y-5">@csrf
<label class="block text-sm font-medium">Nama proyek<input required maxlength="160" name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-lg border-slate-300"></label>
<label class="block text-sm font-medium">Periode Akademik / Semester (opsional)<select name="academic_period_id" class="mt-1 w-full rounded-lg border-slate-300"><option value="">Pilih periode semester...</option>@foreach($academicPeriods as $period)<option value="{{ $period->id }}" @selected(old('academic_period_id') == $period->id || ($period->is_active && !old('academic_period_id')))>{{ $period->name }} {{ $period->is_active ? '· (Aktif)' : '' }}</option>@endforeach</select></label>
<label class="block text-sm font-medium">Deskripsi<textarea name="description" rows="4" maxlength="5000" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description') }}</textarea></label>
<fieldset><legend class="text-sm font-medium">Fokus Tri Dharma (pilih satu atau lebih)</legend><div class="mt-3 flex flex-wrap gap-4">@foreach(['teaching'=>'Pendidikan & pengajaran','research'=>'Penelitian','community'=>'Pengabdian kepada masyarakat'] as $key=>$label)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="pillars[]" value="{{ $key }}" @checked(in_array($key,old('pillars',[]))) class="rounded border-slate-300">{{ $label }}</label>@endforeach</div></fieldset>
<button class="rounded-lg bg-slate-900 px-5 py-3 font-semibold text-white">Simpan proyek</button></form></section>
@endsection
