@php
    $items = $items();
    $isPaginated = $isPaginated();
    $totalItems = $isPaginated && method_exists($data, 'total')
        ? $data->total()
        : $items->count();

    $rowIds = collect(method_exists($data, 'items') ? $data->items() : $data)
        ->map(fn ($row) => (string) data_get($row, $rowKey))
        ->values();

    $colspan = count($columns) + ($selectable ? 1 : 0) + ($actions ? 1 : 0);

    $filterKeys = ['search', 'dosen', 'hari', 'semester'];
    $hasActiveFilter = collect($filterKeys)->contains(fn ($key) => filled(request($key)));
@endphp

<div id="{{ Str::slug($title) }}"
    x-data="dataTableComponent({
        rowIds: @js($rowIds),
    })"
    @keydown.window.shift="isShiftPressed = true"
    @keyup.window.shift="isShiftPressed = false"
    class="space-y-4"
>
    @include('components.ui.partials.data-table.header')

    @include('components.ui.partials.data-table.filter-info')

    <div class="flex flex-wrap gap-4 items-center justify-between">
        @include('components.ui.partials.data-table.bulk-actions')
        @include('components.ui.partials.data-table.pagination')
    </div>

    @include('components.ui.partials.data-table.table')

    @include('components.ui.partials.data-table.mobile-card')

    @include('components.ui.partials.data-table.empty-state')

    @include('components.ui.partials.data-table.pagination')

    @include('components.ui.partials.data-table.scripts')
</div>

