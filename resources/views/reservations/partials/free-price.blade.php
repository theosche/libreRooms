 <div class="form-group" id="free-price-form-group">
    <h3 class="form-group-title">Prix libre</h3>
    <fieldset class="form-element">
        <div class="form-field">
            <label for="free-price" class="form-element-title">Fixez vous-même le prix de la réservation en {{ $currency }} *</label>
            <input
                type="number"
                min="0"
                step=".01"
                id="free-price"
                name="donation"
                required
                value="{{ old('donation') ?? $freePrice }}"
            >
        </div>

    </fieldset>
 </div>
