@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex justify-end">
        <div class="join">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="join-item btn btn-sm btn-disabled">«</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="join-item btn btn-sm">
                    «
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- Separator --}}
                @if (is_string($element))
                    <span class="join-item btn btn-sm btn-disabled">{{ $element }}</span>
                @endif

                {{-- Page Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="join-item btn btn-sm btn-active bg-primary text-primary-content border-primary">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                               class="join-item btn btn-sm btn-outline border-base-content/20"
                               aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="join-item btn btn-sm">
                    »
                </a>
            @else
                <span class="join-item btn btn-sm btn-disabled">»</span>
            @endif

        </div>
    </nav>
@endif
