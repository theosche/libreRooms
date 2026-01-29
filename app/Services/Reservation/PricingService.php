<?php

namespace App\Services\Reservation;

use App\Enums\DiscountTypes;
use App\Models\Room;
use App\Models\RoomDiscount;
use App\Services\Settings\SettingsService;
use Carbon\Carbon;

class PricingService
{
    /**
     * Split event by day considering late end continuation
     * Returns array of segments with start/end hours and date
     */
    protected function splitByDay(Carbon $start, Carbon $end, Room $room): array
    {
        $timezone = app(SettingsService::class)->timezone($room);
        $segments = [];
        $allowLateEnd = $room->allow_late_end_hour;

        // Convert to room timezone for day splitting
        $startInTimezone = $start->copy()->setTimezone($timezone);
        $endInTimezone = $end->copy()->setTimezone($timezone);

        $startDay = $startInTimezone->copy()->startOfDay();
        $endDay = $endInTimezone->copy()->startOfDay();

        // Check if crosses midnight
        $crossesMidnight = !$startDay->eq($endDay);

        // Extract hours with decimals
        $startHour = $startInTimezone->hour + $startInTimezone->minute / 60;
        $endHour = $endInTimezone->hour + $endInTimezone->minute / 60;

        // Check for late continuation
        $hasLateContinuation = $crossesMidnight && $endHour <= $allowLateEnd;

        // Iterate over each day
        $current = $startDay->copy();

        while ($current->lte($endDay)) {
            $isFirst = $current->eq($startDay);
            $isLast = $current->eq($endDay);

            $segments[] = [
                'start' => $isFirst ? $startHour : 0,
                'end' => $isLast ? $endHour : 24,
                'date' => $current->toDateString(),
            ];

            $current->addDay();
        }

        // Apply late continuation: remove last segment and extend previous one
        if ($hasLateContinuation) {
            array_pop($segments);
            $segments[count($segments) - 1]['end'] = 24 + $endHour;
        }

        return $segments;
    }

    /**
     * Calculate price and label for an event (without options)
     * Returns ['label' => string, 'price' => float]
     */
    public function calculateEventPrice(Carbon $start, Carbon $end, Room $room): array
    {
        $shortAfter = $room->always_short_after;
        $shortBefore = $room->always_short_before;
        $maxHoursShort = $room->max_hours_short;
        $priceShort = $room->price_short;
        $priceFullDay = $room->price_full_day;

        $segments = $this->splitByDay($start, $end, $room);

        $nbShort = 0;
        $nbFull = 0;

        foreach ($segments as $segment) {
            $duration = $segment['end'] - $segment['start'];
            if (($shortBefore && ($segment['end'] <= $shortBefore) ||
                $shortAfter && ($segment['start'] >= $shortAfter) ||
                $maxHoursShort && ($duration <= $maxHoursShort))
                && $priceShort) {
                $nbShort++;
            } else {
                $nbFull++;
            }
        }

        // Build label
        $label = '';
        if (count($segments) > 1) {
            if ($nbShort) {
                $label .= $nbShort . 'x réservation courte, ';
            }
            if ($nbFull) {
                $label .= $nbFull . 'x réservation journée, ';
            }
            $label = substr($label, 0, -2);
        } else {
            if ($nbShort) {
                $label .= 'Réservation courte';
            } else if ($nbFull) {
                $label .= 'Réservation journée';
            }
        }

        $price = $nbShort * $priceShort + $nbFull * $priceFullDay;

        return [
            'label' => $label,
            'price' => $price,
        ];
    }

    /**
     * Calculate price and label for options
     * Returns ['label' => string, 'price' => float]
     */
    public function calculateOptionsPrice(array $optionIds, Room $room): array
    {
        if (empty($optionIds)) {
            return ['label' => '', 'price' => 0];
        }

        $label = count($optionIds) === 1 ? 'option: ' : 'options: ';
        $price = 0;

        foreach ($optionIds as $optionId) {
            $option = $room->options->firstWhere('id', $optionId);
            if ($option) {
                $label .= $option->name . ', ';
                $price += $option->price;
            }
        }

        $label = substr($label, 0, -2);

        return [
            'label' => $label,
            'price' => $price,
        ];
    }

    public function calculateSumDiscounts(Room $room, array $discountIds, $fullPrice): float
    {
        $sumDiscounts = 0;
        if (is_array($discountIds) && !empty($discountIds)) {
            foreach ($room->discounts as $discount) {
                if (in_array($discount->id, $discountIds)) {
                    $sumDiscounts += $this->calculateDiscountValue($discount, $fullPrice);
                }
            }
        }
        return $sumDiscounts;
    }

    /**
     * Calculate discount value based on type
     */
    protected function calculateDiscountValue(RoomDiscount $discount, float $initPrice): float
    {
        return match ($discount->type) {
            DiscountTypes::FIXED => $discount->value,
            DiscountTypes::PERCENT => $discount->value * $initPrice / 100,
            default => 0,
        };
    }

}
