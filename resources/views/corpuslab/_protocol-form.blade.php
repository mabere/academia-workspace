@php($fields = [
    ['title', 'Nama korpus', 'text', 180],
    ['language', 'Bahasa korpus', 'text', 80],
    ['research_purpose', 'Tujuan / pertanyaan penelitian', 'textarea', 5000],
    ['participant_population', 'Populasi dan konteks partisipan', 'textarea', 3000],
    ['sampling_method', 'Metode pemilihan dan pengumpulan data', 'textarea', 3000],
    ['consent_process', 'Prosedur persetujuan partisipan', 'textarea', 3000],
    ['ethics_authority', 'Komite / otoritas etik', 'text', 180],
    ['ethics_reference', 'Nomor rujukan persetujuan etik', 'text', 120],
    ['anonymization_plan', 'Rencana anonimisasi dan pemeriksaan manusia', 'textarea', 4000],
    ['privacy_plan', 'Kontrol akses, penggunaan, dan perlindungan data', 'textarea', 4000],
])
<form method="post" action="{{ $update ? route('corpuslab.protocol.update', [$project, $corpus]) : route('corpuslab.protocol.store', $project) }}" class="space-y-4">
    @csrf
    @if($update) @method('PATCH') @endif
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach($fields as [$name, $label, $type, $max])
            <label class="block text-sm font-medium {{ $type === 'textarea' ? 'sm:col-span-2' : '' }}" for="protocol-{{ $name }}">{{ $label }}
                @if($type === 'textarea')
                    <textarea id="protocol-{{ $name }}" name="{{ $name }}" required maxlength="{{ $max }}" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old($name, $corpus?->{$name}) }}</textarea>
                @else
                    <input id="protocol-{{ $name }}" name="{{ $name }}" type="text" required maxlength="{{ $max }}" value="{{ old($name, $corpus?->{$name}) }}" class="mt-1 w-full rounded-lg border-slate-300">
                @endif
                @error($name)<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>
        @endforeach
        <label for="protocol-ethics_approved_on" class="block text-sm font-medium">Tanggal persetujuan etik
            <input id="protocol-ethics_approved_on" name="ethics_approved_on" type="date" required max="{{ today()->toDateString() }}" value="{{ old('ethics_approved_on', $corpus?->ethics_approved_on?->toDateString()) }}" class="mt-1 w-full rounded-lg border-slate-300">
            @error('ethics_approved_on')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
        </label>
        <label for="protocol-retention_until" class="block text-sm font-medium">Data dipertahankan sampai
            <input id="protocol-retention_until" name="retention_until" type="date" required min="{{ today()->addDay()->toDateString() }}" value="{{ old('retention_until', $corpus?->retention_until?->toDateString()) }}" class="mt-1 w-full rounded-lg border-slate-300">
            @error('retention_until')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
        </label>
    </div>
    <p class="rounded-lg bg-amber-50 p-3 text-sm text-amber-900">Isi berdasarkan protokol dan persetujuan etik yang benar-benar telah diterbitkan. Metadata ini bukan pengganti review komite etik. Jangan masukkan nama partisipan atau unggah formulir consent bertanda tangan.</p>
    <button class="rounded-lg bg-slate-900 px-4 py-2.5 font-semibold text-white">{{ $update ? 'Simpan perubahan protokol' : 'Simpan draf protokol' }}</button>
</form>
