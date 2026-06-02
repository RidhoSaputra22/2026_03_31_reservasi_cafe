<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CafePackageCatalog
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function all(): Collection
    {
        return collect(config('packages'))
            ->map(function (array $package, string $slug): array {
                $canonicalSlug = $package['slug'] ?? $slug;

                return [
                    ...$package,
                    'slug' => $canonicalSlug,
                    'aliases' => $package['aliases'] ?? [],
                    'price_amount' => (int) ($package['price_amount'] ?? $this->extractPriceAmount((string) ($package['price'] ?? '0'))),
                ];
            })
            ->values();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $slug): ?array
    {
        return $this->all()->first(
            static fn (array $package): bool => $package['slug'] === $slug
                || in_array($slug, $package['aliases'], true)
        );
    }

    /**
     * @param  array<int, string>  $slugs
     * @return Collection<int, array<string, mixed>>
     */
    public function featured(array $slugs): Collection
    {
        return collect($slugs)
            ->map(fn (string $slug): ?array => $this->find($slug))
            ->filter()
            ->values();
    }

    /**
     * @param  array<string, string|null>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function filter(array $filters = []): Collection
    {
        $packages = $this->all();
        $search = Str::lower(trim((string) ($filters['q'] ?? '')));
        $category = trim((string) ($filters['category'] ?? ''));
        $duration = trim((string) ($filters['duration'] ?? ''));
        $price = trim((string) ($filters['price'] ?? ''));
        $sort = trim((string) ($filters['sort'] ?? 'latest'));

        if ($search !== '') {
            $packages = $packages->filter(function (array $package) use ($search): bool {
                $haystack = Str::lower(implode(' ', array_filter([
                    $package['name'] ?? null,
                    $package['category'] ?? null,
                    $package['summary'] ?? null,
                    $package['description'] ?? null,
                ])));

                return Str::contains($haystack, $search);
            });
        }

        if ($category !== '' && $category !== 'Semua Kategori') {
            $packages = $packages->where('category', $category);
        }

        if ($duration !== '' && $duration !== 'Semua Durasi') {
            $packages = $packages->where('duration', $duration);
        }

        if ($price === 'asc') {
            $packages = $packages->sortBy('price_amount');
        } elseif ($price === 'desc') {
            $packages = $packages->sortByDesc('price_amount');
        } else {
            $packages = match ($sort) {
                'oldest' => $packages->reverse(),
                'name_asc' => $packages->sortBy('name'),
                'name_desc' => $packages->sortByDesc('name'),
                default => $packages,
            };
        }

        return $packages->values();
    }

    /**
     * @return Collection<int, string>
     */
    public function categories(): Collection
    {
        return $this->all()
            ->pluck('category')
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    public function durations(): Collection
    {
        return $this->all()
            ->pluck('duration')
            ->filter()
            ->unique()
            ->values();
    }

    protected function extractPriceAmount(string $formattedPrice): int
    {
        $digits = preg_replace('/\D+/', '', $formattedPrice);

        return (int) ($digits ?: 0);
    }
}
