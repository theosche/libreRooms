@if ($discounts->isNotEmpty())
    @php
        $contactType = old('contact_type') ??  $tenantType->value ?? App\Enums\ContactTypes::ORGANIZATION->value;
        $nbShownDiscounts = $discounts->filter(function ($discount) use ($contactType) {
            return $discount->limit_to_contact_type?->value === $contactType || $discount->limit_to_contact_type === null;
        })->count();

    @endphp
    <div class="form-group {{ $nbShownDiscounts == 0 ? 'hidden' : '' }}" id="discounts-form-group">
        <h3 class="form-group-title">Réductions</h3>
        <fieldset class="form-element">
            <legend class="form-element-title">Cocher ce qui s'applique</legend>
            @foreach($discounts as $discount)
                @php
                $visible = is_null($discount->limit_to_contact_type) || $discount->limit_to_contact_type?->value == $contactType;
                @endphp
                <div class="form-field {{ $visible ? '' : 'hidden' }}">
                    <input
                        type="checkbox"
                        id="discount_{{ $discount->id }}"
                        name="discounts[]"
                        value="{{ $discount->id }}"
                        @checked(in_array($discount->id, $enabledDiscounts))
                    >
                    <label for="discount_{{ $discount->id }}">
                        {{ $discount->description }} ({{ $discount->type == App\Enums\DiscountTypes::FIXED ?
                            currency($discount->value,$owner) :
                            $discount->value . '%' }})
                    </label>
                </div>
            @endforeach
        </fieldset>
    </div>
@endif
