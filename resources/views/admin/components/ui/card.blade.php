{{--
    Reusable Card Component

    @param string $title - Card title (optional)
    @param string $class - Additional CSS classes (optional)
    @param bool $compact - Use compact padding (optional)
    @param string $href - If provided, the entire card becomes a link (optional)
    @slot actions - Optional slot for action buttons/links
--}}

@props([
    'title' => null,
    'class' => '',
    'compact' => false,
    'href' => null,
])

@php
    $hoverClass = $href
        ? ' cursor-pointer transition duration-200 hover:-translate-y-0.5 hover:shadow-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40'
        : '';
@endphp

<div
    {{ $attributes->merge(['class' => 'card bg-base-100 shadow-xl ' . $class . $hoverClass]) }}
    @if ($href)
        role="link"
        tabindex="0"
        data-card-href="{{ $href }}"
        aria-label="{{ $title ? 'Buka ' . $title : 'Buka card' }}"
        onclick="if (!event.target.closest('a, button, input, select, textarea, summary, label, [role=button], [role=link]')) { window.location.href = this.dataset.cardHref; }"
        onkeydown="if ((event.key === 'Enter' || event.key === ' ') && !event.target.closest('a, button, input, select, textarea, summary, label, [role=button], [role=link]')) { event.preventDefault(); window.location.href = this.dataset.cardHref; }"
    @endif
>
    <div class="card-body {{ $compact ? 'p-4' : '' }}">
        @if ($title)
            <h2 class="card-title">{{ $title }}</h2>
        @endif
        {{ $slot }}
        @if (isset($actions))
            <div class="card-actions justify-end mt-4">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
