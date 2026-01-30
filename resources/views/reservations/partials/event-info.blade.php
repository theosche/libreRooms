 <div class="form-group" id="event-info-form-group">
    <h3 class="form-group-title">Informations générales</h3>
    <fieldset class="form-element">
        <div class="form-field">
            <label for="res_title" class="form-element-title">Nom de l'événement/activité *</label>
            <input
                type="text"
                id="res_title"
                name="res_title"
                required
                value="{{ old('res_title') ?? $title }}"
            >
            @error('res_title')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </fieldset>
     <fieldset class="form-element">
         <div class="form-field">
             <label for="res_description" class="form-element-title">Description et demandes particulières</label>
             <textarea
                 rows="5"
                 id="res_description"
                 name="res_description"
             >{{ old('res_description') ?? $description }}</textarea>
             @error('res_description')
                 <span class="text-red-600 text-sm">{{ $message }}</span>
             @enderror
         </div>
     </fieldset>
 </div>
