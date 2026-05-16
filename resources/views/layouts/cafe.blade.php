@php
    $pageTitle = trim($__env->yieldContent('title')) ?: 'AMIKOSPACE Coffee & Tea';
    $pageDescription = trim($__env->yieldContent('description')) ?: 'Aplikasi reservasi cafe AMIKOSPACE Coffee & Tea.';
    $activePage = trim($__env->yieldContent('active_page')) ?: 'landing';
@endphp
@include('components.head', ['pageTitle' => $pageTitle, 'pageDescription' => $pageDescription])
@include('components.navbar', ['activePage' => $activePage])
@yield('content')
@include('components.footer')
