@props(['title' => 'Dashboard', 'breadcrumbs' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#1e40af">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="manifest" href="{{ asset('build/manifest.webmanifest') }}">

    <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name', 'SIM Penugasan Kuliah') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const theme = savedTheme ?
                savedTheme :
                (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="min-h-screen bg-base-200" x-data="{ sidebarOpen: true, sidebarMobileOpen: false }">

    {{-- Mobile sidebar overlay --}}
    <div x-show="sidebarMobileOpen" x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-black/50 lg:hidden"
        @click="sidebarMobileOpen = false" x-cloak>
    </div>

    {{-- Sidebar --}}
    <x-layouts.sidebar />

    {{-- Main content area --}}
    <div class="transition-all duration-300 lg:ml-64" :class="{ 'lg:ml-64': sidebarOpen, 'lg:ml-0': !sidebarOpen }">

        {{-- Top navbar --}}
        <x-layouts.navbar :title="$title ?? 'Dashboard'" />

        {{-- Page content --}}
        <main class="p-4 md:p-6 lg:p-8 min-h-screen">
            {{-- Breadcrumb --}}
            @if (isset($breadcrumbs))
                <x-layouts.breadcrumb :items="$breadcrumbs" />
            @endif

            {{-- Flash messages --}}
            <x-ui.toast />

            {{-- Page header --}}
            @if (isset($header))
                <div class="mb-6">
                    {{ $header }}
                </div>
            @endif

            {{-- Main slot --}}
            {{ $slot }}

            {{-- Floating Action Button --}}
            <x-ui.context-fab />

        </main>

        {{-- Footer --}}
        <x-layouts.footer />
    </div>

    {{-- Global delete confirmation modal --}}
    <x-ui.confirm-delete />

    @stack('modals')
    @stack('scripts')
</body>

</html>
