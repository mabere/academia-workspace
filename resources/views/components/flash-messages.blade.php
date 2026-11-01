@if(session('status'))
    <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/90 p-4 text-sm font-medium text-emerald-950 shadow-2xs" role="status" aria-live="polite">
        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white" aria-hidden="true">✓</span>
        <div class="flex-1 leading-relaxed">{{ session('status') }}</div>
    </div>
@endif
@if($errors->any())
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50/90 p-4 text-sm text-rose-950 shadow-2xs" role="alert" aria-labelledby="form-errors-heading">
        <div class="flex items-start gap-3">
            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-rose-600 text-xs font-bold text-white" aria-hidden="true">!</span>
            <div class="flex-1">
                <h2 id="form-errors-heading" class="font-bold text-rose-950">Periksa kembali isian berikut</h2>
                <ul class="mt-1.5 list-inside list-disc space-y-1 text-rose-900">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        </div>
    </div>
@endif
