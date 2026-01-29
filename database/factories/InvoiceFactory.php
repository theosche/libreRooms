<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Owner;
use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement([InvoiceStatus::DUE,InvoiceStatus::LATE,InvoiceStatus::TOO_LATE,InvoiceStatus::PAID,InvoiceStatus::CANCELLED]);
        $issuedAt = fake()->dateTimeBetween('-60 days', 'now');
        $dueAt = $issuedAt ? (clone $issuedAt)->modify('+30 days') : null;
        $paidAt = ($status === 'paid' && $issuedAt) ? fake()->dateTimeBetween($issuedAt, 'now') : null;
        $reminderCount = $status === 'sent' ? fake()->numberBetween(0, 3) : 0;
        $lastReminderAt = ($reminderCount > 0 && $issuedAt) ? fake()->dateTimeBetween($issuedAt, 'now') : null;

        return [
            'reservation_id' => Reservation::factory(),
            'owner_id' => Owner::factory(),
            'number' => 'INV-' . fake()->year() . '-' . fake()->unique()->numberBetween(1000, 9999),
            'amount' => fake()->randomFloat(2, 50, 1000),
            'first_issued_at' => $issuedAt,
            'issued_at' => $issuedAt,
            'first_due_at' => $dueAt,
            'paid_at' => $paidAt,
            'reminder_count' => $reminderCount,
            'due_at' => $dueAt,
        ];
    }

    /**
     * Indicate that the invoice is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the invoice is sent but not paid.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'issued_at' => fake()->dateTimeBetween('-60 days', 'now'),
            'paid_at' => null,
        ]);
    }

}
