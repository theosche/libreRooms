@php
    $totalPrice = $reservation->events->sum('price');
    $useFreePrice = $room->price_mode == App\Enums\PriceModes::FREE;
    $hasDiscounts = $reservation->discounts->isNotEmpty() || $reservation->special_discount || ($reservation->donation && !$useFreePrice);
    $finalTotal = $reservation->finalPrice();
@endphp

<div class="totals-section">
    @if($hasDiscounts)
    <table class="totals-table">

            <tr>
                <td class="label-col"><strong>Total initial</strong></td>
                <td class="price-col"><strong>{{ currency($totalPrice, $owner) }}</strong></td>
            </tr>
    </table>
    <div style="height: 5mm;"></div>
    <table class="totals-table">
            @foreach($reservation->discounts as $discount)
                @php
                    $discountValue = $discount->type->value === 'fixed'
                        ? $discount->value
                        : ($discount->value * $totalPrice / 100);
                    $discountLabel = $discount->name;
                    if ($discount->type->value === 'percent') {
                        $discountLabel .= " (-" . $discount->value . "%)";
                    }
                @endphp
                <tr>
                    <td class="label-col">{{ $discountLabel }}</td>
                    <td class="price-col">{{ currency(-$discountValue, $owner) }}</td>
                </tr>
            @endforeach

            @if($reservation->special_discount)
                <tr>
                    <td class="label-col">Réduction spéciale</td>
                    <td class="price-col">{{ currency(-$reservation->special_discount, $owner) }}</td>
                </tr>
            @endif

            @if($reservation->donation && !$useFreePrice)
                <tr>
                    <td class="label-col">Don supplémentaire</td>
                    <td class="price-col">{{ currency($reservation->donation, $owner) }}</td>
                </tr>
            @endif
        </table>
        <div style="height: 5mm;"></div>
        @endif

        @if($useFreePrice)
        <table class="totals-table">
            <tr>
                <td class="label-col">Total tarif recommandé</td>
                <td class="price-col">{{ currency($reservation->recommendedPrice(), $owner) }}</td>
            </tr>
            <tr class="total-row">
                <td class="label-col">Tarif libre</td>
                <td class="price-col">{{ currency($finalTotal, $owner) }}</td>
            </tr>
        @else
        <table class="totals-table">
            <tr class="total-row">
                <td class="label-col">Total (TTC)</td>
                <td class="price-col">{{ currency($finalTotal, $owner) }}</td>
            </tr>
        </table>
        @endif
</div>
