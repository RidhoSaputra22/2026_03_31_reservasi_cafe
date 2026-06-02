@props([
    'class' => 'h-10 w-auto rounded-full object-cover',
])

<img src="{{ asset('assets/images/logo.jpg') }}" alt="Cafe Amiko" class="{{ $class }}">
