<x-layouts.app title="Dashboard Admin" :breadcrumbs="[]">
    <x-slot:header>
        <x-layouts.page-header title="Dashboard Admin" description="Ringkasan operasional AMIKOSPACE Coffee & Tea dan pintu masuk ke semua panel pengelolaan.">
            <x-slot:actions>
                <x-ui.button :href="route('admin.reservations.index')" type="primary" size="sm" :isSubmit="false">
                    Kelola Reservasi
                </x-ui.button>
                <x-ui.button :href="route('landing')" type="ghost" size="sm" :isSubmit="false">
                    Lihat Website
                </x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    <div class="space-y-6">
        <x-ui.alert type="info" :dismissible="true">
            <div>
                <p class="font-semibold">Panel admin siap dipakai untuk mengelola reservasi cafe.</p>
                <p class="text-sm opacity-80">Gunakan pencarian global di navbar, aksi cepat di kanan bawah, atau kartu panel di bawah untuk berpindah modul.</p>
            </div>
        </x-ui.alert>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat title="Reservasi Hari Ini" :value="$stats['reservations_today']" description="Booking dengan tanggal hari ini" trend="up" trendValue="Live">
                <x-slot:icon>
                    <span class="btn btn-circle btn-primary btn-sm"><x-ui.fab.icon name="calendar" class="h-5 w-5" /></span>
                </x-slot:icon>
            </x-ui.stat>
            <x-ui.stat title="Menunggu Konfirmasi" :value="$stats['awaiting_confirmation']" description="Perlu dicek admin" trend="up" trendValue="Action">
                <x-slot:icon>
                    <span class="btn btn-circle btn-info btn-sm"><x-ui.fab.icon name="payment" class="h-5 w-5" /></span>
                </x-slot:icon>
            </x-ui.stat>
            <x-ui.stat title="Meja Aktif" :value="$stats['active_tables']" description="Meja siap dipakai">
                <x-slot:icon>
                    <span class="btn btn-circle btn-accent btn-sm"><x-ui.fab.icon name="table" class="h-5 w-5" /></span>
                </x-slot:icon>
            </x-ui.stat>
            <x-ui.stat title="Revenue Terverifikasi" value="Rp {{ number_format((float) $stats['revenue_paid'], 0, ',', '.') }}" description="Total pembayaran paid">
                <x-slot:icon>
                    <span class="btn btn-circle btn-success btn-sm"><x-ui.fab.icon name="payment" class="h-5 w-5" /></span>
                </x-slot:icon>
            </x-ui.stat>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($panels as $panel)
                <x-ui.card :href="$panel['href']" compact class="border border-base-200">
                    <div class="flex items-start gap-4">
                        <span class="btn btn-circle btn-{{ $panel['tone'] }} btn-sm shrink-0">
                            <x-ui.fab.icon :name="$panel['icon']" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-bold text-base-content">{{ $panel['title'] }}</h3>
                            <p class="mt-1 text-sm leading-6 text-base-content/65">{{ $panel['description'] }}</p>
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        <div x-data="{ tab: 'overview' }" class="space-y-4">
            <x-ui.tabs variant="boxed" layout="flex" navClass="overflow-x-auto">
                <button type="button" class="tab whitespace-nowrap" :class="tab === 'overview' ? 'tab-active' : ''" @click="tab = 'overview'">Overview</button>
                <button type="button" class="tab whitespace-nowrap" :class="tab === 'calendar' ? 'tab-active' : ''" @click="tab = 'calendar'">Kalender Reservasi</button>
                <button type="button" class="tab whitespace-nowrap" :class="tab === 'finance' ? 'tab-active' : ''" @click="tab = 'finance'">Pembayaran</button>
            </x-ui.tabs>

            <div x-show="tab === 'overview'" x-transition class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
                <x-ui.card title="Reservasi Hari Ini">
                    <div class="space-y-3">
                        @forelse ($todayReservations as $reservation)
                            @php
                                $statusType = match ($reservation->status) {
                                    App\Enums\ReservationStatus::Confirmed => 'primary',
                                    App\Enums\ReservationStatus::CheckedIn => 'success',
                                    App\Enums\ReservationStatus::AwaitingConfirmation => 'info',
                                    App\Enums\ReservationStatus::Cancelled => 'error',
                                    App\Enums\ReservationStatus::Completed => 'neutral',
                                    default => 'warning',
                                };
                            @endphp
                            <div class="rounded-box border border-base-200 bg-base-100 p-4">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-bold">{{ $reservation->customer_name }}</p>
                                            <x-ui.badge :type="$statusType" size="sm">{{ $reservation->status->label() }}</x-ui.badge>
                                        </div>
                                        <p class="text-sm text-base-content/60">{{ $reservation->reservation_code }} · {{ substr($reservation->start_time, 0, 5) }} · {{ $reservation->guest_count }} tamu · {{ $reservation->cafeTable?->name ?? 'Tanpa meja' }}</p>
                                    </div>
                                    <x-ui.button :href="route('admin.reservations.index', ['search' => $reservation->reservation_code])" type="ghost" size="sm" :isSubmit="false">Detail</x-ui.button>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-box border border-dashed border-base-300 p-6 text-center text-base-content/60">
                                Belum ada reservasi untuk hari ini.
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>

                <x-ui.card title="Status Meja">
                    <div class="space-y-4">
                        @foreach (App\Enums\TableStatus::cases() as $status)
                            @php
                                $count = (int) ($tableStatusCounts[$status->value] ?? 0);
                            @endphp
                            <div>
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <span>{{ $status->label() }}</span>
                                    <span class="font-bold">{{ $count }}</span>
                                </div>
                                <progress class="progress progress-primary w-full" value="{{ $count }}" max="{{ max($stats['active_tables'], 1) }}"></progress>
                            </div>
                        @endforeach
                    </div>
                    <x-slot:actions>
                        <x-ui.button :href="route('admin.tables.index')" type="primary" size="sm" :isSubmit="false">Kelola Meja</x-ui.button>
                    </x-slot:actions>
                </x-ui.card>
            </div>

            <div x-show="tab === 'calendar'" x-transition>
                <x-ui.callendar
                    :events="$calendarEvents"
                    mode="slim"
                    :allowEventCrud="false"
                    :interactive="false"
                    :showScheduleLegend="false"
                    :showDeadlineLegend="false"
                    customLegendLabel="Reservasi"
                />
            </div>

            <div x-show="tab === 'finance'" x-transition class="grid gap-6 xl:grid-cols-[.9fr_1.1fr]">
                <x-ui.card title="Ringkasan Finance">
                    <div class="stats stats-vertical w-full bg-base-200 lg:stats-horizontal">
                        <div class="stat">
                            <div class="stat-title">Menu tersedia</div>
                            <div class="stat-value text-success">{{ $stats['menu_available'] }}</div>
                            <div class="stat-desc">Aktif di katalog</div>
                        </div>
                        <div class="stat">
                            <div class="stat-title">Payment pending</div>
                            <div class="stat-value text-warning">{{ $stats['pending_payments'] }}</div>
                            <div class="stat-desc">Butuh follow-up</div>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card title="Pembayaran Terbaru">
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Reservasi</th>
                                    <th>Status</th>
                                    <th class="text-right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentPayments as $payment)
                                    @php
                                        $paymentType = match ($payment->status) {
                                            App\Enums\PaymentStatus::Paid => 'success',
                                            App\Enums\PaymentStatus::AwaitingVerification => 'info',
                                            App\Enums\PaymentStatus::Failed => 'error',
                                            App\Enums\PaymentStatus::Refunded => 'neutral',
                                            default => 'warning',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="font-semibold">{{ $payment->payment_code }}</td>
                                        <td>{{ $payment->reservation?->reservation_code ?? '-' }}</td>
                                        <td><x-ui.badge :type="$paymentType" size="sm">{{ $payment->status->label() }}</x-ui.badge></td>
                                        <td class="text-right">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-base-content/60">Belum ada pembayaran.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <x-slot:actions>
                        <x-ui.button :href="route('admin.payments.index')" type="primary" size="sm" :isSubmit="false">Kelola Pembayaran</x-ui.button>
                    </x-slot:actions>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
