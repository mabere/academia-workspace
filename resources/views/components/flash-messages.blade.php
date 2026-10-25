@if(session('status'))
    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-950" role="status" aria-live="polite">{{ session('status') }}</div>
@endif
@if($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-950" role="alert" aria-labelledby="form-errors-heading">
        <h2 id="form-errors-heading" class="font-semibold">Periksa kembali isian berikut</h2>
        <ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
