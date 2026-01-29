 <div class="form-group" id="custom-message-form-group">
    <h3 class="form-group-title">Indications particulières (admin)</h3>
     <fieldset class="form-element">
         <div class="form-field">
             <label for="custom_message" class="form-element-title">Ces indications seront transmises dans le mail de confirmation.</label>
             <textarea
                 rows="5"
                 id="custom_message"
                 name="custom_message"
             >{{ old('custom_message') ?? $customMessage }}</textarea>
         </div>
     </fieldset>
 </div>
