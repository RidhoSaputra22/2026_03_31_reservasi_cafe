@php
    $pageTitle = $pageTitle ?? config('cafe.name', config('app.name'));
    $pageDescription = $pageDescription ?? config('cafe.default_description');
@endphp
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $pageTitle }}</title>
  <meta name="description" content="{{ $pageDescription }}">
  <script>
    window.tailwind = window.tailwind || {};
    window.tailwind.config = {
      theme: {
        extend: {
          colors: {
            coffee: {
              50: '#fbf7f0',
              100: '#f1e6d4',
              200: '#d7c0a0',
              300: '#b9956c',
              400: '#8a623d',
              500: '#6f4b32',
              600: '#563927',
              700: '#3d2a1f',
              800: '#271b15',
              900: '#16100d'
            }
          },
          boxShadow: {
            soft: '0 18px 60px rgba(39, 27, 21, 0.16)'
          }
        }
      }
    };
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    html { scroll-behavior: smooth; }
    body { font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
    .hero-bg {
      background-image: linear-gradient(rgba(22,16,13,.56), rgba(22,16,13,.62)), url('{{ asset('assets/images/hero.png') }}');
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
