<div class="form-group" id="contact-form-group">
    <h3 class="form-group-title">Coordonnées</h3>
    @guest
        <div class="info">Vous n'êtes pas connecté·e. Connectez-vous ou créez un compte pour pouvoir enregistrer vos informations et remplir les formulaires plus rapidement.</div>
    @endguest
    @auth
        @if ($contacts->isEmpty())
            <div class="info">Pas de coordonnées enregistrées. Ajoutez de nouvelles coordonnées. Elles seront sauvegardées pour votre prochaine visite.</div>
        @endif
        <fieldset class="form-element">
            <div class="form-field">
                <label for="contact-select" class="form-element-title">Charger des coordonnées *</label>
                <select name="contact_id" id="contact-select">
                    @php
                        $selectedContact = $contacts->find(old('contact_id')) ?? $tenant ?? $contacts?->first();
                    @endphp
                    <option value="" @selected(is_null($selectedContact))>
                        Nouvelles coordonnées
                    </option>
                    @foreach($contacts as $contact)
                        <option value="{{ $contact->id }}" @selected($selectedContact?->id == $contact->id)>
                            {{ $contact->display_name() }}
                        </option>
                    @endforeach
                </select>
            </div>
        </fieldset>
    @endauth

    <div id="contact-form">
        @php
            $currentContact = $selectedContact ?? $tenant;
            $contactType = old('contact_type') ??  $currentContact?->type->value ?? App\Enums\ContactTypes::ORGANIZATION->value;
            $isOrganization = $contactType === App\Enums\ContactTypes::ORGANIZATION->value;
            $hasInvoiceEmail = old('has_invoice_email') ?? !is_null($currentContact?->invoice_mail) ?? false;
            $contactFields = [
                'entity_name',
                'first_name',
                'last_name',
                'email',
                'invoice_email',
                'phone',
                'street',
                'zip',
                'city',
            ];
            $contactInfo = collect($contactFields)->mapWithKeys(fn ($field) => [
                $field => old($field) ?? $currentContact?->{$field},
            ]);
        @endphp

        <fieldset class="form-element">
            <legend class="form-element-title">Type *</legend>
            <div class="form-element-row">
            @foreach (App\Enums\ContactTypes::cases() as $type)
                <div class="form-field">
                    <input
                        type="radio"
                        id="type_{{$type->value}}"
                        name="contact_type"
                        value="{{$type->value}}"
                        required
                        @checked($contactType === $type->value)
                    >
                    <label for="type_{{$type->value}}">
                        {{$type->label()}}
                    </label><br>
                </div>
            @endforeach
            </div>
        </fieldset>

        <fieldset class="form-element {{ $isOrganization ? '' : 'hidden' }}" data-show-when="{{App\Enums\ContactTypes::ORGANIZATION->value}}">
            <div class="form-field">
                <label for="entity_name" class="form-element-title">Nom de l'organisation *</label>
                <input
                    type="text"
                    id="entity_name"
                    name="entity_name"
                    value="{{ $contactInfo['entity_name'] }}"
                    @required($isOrganization)
                >
            </div>
        </fieldset>

        <fieldset class="form-element">
            <div class="form-element-row">
                <div class="form-field">
                    <label for="first_name" class="form-element-title">Prénom
                        <span class="{{ $isOrganization ? '' : 'hidden' }}" data-show-when="{{App\Enums\ContactTypes::ORGANIZATION->value}}">(personne de contact)</span> *
                    </label>
                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        value="{{ $contactInfo['first_name'] }}"
                        required
                    >
                </div>
                <div class="form-field">
                    <label for="last_name" class="form-element-title">Nom
                        <span class="{{ $isOrganization ? '' : 'hidden' }}" data-show-when="{{App\Enums\ContactTypes::ORGANIZATION->value}}">(personne de contact)</span> *
                    </label>
                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        value="{{ $contactInfo['last_name'] }}"
                        required
                    >
                </div>
            </div>
        </fieldset>

        <fieldset class="form-element">
            <div class="form-field">
                <label for="email" class="form-element-title">Email *</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ $contactInfo['email'] }}"
                    required
                >
            </div>
        </fieldset>

        <fieldset class="form-element">
            <div class="form-field">
                <input
                    type="checkbox"
                    id="has_invoice_email"
                    name="has_invoice_email"
                    value="1"
                    @checked($hasInvoiceEmail)
                >
                <label for="has_invoice_email">Email différent pour la facturation</label>
            </div>

            <div class="form-field {{ $hasInvoiceEmail ? '' : 'hidden' }}" data-toggle="invoice-email">
                <label for="invoice_email" class="form-element-title">Email de facturation</label>
                <input
                    type="email"
                    id="invoice_email"
                    name="invoice_email"
                    value="{{ $contactInfo['invoice_email'] }}"
                >
            </div>
        </fieldset>

        <fieldset class="form-element">
            <div class="form-field">
                <label for="phone" class="form-element-title">Téléphone *</label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    value="{{ $contactInfo['phone'] }}"
                    required
                >
            </div>
        </fieldset>

        <fieldset class="form-element">
            <div class="form-field">
                <label for="street" class="form-element-title">
                    <span class="{{ $isOrganization ? 'hidden' : '' }}" data-show-when="{{App\Enums\ContactTypes::INDIVIDUAL->value}}">Adresse *</span>
                    <span class="{{ $isOrganization ? '' : 'hidden' }}" data-show-when="{{App\Enums\ContactTypes::ORGANIZATION->value}}">Adresse de l'organisation *</span>
                </label>
                <input
                    type="text"
                    id="street"
                    name="street"
                    value="{{ $contactInfo['street'] }}"
                    required
                >
            </div>
        </fieldset>

        <fieldset class="form-element">
            <div class="form-element-row">
                <div class="form-field">
                    <label for="zip" class="form-element-title">NPA *</label>
                    <input
                        type="text"
                        id="zip"
                        name="zip"
                        value="{{ $contactInfo['zip'] }}"
                        required
                    >
                </div>
                <div class="form-field">
                    <label for="city" class="form-element-title">Ville *</label>
                    <input
                        type="text"
                        id="city"
                        name="city"
                        value="{{ $contactInfo['city'] }}"
                        required
                    >
                </div>
            </div>
        </fieldset>
    </div>
</div>
