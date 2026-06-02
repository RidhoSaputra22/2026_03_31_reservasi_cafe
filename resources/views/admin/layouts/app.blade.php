@php
    $pageTitle = $title ?? 'Dashboard';
    $pageBreadcrumbs = $breadcrumbs ?? null;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="theme-color" content="#6f4b32">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="manifest" href="{{ asset('build/manifest.webmanifest') }}">

    <title>{{ $pageTitle }} - {{ config('app.name', 'AMIKOSPACE Admin') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <script>
        (function() {
            const theme = localStorage.getItem('admin-theme') || 'light';

            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="admin-shell" x-data="{
    sidebarOpen: true,
    sidebarMobileOpen: false,
    adminTheme: document.documentElement.getAttribute('data-theme') || 'light',
    toggleTheme() {
        this.adminTheme = this.adminTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', this.adminTheme);
        localStorage.setItem('admin-theme', this.adminTheme);
    }
}">
    <div x-show="sidebarMobileOpen" x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-black/50 lg:hidden"
        @click="sidebarMobileOpen = false" x-cloak>
    </div>

    <x-layouts.sidebar />

    <div class="transition-all duration-300 lg:ml-64" :class="{ 'lg:ml-64': sidebarOpen, 'lg:ml-0': !sidebarOpen }">
        <x-layouts.navbar :title="$pageTitle" />

        <main class="min-h-screen p-4 md:p-6 lg:p-8">
            @if (isset($pageBreadcrumbs))
                <x-layouts.breadcrumb :items="$pageBreadcrumbs" />
            @endif

            <x-ui.toast />

            @hasSection('header')
                <div class="mb-6">
                    @yield('header')
                </div>
            @endif

            @yield('content')

            <x-ui.context-fab />
        </main>

        <x-layouts.footer />
    </div>

    <x-ui.confirm-delete />

    @stack('modals')
    @stack('scripts')
</body>

</html>
