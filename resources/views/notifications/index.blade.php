@extends('layouts.hub')
@section('title', 'Notifikasi Saya — Academic Hub')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    {{-- Header Halaman --}}
    <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-7 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-teal-800 border border-teal-200/80">
                        <span class="h-1.5 w-1.5 rounded-full bg-teal-600"></span>
                        Pusat Komunikasi
                    </span>
                    @if($unreadCount > 0)
                        <span class="rounded-full bg-rose-500 px-2.5 py-0.5 text-xs font-bold text-white shadow-xs">
                            {{ $unreadCount }} Belum Dibaca
                        </span>
                    @endif
                </div>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Notifikasi Akademik</h1>
                <p class="mt-1 text-sm text-slate-600 max-w-2xl leading-relaxed">
                    Pantau informasi penting terkait penugasan, batas waktu draf, penilaian & umpan balik dosen, serta presensi kelas.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                @if($unreadCount > 0)
                    <form method="post" action="{{ route('notifications.mark-all-read') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-teal-300 bg-teal-50 px-4 py-2 text-xs sm:text-sm font-bold text-teal-900 hover:bg-teal-100 transition shadow-xs">
                            <svg class="h-4 w-4 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Tandai Semua Dibaca
                        </button>
                    </form>
                @endif

                @if($notifications->total() > 0)
                    <form method="post" action="{{ route('notifications.clear-all') }}" onsubmit="return confirm('Hapus seluruh riwayat notifikasi Anda?')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-rose-700 transition shadow-xs">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Bersihkan Riwayat
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div class="mt-6 border-t border-slate-100 pt-4 flex flex-wrap gap-2 text-xs sm:text-sm font-semibold">
            <a href="{{ route('notifications.index', ['filter' => 'all']) }}"
                class="rounded-xl px-4 py-2 transition {{ $currentFilter === 'all' ? 'bg-teal-800 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Semua
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'unread']) }}"
                class="rounded-xl px-4 py-2 transition inline-flex items-center gap-1.5 {{ $currentFilter === 'unread' ? 'bg-teal-800 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                <span>Belum Dibaca</span>
                @if($unreadCount > 0)
                    <span class="rounded-full bg-white/20 px-2 py-0.5 text-[11px] font-bold">{{ $unreadCount }}</span>
                @endif
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'assignment']) }}"
                class="rounded-xl px-4 py-2 transition {{ $currentFilter === 'assignment' ? 'bg-teal-800 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Tugas & Submisi
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'grade']) }}"
                class="rounded-xl px-4 py-2 transition {{ $currentFilter === 'grade' ? 'bg-teal-800 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Nilai & Evaluasi
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'attendance']) }}"
                class="rounded-xl px-4 py-2 transition {{ $currentFilter === 'attendance' ? 'bg-teal-800 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Presensi Sesi
            </a>
        </div>
    </div>

    {{-- Daftar Notifikasi --}}
    @if($notifications->isEmpty())
        <div class="rounded-3xl border border-slate-200/90 bg-white p-12 text-center shadow-sm">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-teal-50 text-teal-700">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-bold text-slate-900">Belum Ada Notifikasi</h3>
            <p class="mt-1 text-sm text-slate-500 max-w-md mx-auto">
                @if($currentFilter !== 'all')
                    Tidak ada notifikasi pada kategori ini. Coba pilih filter lain untuk melihat riwayat aktivitas.
                @else
                    Semua kabar dan pembaruan kelas akademik akan muncul di sini secara otomatis.
                @endif
            </p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($notifications as $notif)
                @php
                    $isUnread = $notif->read_at === null;
                    $data = $notif->data;
                    $iconType = $data['icon'] ?? 'bell';
                @endphp
                <div class="rounded-2xl border transition shadow-xs flex flex-wrap items-center justify-between gap-4 p-5 sm:p-6
                    {{ $isUnread ? 'bg-white border-l-4 border-l-teal-600 border-slate-200/90 shadow-sm' : 'bg-white/80 border-l-4 border-l-slate-200 border-slate-200/80 hover:bg-white' }}">
                    
                    <div class="flex items-start gap-4 min-w-0 flex-1">
                        <div class="h-11 w-11 shrink-0 rounded-2xl flex items-center justify-center text-sm font-bold shadow-xs
                            @if($iconType === 'grade')
                                bg-amber-50 text-amber-700 border border-amber-200
                            @elseif($iconType === 'attendance')
                                bg-emerald-50 text-emerald-700 border border-emerald-200
                            @else
                                bg-teal-50 text-teal-700 border border-teal-200
                            @endif">
                            @if($iconType === 'grade')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            @elseif($iconType === 'attendance')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @else
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-bold text-slate-900 {{ $isUnread ? 'font-extrabold text-teal-950' : '' }}">
                                    {{ $data['title'] ?? 'Pemberitahuan' }}
                                </span>
                                @if($isUnread)
                                    <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-[10px] font-bold text-teal-900">
                                        Baru
                                    </span>
                                @endif
                                <span class="text-xs text-slate-300">·</span>
                                <span class="text-xs text-slate-500 font-medium">
                                    {{ $notif->created_at->diffForHumans() }} ({{ $notif->created_at->format('d/m/Y H:i') }} WIB)
                                </span>
                            </div>

                            <p class="mt-1.5 text-sm text-slate-700 leading-relaxed">
                                {{ $data['message'] ?? '' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        @if(!empty($data['action_url']))
                            <a href="{{ route('notifications.read', ['id' => $notif->id]) }}"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-4 py-2 text-xs sm:text-sm font-bold text-white hover:bg-teal-700 transition shadow-xs">
                                <span>Buka</span>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        @endif

                        <form method="post" action="{{ route('notifications.destroy', ['id' => $notif->id]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-xl p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus notifikasi">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
