        <header class="workspace-header border-b border-slate-200/80 bg-white/90 backdrop-blur-md">
            <nav aria-label="Navigasi atas"
                class="flex min-h-[4rem] sm:min-h-[4.5rem] items-center justify-between gap-2.5 sm:gap-4 px-3 sm:px-6">
                <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                    @if ($showSidebar)
                        <button id="sidebar-toggle-mobile" type="button" aria-controls="workspace-sidebar"
                            aria-expanded="false" aria-label="Buka menu samping"
                            class="sidebar-toggle-mobile inline-flex h-9 w-9 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition focus:outline-none focus:ring-2 focus:ring-teal-700 shadow-2xs">
                            <svg data-sidebar-icon="menu" aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg data-sidebar-icon="close" aria-hidden="true" class="hidden h-5 w-5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
                            </svg>
                        </button>
                    @endif
                    <a href="{{ route('home') }}" aria-label="ELT Academic Hub"
                        class="inline-flex min-w-0 shrink-0 items-center gap-2 sm:gap-3">
                        <img src="{{ asset('apple-touch-icon.png') }}" width="48" height="48"
                            alt="Logo Academia Hub" class="block h-8 w-8 shrink-0 object-contain sm:h-10 sm:w-10 md:h-11 md:w-11">
                        <span class="flex min-w-0 flex-col leading-tight">
                            <span class="truncate text-sm font-extrabold tracking-tight sm:text-base md:text-lg"><span
                                    class="text-[#05285c]">Academic</span> <span
                                    class="text-[#00a98f]">Workspace</span></span>
                            <span
                                class="hidden md:block truncate text-[10px] font-bold uppercase tracking-[.08em] text-slate-500">SISTEM
                                INFORMASI KINERJA DOSEN</span>
                        </span>
                    </a>
                </div>
                <div class="flex shrink-0 items-center gap-2 sm:gap-3.5 text-sm">
                    @auth
                        @if (!$showSidebar)
                            <a href="{{ route('dashboard') }}"
                                class="hidden sm:inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-slate-900 transition">
                                <svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4 text-teal-600" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z" />
                                </svg>
                                <span>Dashboard</span>
                            </a>
                        @endif

                        {{-- Dropdown Bell Notifikasi --}}
                        @php
                            $userUnreadCount = auth()->user()->unreadNotifications()->count();
                            $recentNotifications = auth()->user()->notifications()->latest()->take(5)->get();
                        @endphp
                        <div class="relative" x-data="{ bellOpen: false }" @click.outside="bellOpen = false">
                            <button type="button" @click="bellOpen = !bellOpen"
                                class="relative inline-flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition focus:outline-none focus:ring-2 focus:ring-teal-700"
                                aria-label="Notifikasi">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                @if ($userUnreadCount > 0)
                                    <span
                                        class="absolute -top-1 -right-1 flex h-4.5 min-w-4.5 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-bold text-white shadow-xs animate-pulse">
                                        {{ $userUnreadCount > 9 ? '9+' : $userUnreadCount }}
                                    </span>
                                @endif
                            </button>

                            {{-- Backdrop gelap di mobile saat notifikasi terbuka --}}
                            <div x-show="bellOpen" x-transition.opacity
                                class="fixed inset-0 z-40 bg-slate-900/30 backdrop-blur-2xs sm:hidden"
                                @click="bellOpen = false"></div>

                            {{-- Panel Notifikasi Responsif (Fixed di mobile, Absolute di desktop) --}}
                            <div x-show="bellOpen" x-cloak style="display: none;"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-2 sm:translate-y-0 sm:scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave-end="opacity-0 -translate-y-2 sm:translate-y-0 sm:scale-95"
                                class="fixed inset-x-3 top-[4.25rem] sm:top-full mt-2 max-w-sm mx-auto sm:max-w-none sm:mx-0 sm:absolute sm:inset-x-auto sm:right-0 sm:w-96 rounded-2xl border border-slate-200 bg-white shadow-2xl z-50 overflow-hidden text-slate-800">

                                <div
                                    class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900 text-xs">Notifikasi</span>
                                        @if ($userUnreadCount > 0)
                                            <span
                                                class="rounded-full bg-teal-100 px-2 py-0.5 text-[10px] font-bold text-teal-800">
                                                {{ $userUnreadCount }} Baru
                                            </span>
                                        @endif
                                    </div>
                                    @if ($userUnreadCount > 0)
                                        <form method="post" action="{{ route('notifications.mark-all-read') }}">
                                            @csrf
                                            <button type="submit"
                                                class="text-xs font-semibold text-teal-800 hover:text-teal-900">
                                                Tandai semua dibaca
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                                    @forelse($recentNotifications as $notif)
                                        @php
                                            $isUnread = $notif->read_at === null;
                                            $data = $notif->data;
                                        @endphp
                                        <a href="{{ route('notifications.read', ['id' => $notif->id]) }}"
                                            class="block p-3.5 hover:bg-slate-50 transition {{ $isUnread ? 'bg-teal-50/40' : '' }}">
                                            <div class="flex items-start gap-3">
                                                <div
                                                    class="h-8 w-8 rounded-xl shrink-0 flex items-center justify-center text-xs font-bold
                                                    {{ ($data['icon'] ?? '') === 'grade' ? 'bg-amber-100 text-amber-800' : (($data['icon'] ?? '') === 'attendance' ? 'bg-emerald-100 text-emerald-800' : 'bg-teal-100 text-teal-800') }}">
                                                    @if (($data['icon'] ?? '') === 'grade')
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                        </svg>
                                                    @elseif(($data['icon'] ?? '') === 'attendance')
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    @else
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span
                                                            class="text-xs font-bold truncate {{ $isUnread ? 'text-teal-950 font-extrabold' : 'text-slate-800' }}">
                                                            {{ $data['title'] ?? 'Pemberitahuan' }}
                                                        </span>
                                                        <span class="text-[11px] text-slate-400 shrink-0">
                                                            {{ $notif->created_at->diffForHumans(null, true) }}
                                                        </span>
                                                    </div>
                                                    <p class="text-xs text-slate-600 line-clamp-2 mt-0.5 leading-snug">
                                                        {{ $data['message'] ?? '' }}
                                                    </p>
                                                </div>
                                                @if ($isUnread)
                                                    <span class="h-2 w-2 rounded-full bg-teal-600 shrink-0 mt-1.5"></span>
                                                @endif
                                            </div>
                                        </a>
                                    @empty
                                        <div class="p-6 text-center text-xs text-slate-400">
                                            Tidak ada notifikasi saat ini.
                                        </div>
                                    @endforelse
                                </div>

                                <div class="border-t border-slate-100 bg-slate-50 px-4 py-2.5 text-center">
                                    <a href="{{ route('notifications.index') }}"
                                        class="text-xs font-bold text-teal-800 hover:text-teal-900">
                                        Lihat Seluruh Notifikasi &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Dropdown Menu Pengguna (Profil & Logout) --}}
                        <div class="relative" x-data="{ profileOpen: false }" @click.outside="profileOpen = false">
                            <button type="button" @click="profileOpen = !profileOpen"
                                class="flex min-w-0 items-center gap-2 rounded-xl border border-transparent p-1 sm:px-2.5 sm:py-1.5 text-slate-700 hover:border-slate-200 hover:bg-slate-50 transition focus:outline-none focus:ring-2 focus:ring-teal-700"
                                :class="{ 'bg-slate-100 border-slate-200': profileOpen }" aria-haspopup="true"
                                :aria-expanded="profileOpen" aria-label="Menu akun {{ auth()->user()->name }}">
                                @if (auth()->user()->profile_photo_path)
                                    <img src="{{ Storage::disk('public')->url(auth()->user()->profile_photo_path) }}"
                                        alt=""
                                        class="h-9 w-9 shrink-0 rounded-full object-cover ring-2 ring-teal-500/20">
                                @else
                                    <span
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-teal-100 text-xs font-bold text-teal-900 ring-2 ring-teal-500/20">{{ auth()->user()->initials() }}</span>
                                @endif
                                <span
                                    class="hidden max-w-40 truncate text-sm font-semibold text-slate-800 sm:inline">{{ auth()->user()->name }}</span>
                                <svg class="hidden sm:block h-4 w-4 text-slate-400 transition-transform duration-200"
                                    :class="{ 'rotate-180': profileOpen }" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            {{-- Backdrop gelap di mobile saat dropdown profil terbuka --}}
                            <div x-show="profileOpen" x-transition.opacity
                                class="fixed inset-0 z-40 bg-slate-900/20 backdrop-blur-2xs sm:hidden"
                                @click="profileOpen = false"></div>

                            {{-- Panel Menu Pengguna --}}
                            <div x-show="profileOpen" x-cloak style="display: none;"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                class="absolute right-0 mt-2 w-64 max-w-[calc(100vw-1.5rem)] rounded-2xl border border-slate-200 bg-white shadow-2xl z-50 overflow-hidden text-slate-800 divide-y divide-slate-100">

                                {{-- Identitas Pengguna --}}
                                <div class="px-4 py-3 bg-slate-50/80">
                                    <div class="flex items-center gap-3">
                                        @if (auth()->user()->profile_photo_path)
                                            <img src="{{ Storage::disk('public')->url(auth()->user()->profile_photo_path) }}"
                                                alt=""
                                                class="h-10 w-10 shrink-0 rounded-full object-cover ring-2 ring-teal-500/20">
                                        @else
                                            <span
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-teal-100 text-sm font-bold text-teal-900 ring-2 ring-teal-500/20">
                                                {{ auth()->user()->initials() }}
                                            </span>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-bold text-slate-900 truncate leading-tight">
                                                {{ auth()->user()->name }}</p>
                                            <p class="text-xs text-slate-500 truncate mt-0.5">{{ auth()->user()->email }}
                                            </p>
                                        </div>
                                    </div>
                                    <div
                                        class="mt-2.5 flex items-center justify-between gap-2 border-t border-slate-200/60 pt-2 text-[11px]">
                                        <span
                                            class="inline-flex items-center rounded-md bg-teal-50 px-2 py-0.5 font-bold text-teal-800 ring-1 ring-inset ring-teal-600/20">
                                            {{ auth()->user()->roleLabel() }}
                                        </span>
                                        @if (auth()->user()->nim)
                                            <span class="font-mono text-slate-500">NIM: {{ auth()->user()->nim }}</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Tautan Navigasi Akun --}}
                                <div class="p-1.5 space-y-0.5">
                                    @if (Route::has('dashboard'))
                                        <a href="{{ route('dashboard') }}"
                                            class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition">
                                            <svg class="h-4 w-4 text-teal-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z" />
                                            </svg>
                                            <span>Dashboard Utama</span>
                                        </a>
                                    @endif
                                    <a href="{{ route('profile.edit') }}"
                                        class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition">
                                        <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span>Profil & Pengaturan Akun</span>
                                    </a>
                                </div>

                                {{-- Aksi Keluar (Logout) --}}
                                <div class="p-1.5">
                                    <form method="post" action="{{ route('logout') }}" class="w-full">
                                        @csrf
                                        <button type="submit"
                                            class="w-full flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition text-left">
                                            <svg class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            <span>Keluar</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}"
                            class="px-3.5 py-2 text-sm font-semibold text-slate-700 hover:text-[#05285c] transition">Masuk</a>
                        <a href="{{ route('register') }}"
                            class="rounded-xl bg-[#05285c] px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-[#041e45] transition">Daftar</a>
                    @endauth
                </div>
            </nav>
        </header>
