<?php

namespace App\Services;

use App\Models\ReservationPackage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CafePackageCatalog
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function all(): Collection
    {
        if ($this->usesDatabasePackages()) {
            return ReservationPackage::query()
                ->where('is_active', true)
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(fn (ReservationPackage $package): array => $this->mapDatabasePackage($package))
                ->values();
        }

        return collect(config('packages'))
            ->map(function (array $package, string $slug): array {
                $canonicalSlug = $package['slug'] ?? $slug;
                $priceAmount = (int) ($package['price_amount'] ?? $this->extractPriceAmount((string) ($package['price'] ?? '0')));
                $includedHours = max(1, (int) preg_replace('/\D+/', '', (string) ($package['duration'] ?? '1')));

                return [
                    ...$package,
                    'id' => null,
                    'slug' => $canonicalSlug,
                    'aliases' => $package['aliases'] ?? [],
                    'image' => $package['image'] ?? 'assets/images/hero.jpg',
                    'base_price_amount' => $priceAmount,
                    'included_hours' => $includedHours,
                    'extra_hour_price_amount' => 0,
                    'price_amount' => $priceAmount,
                    'price' => $this->formatStartingPrice($priceAmount, 0),
                    'pricing_summary' => $this->buildPricingSummary($priceAmount, $includedHours, 0),
                    'is_featured' => in_array($canonicalSlug, [
                        'coffee-date-corner',
                        'work-brew-table',
                        'live-music-hangout',
                    ], true),
                    'is_active' => true,
                    'sort_order' => 0,
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
        $packages = $this->all();

        if ($slugs === []) {
            return $packages
                ->filter(fn (array $package): bool => (bool) ($package['is_featured'] ?? false))
                ->values();
        }

        return collect($slugs)
            ->map(fn (string $slug): ?array => $packages->first(fn (array $package): bool => $package['slug'] === $slug))
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

        if ($price === 'asc') {
            $packages = $packages->sortBy('base_price_amount');
        } elseif ($price === 'desc') {
            $packages = $packages->sortByDesc('base_price_amount');
        } else {
            $packages = match ($sort) {
                'oldest' => $packages->sortByDesc('sort_order'),
                'name_asc' => $packages->sortBy('name'),
                'name_desc' => $packages->sortByDesc('name'),
                default => $packages->sortBy('sort_order'),
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
     * @param  array<string, mixed>  $package
     */
    public function calculatePrice(array $package, int $durationHours): int
    {
        $basePriceAmount = max(0, (int) ($package['base_price_amount'] ?? $package['price_amount'] ?? 0));
        $includedHours = max(0, (int) ($package['included_hours'] ?? 0));
        $extraHourPriceAmount = max(0, (int) ($package['extra_hour_price_amount'] ?? 0));
        $resolvedDurationHours = max(1, $durationHours);
        $extraBillableHours = max(0, $resolvedDurationHours - $includedHours);

        return $basePriceAmount + ($extraBillableHours * $extraHourPriceAmount);
    }

    public function formatMoney(int|float $amount): string
    {
        return 'Rp. '.number_format((float) $amount, 0, ',', '.');
    }

    protected function usesDatabasePackages(): bool
    {
        return Schema::hasTable('reservation_packages');
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapDatabasePackage(ReservationPackage $package): array
    {
        $basePriceAmount = (int) round((float) $package->base_price);
        $extraHourPriceAmount = (int) round((float) $package->extra_hour_price);
        $includedHours = max(1, (int) $package->included_hours);

        return [
            'id' => $package->id,
            'slug' => $package->slug,
            'aliases' => $package->aliases ?? [],
            'name' => $package->name,
            'category' => $package->category,
            'image' => $package->image_path ?: 'assets/images/hero.jpg',
            'summary' => $package->summary,
            'description' => $package->description,
            'facilities' => $package->facilities ?? [],
            'notes' => $package->notes ?? [],
            'base_price_amount' => $basePriceAmount,
            'included_hours' => $includedHours,
            'extra_hour_price_amount' => $extraHourPriceAmount,
            'price_amount' => $basePriceAmount,
            'price' => $this->formatStartingPrice($basePriceAmount, $extraHourPriceAmount),
            'pricing_summary' => $this->buildPricingSummary($basePriceAmount, $includedHours, $extraHourPriceAmount),
            'is_featured' => $package->is_featured,
            'is_active' => $package->is_active,
            'sort_order' => $package->sort_order,
        ];
    }

    protected function formatStartingPrice(int $basePriceAmount, int $extraHourPriceAmount): string
    {
        $prefix = $extraHourPriceAmount > 0 ? 'Mulai ' : '';

        return $prefix.$this->formatMoney($basePriceAmount);
    }

    protected function buildPricingSummary(int $basePriceAmount, int $includedHours, int $extraHourPriceAmount): string
    {
        if ($extraHourPriceAmount <= 0) {
            return 'Harga dasar '.$this->formatMoney($basePriceAmount).' untuk reservasi paket ini.';
        }

        return 'Harga dasar '.$this->formatMoney($basePriceAmount)
            .' termasuk '.$this->durationLabel($includedHours)
            .', lalu tambah '.$this->formatMoney($extraHourPriceAmount).' per jam.';
    }

    protected function durationLabel(int $durationHours): string
    {
        return $durationHours === 1
            ? '1 jam'
            : $durationHours.' jam';
    }

    protected function extractPriceAmount(string $formattedPrice): int
    {
        $digits = preg_replace('/\D+/', '', $formattedPrice);

        return (int) ($digits ?: 0);
    }
}
