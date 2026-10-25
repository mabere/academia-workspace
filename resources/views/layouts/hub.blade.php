<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('apple-touch-icon.png') }}">
    <title>@yield('title', 'ELT Academic Hub')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-3 focus:shadow-lg">Lewati
        ke konten</a>
    <div class="{{ auth()->check() ? 'workspace-shell' : 'guest-shell' }}">
        <header class="workspace-header border-b border-slate-200 bg-white">
            <nav aria-label="Navigasi atas" class="flex min-h-[4.5rem] items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    @auth
                        <button id="sidebar-toggle-mobile" type="button" aria-controls="workspace-sidebar"
                            aria-expanded="false" aria-label="Ciutkan menu samping"
                            class="sidebar-toggle-mobile inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-teal-700">
                            <svg data-sidebar-icon="menu" aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg data-sidebar-icon="close" aria-hidden="true" class="hidden h-5 w-5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
                            </svg>
                        </button>
                    @endauth
                    <a href="{{ route('home') }}" aria-label="ELT Academic Hub"
                        class="inline-flex min-w-0 shrink items-center gap-2 sm:gap-3">
                        <img src="{{ asset('apple-touch-icon.png') }}" width="48" height="48" alt=""
                            class="block h-10 w-10 shrink-0 object-contain sm:h-12 sm:w-12">
                        <span class="hidden min-w-0 flex-col leading-tight sm:flex">
                            <span class="truncate text-base font-bold tracking-tight sm:text-lg"><span class="text-[#05285c]">Academic</span> <span class="text-[#00a98f]">Workspace</span></span>
                            <span class="truncate text-[9px] font-semibold uppercase tracking-[.08em] text-[#05285c]">SISTEM INFORMASI KINERJA DOSEN</span>
                        </span>
                    </a>
                </div>
                <div class="flex shrink-0 items-center gap-3 text-sm sm:gap-4">
                    @auth
                        <a href="{{ route('profile.edit') }}"
                            class="flex min-w-0 items-center gap-2 rounded-lg px-2 py-1.5 text-slate-700 hover:bg-slate-50"
                            aria-label="Edit profil {{ auth()->user()->name }}">
                            @if (auth()->user()->profile_photo_path)
                                <img src="{{ Storage::disk('public')->url(auth()->user()->profile_photo_path) }}"
                                    alt="" class="h-9 w-9 shrink-0 rounded-full object-cover">
                            @else
                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-teal-100 text-xs font-bold text-teal-900">{{ auth()->user()->initials() }}</span>
                            @endif
                            <span class="hidden max-w-40 truncate text-sm sm:inline">{{ auth()->user()->name }}</span>
                        </a>
                        <form method="post" action="{{ route('logout') }}">@csrf<button
                                class="rounded-lg border border-slate-200 px-3 py-2 font-medium hover:bg-slate-50">Keluar</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="font-medium text-slate-700 hover:text-teal-700">Masuk</a>
                        <a href="{{ route('register') }}"
                            class="rounded-lg bg-slate-900 px-3 py-2 font-medium text-white hover:bg-slate-700">Daftar</a>
                    @endauth
                </div>
            </nav>
        </header>

        @auth
            <button id="sidebar-backdrop" type="button" aria-label="Tutup menu samping" class="sidebar-backdrop"
                tabindex="-1"></button>
            <aside id="workspace-sidebar" class="workspace-sidebar border-r border-slate-200 bg-white"
                aria-label="Menu samping">
                <div class="sidebar-inner flex h-full flex-col overflow-y-auto px-3 py-5">
                    <div class="sidebar-heading mb-3 flex min-h-10 items-center justify-between gap-2 px-3">
                        <p data-sidebar-label class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                            Workspace</p>
                        <button id="sidebar-toggle" type="button" aria-controls="workspace-sidebar" aria-expanded="true"
                            aria-label="Ciutkan menu samping"
                            class="sidebar-toggle-desktop inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-teal-700">
                            <svg data-sidebar-icon="menu" aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg data-sidebar-icon="close" aria-hidden="true" class="hidden h-5 w-5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
                            </svg>
                        </button>
                    </div>
                    <nav aria-label="Menu workspace" class="space-y-1">
                        <a href="{{ route('dashboard') }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif
                            title="Dashboard"
                            class="sidebar-link {{ request()->routeIs('dashboard') ? 'sidebar-link-active' : '' }}">
                            <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z" />
                            </svg><span data-sidebar-label>Dashboard</span>
                        </a>
                        @if (auth()->user()->isStaff())
                            <a href="{{ route('projects.index') }}"
                                @if (request()->routeIs('projects.*')) aria-current="page" @endif title="Proyek"
                                class="sidebar-link {{ request()->routeIs('projects.*') ? 'sidebar-link-active' : '' }}">
                                <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 7.5A1.5 1.5 0 0 1 4.5 6H10l2 2h7.5A1.5 1.5 0 0 1 21 9.5v8a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 17.5z" />
                                </svg><span data-sidebar-label>Proyek</span>
                            </a>
                            <a href="{{ route('outputs.index') }}"
                                @if (request()->routeIs('outputs.*')) aria-current="page" @endif title="Output & Evidence"
                                class="sidebar-link {{ request()->routeIs('outputs.*') ? 'sidebar-link-active' : '' }}">
                                <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 3.75h7l4 4V20a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4.75a1 1 0 0 1 1-1z" />
                                    <path stroke-linecap="round" d="M14 4v4h4M9 13h6M9 17h6" />
                                </svg><span data-sidebar-label>Output & Evidence</span>
                            </a>
                        @endif
                        <a href="{{ route('dashboard') }}#modules-heading" title="Modul" class="sidebar-link">
                            <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none"
                                stroke="currentColor" stroke-width="1.8">
                                <rect x="4" y="4" width="6" height="6" rx="1" />
                                <rect x="14" y="4" width="6" height="6" rx="1" />
                                <rect x="4" y="14" width="6" height="6" rx="1" />
                                <rect x="14" y="14" width="6" height="6" rx="1" />
                            </svg><span data-sidebar-label>Modul</span>
                        </a>
                        @if (auth()->user()->role === 'superadmin')
                            <a href="{{ route('corpuslab.reviews') }}"
                                @if (request()->routeIs('corpuslab.reviews')) aria-current="page" @endif title="Review protokol korpus"
                                class="sidebar-link {{ request()->routeIs('corpuslab.reviews') ? 'sidebar-link-active' : '' }}">
                                <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M5 4h14v16H5zM8 9h8M8 13h8M8 17h5" />
                                </svg><span data-sidebar-label>Review protokol</span>
                            </a>
                        @endif
                        @if (auth()->user()->role === 'student')
                            <a href="{{ route('dashboard') }}#student-work-heading" title="Tugas saya"
                                class="sidebar-link">
                                <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 3.75h7l4 4V20a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4.75a1 1 0 0 1 1-1z" />
                                    <path stroke-linecap="round" d="M14 4v4h4M9 13h6M9 17h6" />
                                </svg><span data-sidebar-label>Tugas saya</span>
                            </a>
                        @endif
                    </nav>
                    <div
                        class="mt-auto flex items-center gap-3 border-t border-slate-100 px-3 pt-5 text-xs leading-5 text-slate-500">
                        <img src="{{ asset('apple-touch-icon.png') }}?v=2" width="32" height="32" alt=""
                            class="h-8 w-8 shrink-0">
                        <span data-sidebar-label>ELT Academic Hub<br>Ruang kerja Tri Dharma</span>
                    </div>
                </div>
            </aside>
        @endauth

        <main id="main-content" tabindex="-1" class="workspace-main min-w-0 px-5 py-8 sm:px-7 sm:py-10">
            <div class="mx-auto w-full max-w-7xl">
                @include('components.flash-messages')
                @yield('content')
            </div>
        </main>

        <footer
            class="workspace-footer fixed border-t border-slate-200 bg-white px-5 py-5 text-sm text-slate-500 sm:px-7">
            <div class="mx-auto flex w-full max-w-7xl flex-wrap items-center justify-between gap-2"><span>©
                    {{ now()->year }} ELT Academic Hub</span><span>Mendukung pendidikan, penelitian, dan
                    pengabdian.</span></div>
        </footer>
    </div>
</body>

</html>
