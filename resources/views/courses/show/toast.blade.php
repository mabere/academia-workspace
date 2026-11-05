        {{-- Toast Notifikasi Sukses Terapkan AI --}}
        <div x-show="aiAppliedToast" x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-4"
            class="fixed bottom-6 right-6 z-50 rounded-2xl bg-slate-900 border border-teal-500/50 p-4 text-white shadow-2xl flex items-center gap-3.5 max-w-sm">
            <div class="h-9 w-9 rounded-xl bg-teal-500/20 text-teal-300 border border-teal-400/40 flex items-center justify-center font-black text-base shrink-0">
                ✓
            </div>
            <div>
                <p class="font-extrabold text-white text-xs">Rancangan AI Berhasil Diterapkan!</p>
                <p class="text-slate-300 text-[11px] mt-0.5">Judul, instruksi langkah kerja, dan kriteria rubrik telah terisi ke formulir penugasan.</p>
            </div>
        </div>
