@php
    $pageTitle = $pageTitle ?? 'AMIKOSPACE Coffee & Tea';
    $pageDescription = $pageDescription ?? 'Aplikasi reservasi cafe AMIKOSPACE Coffee & Tea.';
@endphp
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $pageTitle }}</title>
  <meta name="description" content="{{ $pageDescription }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    html { scroll-behavior: smooth; }
    body { font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
    .hero-bg {
      background-image: linear-gradient(rgba(22,16,13,.56), rgba(22,16,13,.62)), url('{{ asset('assets/images/hero.jpg') }}');
      background-size: cover;
      background-position: center;
    }
    .about-bg {
      background-image: linear-gradient(rgba(22,16,13,.20), rgba(22,16,13,.34)), url('{{ asset('assets/images/about.png') }}');
      background-size: cover;
      background-position: center;
    }
  </style>
</head>
<body class="bg-coffee-50 text-coffee-900 antialiased">
