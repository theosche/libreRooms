@if($charter_mode != App\Enums\CharterModes::NONE)
    <div class="form-group" id="donation-form-group">
        <h3 class="form-group-title">Charte</h3>
        <div class="form-element">
            @if($charter_mode == App\Enums\CharterModes::TEXT)
                <div class="form-field">
                    <p id="charter-text">{{ $charter_str }}</p>
                </div>
            @endif
                <div class="form-field">
                    <input
                        type="checkbox"
                        id="charter_checkbox"
                        name="charter"
                        value="1"
                        required
                        @checked(!$isCreate || old('charter'))
                    >
                    <label for="charter_checkbox">
                        @if($charter_mode == App\Enums\CharterModes::TEXT)
                            J'ai pris connaissance de la charte et j'y adhère.
                        @elseif($charter_mode == App\Enums\CharterModes::LINK)
                            J'ai pris connaissance de <a href="{{ $charter_str }}">la charte</a> et j'y adhère.
                        @endif
                    </label>
                    @error('charter')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>
        </div>
    </div>
@endif
