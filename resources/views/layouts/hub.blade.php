<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('apple-touch-icon.png') }}">
    <title>@yield('title', 'ELT Academic Hub')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-3 focus:shadow-lg">Lewati
        ke konten</a>
    @php
        $showSidebar = auth()->check() && ! request()->routeIs('home') && ! ($hideSidebar ?? false);
    @endphp
    <div class="{{ $showSidebar ? 'workspace-shell' : 'guest-shell' }}">
        <header class="workspace-header border-b border-slate-200 bg-white">
            <nav aria-label="Navigasi atas" class="flex min-h-[4.5rem] items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    @if ($showSidebar)
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
                    @endif
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
                        @if (! $showSidebar)
                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 hover:text-slate-900 transition">
                                <svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4 text-teal-600" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z" />
                                </svg>
                                <span>Dashboard</span>
                            </a>
                        @endif
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
                        <a href="{{ route('login') }}" class="px-3 py-2 text-sm font-semibold text-slate-700 hover:text-[#05285c] transition">Masuk</a>
                        <a href="{{ route('register') }}"
                            class="rounded-xl bg-[#05285c] px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-[#041e45] transition">Daftar</a>
                    @endauth
                </div>
            </nav>
        </header>

        @if ($showSidebar)
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
                        <a href="{{ route('courses.index') }}"
                            @if (request()->routeIs('courses.*')) aria-current="page" @endif title="Mata Kuliah"
                            class="sidebar-link {{ request()->routeIs('courses.*') ? 'sidebar-link-active' : '' }}">
                            <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                            </svg><span data-sidebar-label>Mata Kuliah</span>
                        </a>
                        @if (auth()->user()->isStaff())
                            <a href="{{ route('students.index') }}"
                                @if (request()->routeIs('students.*')) aria-current="page" @endif title="Mahasiswa"
                                class="sidebar-link {{ request()->routeIs('students.*') ? 'sidebar-link-active' : '' }}">
                                <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                                </svg><span data-sidebar-label>Mahasiswa</span>
                            </a>
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
                        @if (auth()->user()->isStaff())
                            <a href="{{ route('dashboard') }}#modules-heading" title="Modul" class="sidebar-link">
                                <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none"
                                    stroke="currentColor" stroke-width="1.8">
                                    <rect x="4" y="4" width="6" height="6" rx="1" />
                                    <rect x="14" y="4" width="6" height="6" rx="1" />
                                    <rect x="4" y="14" width="6" height="6" rx="1" />
                                    <rect x="14" y="14" width="6" height="6" rx="1" />
                                </svg><span data-sidebar-label>Modul</span>
                            </a>
                        @endif
                        @if (auth()->user()->role === 'superadmin')
                            <a href="{{ route('academic-structure.index') }}"
                                @if (request()->routeIs('academic-structure.*')) aria-current="page" @endif title="Fakultas & Prodi"
                                class="sidebar-link {{ request()->routeIs('academic-structure.*') ? 'sidebar-link-active' : '' }}">
                                <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                                </svg><span data-sidebar-label>Fakultas & Prodi</span>
                            </a>
                            <a href="{{ route('users.index') }}"
                                @if (request()->routeIs('users.*')) aria-current="page" @endif title="Manajemen Pengguna"
                                class="sidebar-link {{ request()->routeIs('users.*') ? 'sidebar-link-active' : '' }}">
                                <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg><span data-sidebar-label>Manajemen Pengguna</span>
                            </a>
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
        @endif

        <main id="main-content" tabindex="-1" class="workspace-main min-w-0 px-5 py-8 sm:px-7 sm:py-10">
            <div class="mx-auto w-full max-w-7xl">
                @include('components.flash-messages')
                @yield('content')
            </div>
        </main>

        <footer
            class="workspace-footer {{ $showSidebar ? 'fixed' : 'relative mt-auto' }} border-t border-slate-200 bg-white px-5 py-5 text-sm text-slate-500 sm:px-7">
            <div class="mx-auto flex w-full max-w-7xl flex-wrap items-center justify-between gap-2"><span>©
                    {{ now()->year }} Academia Workspace · ELT Academic Hub</span><span>Mendukung Tri Dharma: Pendidikan, Penelitian, dan
                    Pengabdian.</span></div>
        </footer>
    </div>
</body>

</html>
