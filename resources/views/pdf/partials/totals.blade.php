@php
    $totalPrice = $reservation->events->sum('price');
    $useFreePrice = $room->price_mode == App\Enums\PriceModes::FREE;
    $hasDiscounts = !empty($reservation->discounts) || $reservation->special_discount || ($reservation->donation && !$useFreePrice);
    $finalTotal = $reservation->finalPrice();
@endphp

<div class="totals-section">
    @if($hasDiscounts)
    <table class="totals-table">

            <tr>
                <td class="label-col"><strong>{{ __('Initial total') }}</strong></td>
                <td class="price-col"><strong>{{ currency($totalPrice, $owner) }}</strong></td>
            </tr>
    </table>
    <div style="height: 5mm;"></div>
    <table class="totals-table">
            @foreach($reservation->discounts ?? [] as $discount)
                <tr>
                    <td class="label-col">{{ $discount[1] }}</td>
                    <td class="price-col">{{ currency(-$discount[2], $owner) }}</td>
                </tr>
            @endforeach

            @if($reservation->special_discount)
                <tr>
                    <td class="label-col">{{ __('Special discount') }}</td>
                    <td class="price-col">{{ currency(-$reservation->special_discount, $owner) }}</td>
                </tr>
            @endif

            @if($reservation->donation && !$useFreePrice)
                <tr>
                    <td class="label-col">{{ __('Additional donation') }}</td>
                    <td class="price-col">{{ currency($reservation->donation, $owner) }}</td>
                </tr>
            @endif
        </table>
        <div style="height: 5mm;"></div>
        @endif

        @if($useFreePrice)
        <table class="totals-table">
            <tr>
                <td class="label-col">{{ __('Total recommended rate') }}</td>
                <td class="price-col">{{ currency($reservation->recommendedPrice(), $owner) }}</td>
            </tr>
            <tr class="total-row">
                <td class="label-col">{{ __('Free rate') }}</td>
                <td class="price-col">{{ currency($finalTotal, $owner) }}</td>
            </tr>
        @else
        <table class="totals-table">
            <tr class="total-row">
                <td class="label-col">{{ __('Total (incl. VAT)') }}</td>
                <td class="price-col">{{ currency($finalTotal, $owner) }}</td>
            </tr>
        </table>
        @endif
</div>
