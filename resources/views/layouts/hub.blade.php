<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('apple-touch-icon.png') }}">
    <title>@yield('title', 'ELT Academic Hub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Newsreader:ital,opsz,wght@0,6..72,400..600;1,6..72,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen bg-slate-50/90 text-slate-900 antialiased selection:bg-teal-100 selection:text-teal-950">
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-xl focus:bg-white focus:px-4 focus:py-3 focus:font-semibold focus:shadow-lg">Lewati
        ke konten</a>
    @php
        $showSidebar = auth()->check() && ! request()->routeIs('home') && ! ($hideSidebar ?? false);
    @endphp
    <div class="{{ $showSidebar ? 'workspace-shell' : 'guest-shell' }}">
        @include('layouts.partials.workspace-header')
        @include('layouts.partials.workspace-sidebar')
        <main id="main-content" tabindex="-1" class="workspace-main min-w-0 px-4 py-7 sm:px-7 sm:py-9">
            <div class="mx-auto w-full max-w-7xl">
                @include('components.flash-messages')
                @yield('content')
            </div>
        </main>

        <footer
            class="workspace-footer border-t border-slate-200/80 bg-white px-5 py-5 text-xs sm:text-sm text-slate-500 sm:px-7">
            <div class="mx-auto flex w-full max-w-7xl flex-wrap items-center justify-between gap-2">
                <span class="font-medium text-slate-600">© {{ now()->year }} Academia Workspace · ELT Academic Hub</span>
                <span>Mendukung Tri Dharma: Pendidikan, Penelitian, dan Pengabdian.</span>
            </div>
        </footer>
    </div>
</body>

</html>
