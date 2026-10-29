@extends('layouts.hub')
@section('title', 'Notifikasi Saya — Academic Hub')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    {{-- Header Halaman --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded-md bg-teal-50 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-teal-800 border border-teal-200">
                        Pusat Komunikasi
                    </span>
                    @if($unreadCount > 0)
                        <span class="rounded-full bg-rose-500 px-2 py-0.5 text-xs font-bold text-white shadow-xs">
                            {{ $unreadCount }} Belum Dibaca
                        </span>
                    @endif
                </div>
                <h1 class="mt-2 text-2xl font-bold text-slate-900">Notifikasi Akademik</h1>
                <p class="mt-1 text-xs text-slate-500">
                    Pantau informasi penting terkait penugasan, batas waktu draf, penilaian & umpan balik dosen, serta presensi kelas.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($unreadCount > 0)
                    <form method="post" action="{{ route('notifications.mark-all-read') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-teal-300 bg-teal-50 px-3.5 py-2 text-xs font-bold text-teal-800 hover:bg-teal-100 transition shadow-xs">
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
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 hover:text-rose-700 transition shadow-xs">
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
        <div class="mt-6 border-t border-slate-100 pt-4 flex flex-wrap gap-2 text-xs font-semibold">
            <a href="{{ route('notifications.index', ['filter' => 'all']) }}"
                class="rounded-xl px-3.5 py-1.5 transition {{ $currentFilter === 'all' ? 'bg-teal-800 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'unread']) }}"
                class="rounded-xl px-3.5 py-1.5 transition {{ $currentFilter === 'unread' ? 'bg-teal-800 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Belum Dibaca
                @if($unreadCount > 0)
                    <span class="ml-1 rounded-full bg-white/20 px-1.5 py-0.2 text-[10px]">{{ $unreadCount }}</span>
                @endif
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'assignment']) }}"
                class="rounded-xl px-3.5 py-1.5 transition {{ $currentFilter === 'assignment' ? 'bg-teal-800 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Tugas & Submisi
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'grade']) }}"
                class="rounded-xl px-3.5 py-1.5 transition {{ $currentFilter === 'grade' ? 'bg-teal-800 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Nilai & Evaluasi
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'attendance']) }}"
                class="rounded-xl px-3.5 py-1.5 transition {{ $currentFilter === 'attendance' ? 'bg-teal-800 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Presensi Sesi
            </a>
        </div>
    </div>

    {{-- Daftar Notifikasi --}}
    @if($notifications->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <h3 class="mt-4 text-base font-bold text-slate-900">Belum Ada Notifikasi</h3>
            <p class="mt-1 text-xs text-slate-500">
                @if($currentFilter !== 'all')
                    Tidak ada notifikasi pada kategori ini.
                @else
                    Semua kabar dan pembaruan kelas akademik akan muncul di sini.
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
                <div class="rounded-2xl border border-slate-200 p-4 transition shadow-xs flex flex-wrap items-center justify-between gap-4
                    {{ $isUnread ? 'bg-teal-50/30 border-l-4 border-l-teal-600' : 'bg-white border-l-4 border-l-slate-200' }}">
                    
                    <div class="flex items-start gap-3.5 min-w-0 flex-1">
                        <div class="h-10 w-10 shrink-0 rounded-xl flex items-center justify-center text-sm font-bold shadow-xs
                            @if($iconType === 'grade')
                                bg-amber-100 text-amber-800 border border-amber-200
                            @elseif($iconType === 'attendance')
                                bg-emerald-100 text-emerald-800 border border-emerald-200
                            @else
                                bg-teal-100 text-teal-800 border border-teal-200
                            @endif">
                            @if($iconType === 'grade')
                                ★
                            @elseif($iconType === 'attendance')
                                ✓
                            @else
                                ✎
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-bold text-slate-900 {{ $isUnread ? 'font-extrabold text-teal-950' : '' }}">
                                    {{ $data['title'] ?? 'Pemberitahuan' }}
                                </span>
                                @if($isUnread)
                                    <span class="rounded-full bg-teal-100 px-2 py-0.2 text-[10px] font-bold text-teal-800">
                                        Baru
                                    </span>
                                @endif
                                <span class="text-[11px] text-slate-400">·</span>
                                <span class="text-[11px] text-slate-500">
                                    {{ $notif->created_at->diffForHumans() }} ({{ $notif->created_at->format('d/m/Y H:i') }} WIB)
                                </span>
                            </div>

                            <p class="mt-1 text-xs text-slate-700 leading-relaxed">
                                {{ $data['message'] ?? '' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if(!empty($data['action_url']))
                            <a href="{{ route('notifications.read', ['id' => $notif->id]) }}"
                                class="inline-flex items-center gap-1 rounded-xl bg-teal-800 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-teal-700 transition shadow-xs">
                                <span>Buka</span>
                                <span>&rarr;</span>
                            </a>
                        @endif

                        <form method="post" action="{{ route('notifications.destroy', ['id' => $notif->id]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-xl p-1.5 text-slate-400 hover:text-rose-600 hover:bg-slate-100 transition" title="Hapus notifikasi">
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
