<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_code' => strtoupper(fake()->unique()->bothify('PAY-####??')),
            'reservation_id' => Reservation::factory(),
            'type' => PaymentType::DownPayment->value,
            'amount' => fake()->randomFloat(2, 25000, 150000),
            'method' => fake()->randomElement([
                PaymentMethod::Cash->value,
                PaymentMethod::BankTransfer->value,
                PaymentMethod::Qris->value,
                PaymentMethod::Card->value,
            ]),
            'status' => PaymentStatus::Pending->value,
            'transaction_reference' => strtoupper(fake()->bothify('TRX-######??')),
            'proof_path' => null,
            'paid_at' => null,
            'verified_at' => null,
            'verified_by' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Pending->value,
            'paid_at' => null,
            'verified_at' => null,
            'verified_by' => null,
        ]);
    }

    public function awaitingVerification(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::AwaitingVerification->value,
            'paid_at' => now(),
            'verified_at' => null,
            'verified_by' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Paid->value,
            'paid_at' => now()->subMinutes(10),
            'verified_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Failed->value,
            'paid_at' => null,
            'verified_at' => null,
            'verified_by' => null,
        ]);
    }
}
