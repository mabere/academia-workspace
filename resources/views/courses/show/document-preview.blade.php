        {{-- MODAL PRATINJAU DOKUMEN GOOGLE DOCS (Global Viewer Modal) --}}
        <div x-show="docModalOpen"
            x-cloak
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-xs"
            @keydown.escape.window="docModalOpen = false">
            <div class="relative w-full max-w-5xl rounded-3xl border border-slate-200 bg-white shadow-2xl overflow-hidden flex flex-col max-h-[92vh]"
                @click.away="docModalOpen = false">
                {{-- Header Modal --}}
                <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50/80 px-6 py-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="rounded-xl bg-blue-100 p-2 text-blue-800">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-extrabold text-slate-900 truncate" x-text="docModalTitle"></h3>
                            <p class="text-xs text-slate-500">Pratinjau Dokumen Online (Google Docs Viewer)</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="docModalUrl" target="_blank"
                            class="hidden sm:inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                            <span>Buka Tab Baru &rarr;</span>
                        </a>
                        <a :href="docDownloadUrl"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-slate-800 transition">
                            <span>Unduh Berkas</span>
                        </a>
                        <button type="button" @click="docModalOpen = false"
                            class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Body Modal --}}
                <div class="flex-1 overflow-hidden p-3 bg-slate-100 flex flex-col">
                    @if(app()->environment('local') || str_contains(request()->getHost(), '.test') || str_contains(request()->getHost(), 'localhost'))
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900 mb-2">
                            <strong>Catatan Pengujian Lokal:</strong> Google Docs Viewer membutuhkan domain yang dapat diakses publik dari internet (seperti pada VPS production). Jika pratinjau Google kosong di localhost, silakan klik tombol <strong>Unduh Berkas</strong> di kanan atas.
                        </div>
                    @endif
                    <iframe :src="docModalUrl" class="w-full flex-1 min-h-[60vh] max-h-[72vh] rounded-2xl border border-slate-200 bg-white shadow-inner" frameborder="0"></iframe>
                </div>
            </div>
        </div>

