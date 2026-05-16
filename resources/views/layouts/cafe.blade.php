@php
    $pageTitle = trim($__env->yieldContent('title')) ?: config('cafe.name', config('app.name'));
    $pageDescription = trim($__env->yieldContent('description')) ?: config('cafe.default_description');
    $activePage = trim($__env->yieldContent('active_page')) ?: 'landing';
@endphp
@include('components.head', ['pageTitle' => $pageTitle, 'pageDescription' => $pageDescription])
@include('components.navbar', ['activePage' => $activePage])
@yield('content')
@include('components.footer')
