@extends('layouts.hub')
@section('title', 'Cadangan Data & Arsip — Academia Workspace')

@section('content')
<div class="space-y-8">
    {{-- HEADER HALAMAN --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-teal-800">Keamanan & Keberlanjutan Data</p>
            <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Cadangan Data & Arsip Mandiri</h1>
            <p class="mt-1.5 text-xs sm:text-sm text-slate-600 max-w-2xl leading-relaxed">
                Unduh salinan data perkuliahan, nilai, tugas, dan seluruh dokumen bahan ajar langsung ke komputer Anda dengan satu klik tanpa perlu konfigurasi server VPS.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Sistem Database Siap Dicadangkan
            </span>
        </div>
    </div>

    {{-- KPI STATS RINGKASAN DATA --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-teal-100 bg-white p-5 shadow-2xs">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Basis Data Aktif</p>
            <p class="mt-1.5 text-2xl font-extrabold text-[#05285c]">{{ $dbSizeFormatted }}</p>
            <p class="mt-1 text-[11px] text-teal-800 font-medium">Mesin: {{ $dbDriver }} (Tunggal & Cepat)</p>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-2xs">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Berkas Materi & Riset</p>
            <p class="mt-1.5 text-2xl font-extrabold text-blue-900">{{ $totalStorageFormatted }}</p>
            <p class="mt-1 text-[11px] text-slate-500">{{ $totalStorageFiles }} berkas PDF, DOCX, & Media</p>
        </div>

        <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-2xs">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Arsip Tersimpan di Server</p>
            <p class="mt-1.5 text-2xl font-extrabold text-amber-900">{{ $totalBackupsCount }} Berkas</p>
            <p class="mt-1 text-[11px] text-slate-500">Total: {{ $totalBackupsFormatted }}</p>
        </div>

        <div class="rounded-2xl border border-purple-100 bg-white p-5 shadow-2xs">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Portabilitas</p>
            <p class="mt-1.5 text-2xl font-extrabold text-purple-900">100% Mandiri</p>
            <p class="mt-1 text-[11px] text-slate-500">Siap dipindahkan antar server/laptop</p>
        </div>
    </div>

    {{-- KARTU OPSI PENCADANGAN UTAMA --}}
    <div class="grid gap-6 md:grid-cols-2">
        {{-- OPSI 1: DATABASE SAJA --}}
        <div class="relative flex flex-col justify-between rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-7 shadow-xs hover:border-teal-700 hover:shadow-md transition duration-200">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-50 text-teal-800 border border-teal-200/60">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                    </div>
                    <div>
                        <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-[10px] font-bold text-teal-900">Rekomendasi Rutin</span>
                        <h2 class="text-lg font-bold text-slate-900 mt-0.5">Cadangan Database Saja (.sqlite)</h2>
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Mencadangkan seluruh rekaman data teks aplikasi: akun pengguna, program studi, periode semester, mata kuliah, jurnal 16 pertemuan, tugas WriteWise, presensi mahasiswa, dan seluruh nilai akademik.
                </p>

                <div class="mt-5 space-y-2 rounded-xl bg-slate-50 p-3.5 text-xs text-slate-600">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Format Ekspor:</span>
                        <span class="font-mono font-bold text-slate-800">.sqlite</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Estimasi Ukuran:</span>
                        <span class="font-bold text-slate-800">~{{ $dbSizeFormatted }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Kecepatan Unduh:</span>
                        <span class="font-bold text-emerald-700">Sangat Cepat (&lt; 1 detik)</span>
                    </div>
                </div>
            </div>

            <form method="post" action="{{ route('backups.database') }}" class="mt-6">
                @csrf
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-teal-800 px-5 py-3 text-xs font-bold text-white shadow-sm hover:bg-teal-700 active:scale-[0.99] transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Unduh Cadangan Database Sekarang (.sqlite)
                </button>
            </form>
        </div>

        {{-- OPSI 2: PAKET LENGKAP FULL BACKUP (ZIP) --}}
        <div class="relative flex flex-col justify-between rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-7 shadow-xs hover:border-[#05285c] hover:shadow-md transition duration-200">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-[#05285c] border border-blue-200/60">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                    <div>
                        <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-[10px] font-bold text-[#05285c]">Komprehensif (All-in-One)</span>
                        <h2 class="text-lg font-bold text-slate-900 mt-0.5">Paket Arsip Lengkap (.zip)</h2>
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Mencadangkan seluruh database **beserta seluruh berkas fisik** bahan ajar perkuliahan (PDF, DOCX, PPT, Audio, Video), berkas kajian LitSynth, dan media yang pernah diunggah ke dalam satu berkas terarsip ZIP.
                </p>

                <div class="mt-5 space-y-2 rounded-xl bg-slate-50 p-3.5 text-xs text-slate-600">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Format Ekspor:</span>
                        <span class="font-mono font-bold text-slate-800">.zip (Terkompresi)</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Cakupan Berkas:</span>
                        <span class="font-bold text-slate-800">Database + {{ $totalStorageFiles }} Berkas Media</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Panduan Restore:</span>
                        <span class="font-bold text-blue-800">Termasuk README-CADANGAN.txt</span>
                    </div>
                </div>
            </div>

            <form method="post" action="{{ route('backups.full') }}" class="mt-6">
                @csrf
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#05285c] px-5 py-3 text-xs font-bold text-white shadow-sm hover:bg-[#041e45] active:scale-[0.99] transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Unduh Paket Arsip Lengkap (.zip)
                </button>
            </form>
        </div>
    </div>

    {{-- RIWAYAT BERKAS CADANGAN DI SERVER --}}
    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50 px-5 py-3.5">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Riwayat Berkas Cadangan Tersimpan di Server</h3>
                <p class="text-[11px] text-slate-500">Snapshot cadangan yang pernah dibuat dan siap diunduh ulang atau dibersihkan.</p>
            </div>
            <span class="text-xs font-semibold text-slate-500">{{ count($backupFiles) }} berkas cadangan</span>
        </div>

        @if(empty($backupFiles))
            <div class="p-10 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-3">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700">Belum Ada Riwayat Cadangan di Server</p>
                <p class="mt-1 text-xs text-slate-500">Gunakan salah satu tombol di atas untuk membuat dan mengunduh cadangan pertama Anda.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="border-b border-slate-200 bg-slate-50/70 font-bold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center whitespace-nowrap">No</th>
                            <th class="py-3 px-4">Nama Berkas Cadangan</th>
                            <th class="py-3 px-4 w-44 whitespace-nowrap">Format & Jenis</th>
                            <th class="py-3 px-4 w-28 text-center whitespace-nowrap">Ukuran</th>
                            <th class="py-3 px-4 w-48 whitespace-nowrap">Waktu Dibuat</th>
                            <th class="py-3 px-4 w-40 text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($backupFiles as $idx => $bk)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 text-center font-medium text-slate-400 whitespace-nowrap">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        @if($bk['is_zip'])
                                            <span class="rounded bg-blue-100 p-1 text-blue-800">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                </svg>
                                            </span>
                                        @else
                                            <span class="rounded bg-teal-100 p-1 text-teal-800">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                                </svg>
                                            </span>
                                        @endif
                                        <span class="truncate max-w-xs sm:max-w-md">{{ $bk['filename'] }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold
                                        {{ $bk['is_zip'] ? 'bg-blue-50 text-blue-900 border border-blue-200' : 'bg-teal-50 text-teal-900 border border-teal-200' }}">
                                        {{ $bk['type_label'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-800 whitespace-nowrap">
                                    {{ $bk['size_formatted'] }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                                    <div>{{ $bk['created_at']->translatedFormat('d M Y, H:i') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $bk['created_at']->diffForHumans() }}</div>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center justify-center gap-1.5">
                                        <a href="{{ route('backups.download', $bk['filename']) }}" class="inline-flex items-center gap-1 rounded-lg bg-teal-800 px-2.5 py-1 text-xs font-semibold text-white hover:bg-teal-700 transition shadow-2xs">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            <span>Unduh</span>
                                        </a>

                                        <form method="post" action="{{ route('backups.destroy', $bk['filename']) }}" onsubmit="return confirm('Hapus berkas cadangan {{ $bk['filename'] }} dari server?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 bg-white p-1 text-red-500 hover:bg-red-50 hover:text-red-700 transition" title="Hapus dari server">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- KOTAK INFORMASI / TIPS KEAMANAN --}}
    <div class="rounded-2xl border border-teal-200 bg-teal-50/50 p-5 text-xs text-teal-950">
        <div class="flex items-start gap-3">
            <span class="rounded-lg bg-teal-100 p-2 text-teal-800 shrink-0">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <div class="space-y-1">
                <h4 class="font-bold text-teal-900 text-sm">Panduan Pencadangan & Pemulihan (Disaster Recovery):</h4>
                <ul class="list-disc list-inside space-y-1 text-teal-900/90 leading-relaxed pt-1">
                    <li><strong>Disarankan Rutin:</strong> Unduh berkas cadangan database minimal satu kali seminggu atau setiap kali selesai melakukan penilaian tugas akhir semester.</li>
                    <li><strong>Simpan di Luar Server:</strong> Simpan file cadangan yang diunduh ke cloud pribadi Anda (Google Drive / OneDrive) atau penyimpanan offline (flashdisk).</li>
                    <li><strong>Proses Pemulihan Mudah:</strong> Berkas cadangan <code>.sqlite</code> ini dapat langsung digunakan jika Anda berganti laptop atau memindahkan aplikasi ke hosting lain tanpa perlu proses konversi yang rumit.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
