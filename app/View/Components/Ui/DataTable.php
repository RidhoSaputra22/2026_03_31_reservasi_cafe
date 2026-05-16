<?php

namespace App\View\Components\Ui;

use BackedEnum;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class DataTable extends Component
{
    public array $columns = [];

    public function __construct(
        public string $title = 'Data Table',
        public mixed $data = [],
        public ?string $model = null,

        // auto column behavior
        public array $exclude = ['id', 'created_at', 'updated_at', 'deleted_at'],
        public ?array $only = null,

        // label / format / sort
        public array $labels = [],
        public array $formats = [],
        public array $sortable = [],

        // features
        public string $rowKey = 'id',
        public bool $selectable = true,
        public bool $actions = true,
        public bool $mobileCard = true,

        // routes / actions
        public mixed $showRoute = null,
        public mixed $editRoute = null,
        public mixed $deleteRoute = null,
        public ?string $bulkActionRoute = null,

        // ui
        public ?string $search = null,
        public string $emptyText = 'Data tidak tersedia.',
    ) {
        $this->columns = $this->resolveColumns();
    }

    protected function resolveColumns(): array
    {
        $columns = [];

        if ($this->model && class_exists($this->model)) {
            $instance = app($this->model);

            if ($instance instanceof Model) {
                $columns = $instance->getFillable();
            }
        }

        if (empty($columns)) {
            $items = $this->items();
            $first = $items->first();

            if ($first) {
                if (is_array($first)) {
                    $columns = array_keys($first);
                } elseif (is_object($first) && method_exists($first, 'getAttributes')) {
                    $columns = array_keys($first->getAttributes());
                } else {
                    $columns = array_keys((array) $first);
                }
            }
        }

        if ($this->only) {
            return array_values(array_filter($columns, fn($col) => in_array($col, $this->only, true)));
        }

        return array_values(array_filter($columns, fn($col) => !in_array($col, $this->exclude, true)));
    }

    public function items(): Collection
    {
        if ($this->data instanceof AbstractPaginator) {
            return $this->data->getCollection();
        }

        return collect($this->data);
    }

    public function isPaginated(): bool
    {
        return $this->data instanceof AbstractPaginator;
    }

    public function label(string $field): string
    {
        return $this->labels[$field] ?? Str::title(str_replace('_', ' ', $field));
    }

    public function isSortable(string $field): bool
    {
        return in_array($field, $this->sortable, true);
    }

    public function sortDirection(string $field): ?string
    {
        $sort = request('sort');
        $direction = request('direction', 'asc');

        if ($sort !== $field) {
            return null;
        }

        return $direction === 'desc' ? 'desc' : 'asc';
    }

    public function sortUrl(string $field): string
    {
        $currentSort = request('sort');
        $currentDirection = request('direction', 'asc');

        $direction = ($currentSort === $field && $currentDirection === 'asc') ? 'desc' : 'asc';

        return request()->fullUrlWithQuery([
            'sort' => $field,
            'direction' => $direction,
        ]);
    }

    public function formatValue(mixed $row, string $field): string
    {
        $value = data_get($row, $field);
        $format = $this->formats[$field] ?? null;

        if ($value instanceof \UnitEnum) {
            $value = method_exists($value, 'label')
                ? $value->label()
                : ($value instanceof BackedEnum ? $value->value : $value->name);
        }

        if (is_null($value) || $value === '') {
            return '-';
        }

        return match ($format) {
            'badge' => '<span class="badge badge-info badge-sm">' . e($value) . '</span>',
            'success-badge' => '<span class="badge badge-success badge-sm">' . e($value) . '</span>',
            'warning-badge' => '<span class="badge badge-warning badge-sm">' . e($value) . '</span>',
            'error-badge' => '<span class="badge badge-error badge-sm">' . e($value) . '</span>',
            'boolean' => $value
            ? '<span class="badge badge-success badge-sm">Ya</span>'
            : '<span class="badge badge-error badge-sm">Tidak</span>',
            'date' => e(\Carbon\Carbon::parse($value)->format('d-m-Y')),
            'datetime' => e(\Carbon\Carbon::parse($value)->format('d-m-Y H:i')),
            'time' => e(\Carbon\Carbon::parse($value)->format('H:i')),
            'money' => e('Rp ' . number_format((float) $value, 0, ',', '.')),
            'number' => e(number_format((float) $value, 0, ',', '.')),
            default => e((string) $value),
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.ui.data-table', [
            'data' => $this->data,
            'columns' => $this->columns,
        ]);
    }
}
