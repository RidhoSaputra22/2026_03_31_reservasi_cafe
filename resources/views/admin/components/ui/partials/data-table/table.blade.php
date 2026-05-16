@if ($items->count() > 0)
    <div class="hidden overflow-x-auto md:block" :class="{ 'select-none': isShiftPressed }" >

        <table class="table table-zebra w-full">
            <thead>
                <tr>
                    @if ($selectable)
                        <th class="w-10">
                            <input
                                type="checkbox"
                                value="1"
                                class="checkbox checkbox-primary checkbox-sm"
                                x-bind:checked="isAllSelected"
                                aria-label="Pilih semua data"
                                x-on:mousedown.shift.prevent="$event.preventDefault()"
                                x-on:click="toggleAll($event)"
                            />
                        </th>
                    @endif

                    @foreach ($columns as $field)
                        <th>
                            @if ($isSortable($field))
                                <a href="{{ $sortUrl($field) }}" class="inline-flex items-center gap-1 hover:underline">
                                    <span>{{ $label($field) }}</span>

                                    @if ($sortDirection($field) === 'asc')
                                        <span>↑</span>
                                    @elseif ($sortDirection($field) === 'desc')
                                        <span>↓</span>
                                    @else
                                        <span class="opacity-30">↕</span>
                                    @endif
                                </a>
                            @else
                                {{ $label($field) }}
                            @endif
                        </th>
                    @endforeach

                    @if ($actions)
                        <th class="w-28 text-center">Aksi</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @foreach ($data as $row)
                    @php
                        $rowId = (string) data_get($row, $rowKey);
                        $rowIdJs = json_encode($rowId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                    @endphp

                    <tr class="hover">
                        @if ($selectable)
                            <td>
                                <input
                                    type="checkbox"
                                    value="1"
                                    class="checkbox checkbox-primary checkbox-sm"
                                    x-bind:checked="selected.includes({{ $rowIdJs }})"
                                    x-bind:aria-label="'Pilih data ' + {{ $rowIdJs }}"
                                    x-on:mousedown.shift.prevent="$event.preventDefault()"
                                    x-on:click="toggleRow($event, {{ $rowIdJs }})"
                                />
                            </td>
                        @endif

                        @foreach ($columns as $field)
                            <td>
                                @isset(${'cell_' . $field})
                                    {{ ${'cell_' . $field}($row) }}
                                @else
                                    {!! $formatValue($row, $field) !!}
                                @endisset
                            </td>
                        @endforeach

                        @if ($actions)
                            <td>
                                <div class="flex items-center justify-center gap-1">
                                    @isset($rowActions)
                                        {{ $rowActions($row) }}
                                    @else
                                        @if ($showRoute)
                                            <a href="{{ $showRoute($row) }}" class="btn btn-ghost btn-xs" title="Fokus"
                                                aria-label="Fokus">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                                </svg>
                                            </a>
                                        @endif

                                        @if ($editRoute)
                                            <a href="{{ $editRoute($row) }}" class="btn btn-ghost btn-xs" title="Edit"
                                                aria-label="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.5-9.5a2.1 2.1 0 113 3L12 16l-4 1 1-4 8.5-8.5z" />
                                                </svg>
                                            </a>
                                        @endif

                                        @if ($deleteRoute)
                                            <x-ui.button type="ghost" size="xs" :isSubmit="false" class="text-error"
                                                title="Hapus" aria-label="Hapus"
                                                @click="$dispatch('confirm-delete', { action: '{{ $deleteRoute($row) }}', message: 'Apakah Anda yakin ingin menghapus data ini?' })">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.9 12A2 2 0 0116.1 21H7.9a2 2 0 01-2-1.9L5 7m5 4v6m4-6v6M4 7h16m-3 0V5a2 2 0 00-2-2h-6a2 2 0 00-2 2v2" />
                                                </svg>
                                            </x-ui.button>
                                        @endif
                                @endisset
                            </div>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
