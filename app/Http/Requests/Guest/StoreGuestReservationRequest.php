<?php

namespace App\Http\Requests\Guest;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGuestReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCustomer() ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:12'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in(array_map(
                static fn (PaymentMethod $method): string => $method->value,
                PaymentMethod::cases(),
            ))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_name' => 'nama pelanggan',
            'customer_phone' => 'nomor telepon',
            'reservation_date' => 'tanggal reservasi',
            'start_time' => 'jam reservasi',
            'guest_count' => 'jumlah tamu',
            'payment_method' => 'metode pembayaran',
        ];
    }
}
