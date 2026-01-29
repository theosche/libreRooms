 <div class="form-group" id="donation-form-group">
    <h3 class="form-group-title">Ajouter un don (facultatif)</h3>
    <fieldset class="form-element">
        <div class="form-field">
            <label for="donation" class="form-element-title">Don en {{$currency}}</label>
            <input
                type="number"
                min="0"
                step=".01"
                id="donation"
                name="donation"
                value="{{ old('donation') ?? $reservationDonation }}"
            >
        </div>

    </fieldset>
 </div>
