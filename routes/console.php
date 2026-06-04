<?php

use App\Services\CafeReservation\CafeReservationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('reservations:expire-pending-payments', function (): void {
    $expiredReservations = app(CafeReservationService::class)->expireTimedOutPendingReservations();

    $this->info("Reservasi kadaluarsa yang dibatalkan: {$expiredReservations}");
})->purpose('Batalkan otomatis reservasi dengan pembayaran pending yang melewati batas waktu');

Schedule::command('reservations:expire-pending-payments')
    ->everyMinute()
    ->withoutOverlapping();
