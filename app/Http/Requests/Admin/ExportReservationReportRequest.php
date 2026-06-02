<?php

namespace App\Http\Requests\Admin;

use App\Enums\ReservationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportReservationReportRequest extends FormRequest
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
            'date_from' => ['nullable', 'date'],
            'date_until' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', Rule::in(array_map(
                static fn (ReservationStatus $status): string => $status->value,
                ReservationStatus::cases(),
            ))],
            'cafe_table_id' => ['nullable', 'integer', 'exists:cafe_tables,id'],
            'min_guest_count' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'search' => 'kata kunci',
            'date_from' => 'tanggal mulai',
            'date_until' => 'tanggal selesai',
            'status' => 'status reservasi',
            'cafe_table_id' => 'meja atau area',
            'min_guest_count' => 'minimum jumlah tamu',
        ];
    }
}
