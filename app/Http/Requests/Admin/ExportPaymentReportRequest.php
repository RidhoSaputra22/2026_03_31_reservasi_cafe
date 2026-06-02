<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportPaymentReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() || $this->user()?->isStaff();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'date_field' => ['nullable', Rule::in(['created_at', 'paid_at', 'verified_at'])],
            'date_from' => ['nullable', 'date'],
            'date_until' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', Rule::in(array_map(
                static fn (PaymentStatus $status): string => $status->value,
                PaymentStatus::cases(),
            ))],
            'method' => ['nullable', Rule::in(array_map(
                static fn (PaymentMethod $method): string => $method->value,
                PaymentMethod::cases(),
            ))],
            'type' => ['nullable', Rule::in(array_map(
                static fn (PaymentType $type): string => $type->value,
                PaymentType::cases(),
            ))],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'search' => 'kata kunci',
            'date_field' => 'basis tanggal',
            'date_from' => 'tanggal mulai',
            'date_until' => 'tanggal selesai',
            'status' => 'status pembayaran',
            'method' => 'metode pembayaran',
            'type' => 'jenis pembayaran',
            'min_amount' => 'nominal minimum',
        ];
    }
}
