 <div class="form-group" id="special_discount-form-group">
    <h3 class="form-group-title">Accorder une réduction spéciale (admin)</h3>
    <fieldset class="form-element">
        <div class="form-field">
            <label for="special_discount" class="form-element-title">Réduction en {{$currency}}</label>
            <input
                type="number"
                min="0"
                step=".01"
                id="special_discount"
                name="special_discount"
                value="{{ old('special_discount') ?? $specialDiscount }}"
            >
            @error('special_discount')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </fieldset>
 </div>
