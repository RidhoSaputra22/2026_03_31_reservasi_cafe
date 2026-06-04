<?php

return [
    'pending_payment_timeout_minutes' => (int) env('RESERVATION_PENDING_PAYMENT_TIMEOUT_MINUTES', 60),
    'expired_payment_cancellation_reason' => env(
        'RESERVATION_EXPIRED_PAYMENT_CANCELLATION_REASON',
        'Reservasi dibatalkan otomatis karena batas waktu pembayaran habis.',
    ),
];
