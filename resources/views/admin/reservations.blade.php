<x-layouts.app title="Panel Reservasi" :breadcrumbs="[['label' => 'Reservasi']]">
    <x-slot:header>
        <x-layouts.page-header title="Panel Reservasi" description="Kelola booking pelanggan, status konfirmasi, check-in, pembatalan, dan histori reservasi.">
            <x-slot:actions>
                <x-ui.button :href="route('admin.payments.index')" type="warning" size="sm" :isSubmit="false">Cek Pembayaran</x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    <div class="space-y-6">
        <x-ui.card title="Antrian Tindakan Reservasi">
            <div class="grid gap-4 lg:grid-cols-2">
                @forelse ($reservations->getCollection()->take(4) as $reservation)
                    <form method="POST" action="{{ route('admin.reservations.status', $reservation) }}" class="rounded-box border border-base-200 bg-base-100 p-4">
                        @csrf
                        @method('PATCH')
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold">{{ $reservation->customer_name }}</p>
                                <p class="text-sm text-base-content/60">{{ $reservation->reservation_code }} · {{ $reservation->reservation_date?->format('d M Y') }} {{ substr($reservation->start_time, 0, 5) }}</p>
                            </div>
                            <x-ui.badge type="info" size="sm">{{ $reservation->status->label() }}</x-ui.badge>
                        </div>
                        <div class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto]">
                            <x-ui.select name="status" :options="$statusOptions" :selected="$reservation->status->value" size="sm" placeholder="Pilih status" />
                            <x-ui.button type="primary" size="sm">Update</x-ui.button>
                        </div>
                        <x-ui.input name="cancellation_reason" size="sm" placeholder="Alasan pembatalan bila status dibatalkan" class="mt-3" />
                    </form>
                @empty
                    <div class="rounded-box border border-dashed border-base-300 p-6 text-center text-base-content/60 lg:col-span-2">
                        Belum ada data reservasi pada filter ini.
                    </div>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card>
            <x-ui.data-table
                title="Daftar Reservasi"
                :data="$reservations"
                :only="['reservation_code', 'customer_name', 'reservation_date', 'start_time', 'guest_count', 'status', 'amount_due']"
                :labels="[
                    'reservation_code' => 'Kode',
                    'customer_name' => 'Pelanggan',
                    'reservation_date' => 'Tanggal',
                    'start_time' => 'Jam',
                    'guest_count' => 'Tamu',
                    'amount_due' => 'DP',
                ]"
                :formats="[
                    'reservation_date' => 'date',
                    'start_time' => 'time',
                    'status' => 'badge',
                    'amount_due' => 'money',
                ]"
                :sortable="['reservation_date', 'start_time', 'guest_count', 'status', 'amount_due']"
                :selectable="false"
                :delete-route="fn ($row) => route('admin.reservations.destroy', $row)"
            >
                <x-slot:filters>
                    <x-ui.select name="status" :options="$statusOptions" :selected="request('status')" placeholder="Semua status" size="sm" class="min-w-48" />
                    <x-ui.input name="date" type="date" :value="request('date')" size="sm" />
                </x-slot:filters>
            </x-ui.data-table>
        </x-ui.card>
    </div>
</x-layouts.app>
