{{-- Info filter aktif --}}
@if ($hasActiveFilter)
    <div class="alert alert-info py-2">
        <div class="flex w-full flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
            <div class="text-sm">
                Filter sedang aktif.
                @if (filled(request('search')))
                    <span class="ml-1">
                        Kata kunci:
                        <span class="font-semibold">"{{ request('search') }}"</span>
                    </span>
                @endif
            </div>

            <a href="{{ url()->current() }}" class="btn btn-xs btn-ghost">
                Reset Filter
            </a>
        </div>
    </div>
@endif
