@extends('admin.layouts.app', ['title' => 'Profil Cafe', 'breadcrumbs' => [['label' => 'Profil Cafe']]])

@section('header')
    <x-layouts.page-header title="Profil Cafe" description="Kelola informasi AMIKOSPACE yang menjadi sumber aturan reservasi dan data menu.">
        <x-slot:actions>
            <x-ui.button :href="route('admin.menu.index')" type="ghost" size="sm" :isSubmit="false">Kelola Menu</x-ui.button>
        </x-slot:actions>
    </x-layouts.page-header>
@endsection

@section('content')
    <div class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
        <x-ui.card title="Form Profil Cafe">
            <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <x-ui.input name="name" label="Nama Cafe" :value="$profile->name" required />
                <x-ui.textarea name="description" label="Deskripsi" :value="$profile->description" rows="4" />
                <x-ui.textarea name="address" label="Alamat" :value="$profile->address" rows="3" required />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="phone_number" label="Nomor Telepon" :value="$profile->phone_number" />
                    <x-ui.input name="down_payment_amount" type="number" label="Nominal DP" :value="(int) $profile->down_payment_amount" required />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="opening_time" type="time" label="Jam Buka" :value="substr($profile->opening_time, 0, 5)" required />
                    <x-ui.input name="closing_time" type="time" label="Jam Tutup" :value="substr($profile->closing_time, 0, 5)" required />
                </div>
                <x-ui.textarea name="facilities" label="Fasilitas" :value="collect($profile->facilities ?? [])->implode(PHP_EOL)" rows="5" placeholder="Satu fasilitas per baris" />
                <x-ui.textarea name="reservation_rules" label="Aturan Reservasi" :value="$profile->reservation_rules" rows="4" />
                <div class="flex justify-end">
                    <x-ui.button type="primary">Simpan Profil</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <div class="space-y-6">
            <x-ui.card title="Preview Cafe">
                <div class="space-y-4">
                    <div>
                        <p class="text-sm uppercase tracking-wide text-base-content/50">Nama</p>
                        <p class="text-2xl font-bold">{{ $profile->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm uppercase tracking-wide text-base-content/50">Jam Operasional</p>
                        <p class="font-semibold">{{ substr($profile->opening_time, 0, 5) }} - {{ substr($profile->closing_time, 0, 5) }}</p>
                    </div>
                    <div>
                        <p class="text-sm uppercase tracking-wide text-base-content/50">Fasilitas</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($profile->facilities ?? [] as $facility)
                                <x-ui.badge type="primary" outline>{{ $facility }}</x-ui.badge>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.alert type="warning">
                <div>
                    <p class="font-semibold">Nominal DP memengaruhi reservasi baru.</p>
                    <p class="text-sm opacity-80">Jika DP diubah, data pembayaran baru akan mengikuti nilai terbaru dari profil cafe.</p>
                </div>
            </x-ui.alert>
        </div>
    </div>
@endsection
