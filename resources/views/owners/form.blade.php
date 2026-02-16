@extends('layouts.app')

@section('title', isset($owner) ? __('Edit owner') : __('New owner'))

@section('page-script')
    @vite(['resources/js/owners/owner-form.js'])
    <script>
        window.ownerId = {{ $owner->id ?? 'null' }};
        window.translations = {
            testing: @json(__('Testing...')),
            verifying: @json(__('Verifying...')),
        };
    </script>
@endsection

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="form-header">
        <h1 class="form-title">
            {{ isset($owner) ? __('Edit owner') : __('New owner') }}
        </h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ isset($owner) ? route('owners.update', [$owner] + redirect_back_query()) : route('owners.store', redirect_back_query()) }}" class="styled-form">
            @csrf
            @if(isset($owner))
                @method('PUT')
            @endif

            @php
                // Determine if using default configs (for editing)
                // Priority: old() > existing owner data
                $useDefaultMail = old('use_default_mail') !== null
                    ? (bool) old('use_default_mail')
                    : (!isset($owner) || is_null($owner->mail_host));
                $useDefaultCaldav = old('use_default_caldav') !== null
                    ? (bool) old('use_default_caldav')
                    : (!isset($owner) || is_null($owner->dav_url));
                $useDefaultWebdav = old('use_default_webdav') !== null
                    ? (bool) old('use_default_webdav')
                    : (!isset($owner) || is_null($owner->webdav_endpoint));

                // Determine if CalDAV/WebDAV is enabled
                $useCaldav = old('use_caldav') !== null
                    ? (bool) old('use_caldav')
                    : (isset($owner) && $owner->use_caldav);
                $useWebdav = old('use_webdav') !== null
                    ? (bool) old('use_webdav')
                    : (isset($owner) && $owner->use_webdav);
            @endphp

            <!-- Contact et Slug -->
            <div class="form-group">
                <h3 class="form-group-title">{{ __('Basic information') }}</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="contact_id" class="form-element-title">{{ __('Contact') }}</label>
                        <select name="contact_id" id="contact_id" required>
                            <option value="">{{ __('Select a contact') }}</option>
                            @foreach($contacts as $contact)
                                @php
                                    $isCurrentContact = isset($currentContactId) && $contact->id === $currentContactId;
                                    $showCurrentLabel = $isCurrentContact && isset($currentContactInUserList) && !$currentContactInUserList;
                                @endphp
                                <option value="{{ $contact->id }}" @selected(old('contact_id', $owner?->contact_id) == $contact->id)>
                                    {{ $contact->display_name() }}@if($showCurrentLabel) ({{ __('Current contact') }})@endif
                                </option>
                            @endforeach
                        </select>
                        @error('contact_id')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field">
                            <label class="flex items-center gap-2">
                                <input type="hidden" name="hide_email" value="0">
                                <input
                                    type="checkbox"
                                    name="hide_email"
                                    value="1"
                                    @checked(old('hide_email', $owner?->hide_email ?? false))
                                >
                                {{ __('Hide email on public pages') }}
                            </label>
                        </div>
                        <div class="form-field">
                            <label class="flex items-center gap-2">
                                <input type="hidden" name="hide_phone" value="0">
                                <input
                                    type="checkbox"
                                    name="hide_phone"
                                    value="1"
                                    @checked(old('hide_phone', $owner?->hide_phone ?? false))
                                >
                                {{ __('Hide phone number on public pages') }}
                            </label>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="slug" class="form-element-title">{{ __('Slug (unique identifier)') }}</label>
                        <input
                            type="text"
                            id="slug"
                            name="slug"
                            value="{{ old('slug', $owner?->slug) }}"
                            required
                            placeholder="{{ __('e.g.: my-organization') }}"
                        >
                        <small class="text-gray-600">{{ __('Used in URLs. Only lowercase letters, numbers, and hyphens.') }}</small>
                        @error('slug')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="website" class="form-element-title">{{ __('Website (optional)') }}</label>
                        <input
                            type="url"
                            id="website"
                            name="website"
                            value="{{ old('website', $owner?->website) }}"
                            placeholder="https://www.example.com"
                        >
                        @error('website')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>
            </div>

            <!-- Paramètres de facturation -->
            <div class="form-group">
                <h3 class="form-group-title">{{ __('Billing settings') }}</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="invoice_due_mode" class="form-element-title">{{ __('Invoice due date mode') }}</label>
                        <select name="invoice_due_mode" id="invoice_due_mode" required>
                            <option value="">{{ __('Select a mode') }}</option>
                            @foreach(App\Enums\InvoiceDueModes::cases() as $mode)
                                <option value="{{ $mode->value }}" @selected(old('invoice_due_mode', $owner?->invoice_due_mode?->value) == $mode->value)>
                                    {{ $mode->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('invoice_due_mode')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field">
                            <label for="invoice_due_days" class="form-element-title">{{ __('Payment delay (days)') }}</label>
                            <input
                                type="number"
                                id="invoice_due_days"
                                name="invoice_due_days"
                                value="{{ old('invoice_due_days', $owner?->invoice_due_days) }}"
                                required
                                min="0"
                            >
                            @error('invoice_due_days')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label for="invoice_due_days_after_reminder" class="form-element-title">{{ __('Delay after reminder (days)') }}</label>
                            <input
                                type="number"
                                id="invoice_due_days_after_reminder"
                                name="invoice_due_days_after_reminder"
                                value="{{ old('invoice_due_days_after_reminder', $owner?->invoice_due_days_after_reminder) }}"
                                required
                                min="0"
                            >
                            @error('invoice_due_days_after_reminder')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="max_nb_reminders" class="form-element-title">{{ __('Maximum number of reminders') }}</label>
                        <input
                            type="number"
                            id="max_nb_reminders"
                            name="max_nb_reminders"
                            value="{{ old('max_nb_reminders', $owner?->max_nb_reminders) }}"
                            required
                            min="0"
                        >
                        @error('max_nb_reminders')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="late_invoices_reminder" class="form-element-title">{{ __('Late invoices reminder') }}</label>
                        <select id="late_invoices_reminder" name="late_invoices_reminder">
                            @foreach(\App\Enums\LateInvoicesReminderFrequency::cases() as $frequency)
                                <option value="{{ $frequency->value }}"
                                    {{ old('late_invoices_reminder', $owner?->late_invoices_reminder?->value ?? 'never') == $frequency->value ? 'selected' : '' }}>
                                    {{ $frequency->label() }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-sm text-gray-500 mt-1">{{ __('Receive an email reminder when there are late invoices to manage') }}</p>
                        @error('late_invoices_reminder')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>
            </div>

            <!-- Instructions de paiement -->
            @php
                $paymentInstructions = old('payment_instructions', $owner?->payment_instructions) ?? [];
                $paymentType = old('payment_type', $paymentInstructions['type'] ?? '');
            @endphp
            <div class="form-group">
                <h3 class="form-group-title">{{ __('Payment instructions') }}</h3>
                <p class="text-sm text-gray-600 mb-4">{{ __('Configure the payment information that will appear on invoices') }}</p>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="payment_type" class="form-element-title">{{ __('Instruction type') }}</label>
                        <select name="payment_type" id="payment_type">
                            <option value="">{{ __('None') }}</option>
                            <option value="international" @selected($paymentType === 'international')>{{ __('International (IBAN/BIC)') }}</option>
                            <option value="sepa" @selected($paymentType === 'sepa')>{{ __('SEPA with QR Code') }}</option>
                            <option value="swiss" @selected($paymentType === 'swiss')>{{ __('Swiss QR Invoice') }}</option>
                        </select>
                        @error('payment_type')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <!-- All payment fields (shown/hidden based on type) -->
                <div id="payment-fields" class="{{ $paymentType ? '' : 'hidden' }}">
                    <fieldset class="form-element">
                        <div class="form-field">
                            <label for="payment_vat_number" class="form-element-title">{{ __('VAT number (optional)') }}</label>
                            <input
                                type="text"
                                id="payment_vat_number"
                                name="payment_vat_number"
                                value="{{ old('payment_vat_number', $paymentInstructions['vat_number'] ?? '') }}"
                                placeholder="ex: CHE-123.456.789"
                            >
                            @error('payment_vat_number')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </fieldset>

                    <fieldset class="form-element">
                        <div class="form-element-row">
                            <div class="form-field">
                                <label for="payment_account_holder" class="form-element-title">{{ __('Account holder') }}</label>
                                <input
                                    type="text"
                                    id="payment_account_holder"
                                    name="payment_account_holder"
                                    value="{{ old('payment_account_holder', $paymentInstructions['account_holder'] ?? '') }}"
                                    placeholder="{{ __('Beneficiary name') }}"
                                >
                                @error('payment_account_holder')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="payment_iban" class="form-element-title">{{ __('IBAN') }}</label>
                                <input
                                    type="text"
                                    id="payment_iban"
                                    name="payment_iban"
                                    value="{{ old('payment_iban', $paymentInstructions['iban'] ?? '') }}"
                                    placeholder="CH93 0076 2011 6238 5295 7"
                                >
                                @error('payment_iban')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </fieldset>

                    <!-- BIC field (optional for international, required for SEPA, hidden for swiss) -->
                    <fieldset class="form-element" id="payment-bic-section">
                        <div class="form-element-row">
                            <div class="form-field">
                                <label for="payment_bic" class="form-element-title">
                                    <span id="payment-bic-label">BIC/SWIFT</span>
                                    <span id="payment-bic-optional" class="text-gray-500 text-xs">({{ __('optional') }})</span>
                                </label>
                                <input
                                    type="text"
                                    id="payment_bic"
                                    name="payment_bic"
                                    value="{{ old('payment_bic', $paymentInstructions['bic'] ?? '') }}"
                                    placeholder="BCVLCH2LXXX"
                                >
                                <small id="payment-bic-hint" class="text-gray-600 hidden">{{ __('Required to generate the SEPA QR code') }}</small>
                                @error('payment_bic')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Bank name (only for international) -->
                            <div class="form-field" id="payment-bank-name-section">
                                <label for="payment_bank_name" class="form-element-title">{{ __('Bank name (optional)') }}</label>
                                <input
                                    type="text"
                                    id="payment_bank_name"
                                    name="payment_bank_name"
                                    value="{{ old('payment_bank_name', $paymentInstructions['bank_name'] ?? '') }}"
                                    placeholder="Banque XYZ"
                                >
                                @error('payment_bank_name')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </fieldset>

                    <!-- Address fields (optional for international, required for swiss, hidden for sepa) -->
                    <fieldset class="form-element" id="payment-address-section">
                        <div class="form-field">
                            <label class="form-element-title">
                                <span id="payment-address-label">{{ __('Address') }}</span>
                                <span id="payment-address-optional" class="text-gray-500 text-xs">({{ __('optional') }})</span>
                                <span id="payment-address-required" class="text-gray-500 text-xs hidden">({{ __('required for Swiss QR') }})</span>
                            </label>
                        </div>
                        <div class="form-element-row">
                            <div class="form-field">
                                <input
                                    type="text"
                                    id="payment_address_street"
                                    name="payment_address_street"
                                    value="{{ old('payment_address_street', $paymentInstructions['address']['street'] ?? '') }}"
                                    placeholder="{{ __('Street') }}"
                                >
                            </div>
                            <div class="form-field" style="max-width: 100px;">
                                <input
                                    type="text"
                                    id="payment_address_zip"
                                    name="payment_address_zip"
                                    value="{{ old('payment_address_zip', $paymentInstructions['address']['zip'] ?? '') }}"
                                    placeholder="{{ __('ZIP') }}"
                                >
                            </div>
                            <div class="form-field">
                                <input
                                    type="text"
                                    id="payment_address_city"
                                    name="payment_address_city"
                                    value="{{ old('payment_address_city', $paymentInstructions['address']['city'] ?? '') }}"
                                    placeholder="{{ __('City') }}"
                                >
                            </div>
                            <div class="form-field" style="max-width: 80px;">
                                <input
                                    type="text"
                                    id="payment_address_country"
                                    name="payment_address_country"
                                    value="{{ old('payment_address_country', $paymentInstructions['address']['country'] ?? '') }}"
                                    placeholder="CH"
                                    maxlength="2"
                                >
                            </div>
                        </div>
                        @error('payment_address_street')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                        @error('payment_address_zip')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                        @error('payment_address_city')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                        @error('payment_address_country')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </fieldset>

                    <!-- BESR-ID field (only for swiss) -->
                    <fieldset class="form-element" id="payment-besr-section">
                        <div class="form-field">
                            <label for="payment_besr_id" class="form-element-title">
                                BESR-ID <span class="text-gray-500 text-xs">({{ __('optional - leave empty for PostFinance') }})</span>
                            </label>
                            <input
                                type="text"
                                id="payment_besr_id"
                                name="payment_besr_id"
                                value="{{ old('payment_besr_id', $paymentInstructions['besr_id'] ?? '') }}"
                                placeholder="{{ __('e.g.: 210000') }}"
                                maxlength="6"
                                style="max-width: 150px;"
                            >
                            <small class="text-gray-600">{{ __('Code provided by your bank for structured QR references') }}</small>
                            @error('payment_besr_id')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </fieldset>
                </div>
            </div>

            <!-- Configuration email -->
            <div class="form-group">
                <h3 class="form-group-title">{{ __('Email configuration') }}</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label class="flex items-center gap-2">
                            <input type="hidden" name="use_default_mail" value="0">
                            <input
                                type="checkbox"
                                id="use_default_mail"
                                name="use_default_mail"
                                value="1"
                                @checked($hasDefaultMail && $useDefaultMail)
                                @disabled(!$hasDefaultMail)
                            >
                            <span class="font-medium">
                                {{ __('Use default configuration') }}
                                @if(!$hasDefaultMail)
                                    <span class="text-red-600 text-sm">({{ __('no default configuration available') }})</span>
                                @endif
                            </span>
                        </label>
                    </div>
                </fieldset>

                <!-- Default mail settings display -->
                <div id="mail-defaults" class="{{ ($hasDefaultMail && $useDefaultMail) ? '' : 'hidden' }}">
                    <fieldset class="form-element">
                        <div class="bg-gray-50 p-4 rounded-md text-sm">
                            <p><strong>{{ __('Server') }}:</strong> {{ $systemSettings?->mail_host }}:{{ $systemSettings?->mail_port }}</p>
                            <p><strong>{{ __('User') }}:</strong> {{ $systemSettings?->mail }}</p>
                            <p><strong>{{ __('Password') }}:</strong> ••••••••</p>
                        </div>
                    </fieldset>
                </div>

                <!-- Custom mail settings inputs -->
                <div id="mail-inputs" class="{{ (!$hasDefaultMail || !$useDefaultMail) ? '' : 'hidden' }}">
                    <fieldset class="form-element">
                        <div class="form-element-row">
                            <div class="form-field">
                                <label for="mail_host" class="form-element-title">{{ __('SMTP server') }}</label>
                                <input
                                    type="text"
                                    id="mail_host"
                                    name="mail_host"
                                    value="{{ old('mail_host', $owner?->mail_host) }}"
                                >
                                @error('mail_host')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="mail_port" class="form-element-title">{{ __('Port') }}</label>
                                <input
                                    type="number"
                                    id="mail_port"
                                    name="mail_port"
                                    value="{{ old('mail_port', $owner?->mail_port) }}"
                                    min="1"
                                    max="65535"
                                >
                                @error('mail_port')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="form-element">
                        <div class="form-element-row">
                            <div class="form-field">
                                <label for="mail" class="form-element-title">{{ __('Email (SMTP user)') }}</label>
                                <input
                                    type="text"
                                    id="mail"
                                    name="mail"
                                    value="{{ old('mail', $owner?->mail) }}"
                                >
                                @error('mail')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="mail_pass" class="form-element-title">
                                    {{ __('SMTP password') }}
                                    @if(isset($owner) && $owner->mail_pass)
                                        <span class="text-xs text-gray-500">({{ __('leave empty to keep current') }})</span>
                                    @endif
                                </label>
                                <input
                                    type="password"
                                    id="mail_pass"
                                    name="mail_pass"
                                    value="{{ old('mail_pass') }}"
                                    @if(isset($owner) && $owner->mail_pass)
                                        placeholder="***************"
                                    @endif
                                >
                                @error('mail_pass')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div id="mail-status" class="config-status hidden"></div>
            </div>

            <!-- Configuration CalDAV -->
            <div class="form-group">
                <h3 class="form-group-title">{{ __('CalDAV configuration') }}</h3>
                <p>{{ __('If a CalDAV configuration is provided, rooms can use an external CalDAV calendar to check availability.') }}</p>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label class="flex items-center gap-2">
                            <input type="hidden" name="use_caldav" value="0">
                            <input
                                type="checkbox"
                                id="use_caldav"
                                name="use_caldav"
                                value="1"
                                @checked($useCaldav)
                            >
                            <span class="font-medium">{{ __('Enable CalDAV') }}</span>
                        </label>
                    </div>
                </fieldset>

                <div id="caldav-config" class="{{ $useCaldav ? '' : 'hidden' }}">
                    <fieldset class="form-element">
                        <div class="form-field">
                            <label class="flex items-center gap-2">
                                <input type="hidden" name="use_default_caldav" value="0">
                                <input
                                    type="checkbox"
                                    id="use_default_caldav"
                                    name="use_default_caldav"
                                    value="1"
                                    @checked($hasDefaultCaldav && $useDefaultCaldav)
                                    @disabled(!$hasDefaultCaldav)
                                >
                                <span class="font-medium">
                                    {{ __('Use the default configuration defined for the system') }}
                                    @if(!$hasDefaultCaldav)
                                        <span class="text-red-600 text-sm">({{ __('no default configuration available') }})</span>
                                    @endif
                                </span>
                            </label>
                        </div>
                    </fieldset>

                    <!-- Default caldav settings display -->
                    <div id="caldav-defaults" class="{{ ($hasDefaultCaldav && $useDefaultCaldav) ? '' : 'hidden' }}">
                        <fieldset class="form-element">
                            <div class="bg-gray-50 p-4 rounded-md text-sm">
                                <p><strong>{{ __('URL') }}:</strong> {{ $systemSettings?->dav_url }}</p>
                                <p><strong>{{ __('User') }}:</strong> {{ $systemSettings?->dav_user }}</p>
                                <p><strong>{{ __('Password') }}:</strong> ••••••••</p>
                            </div>
                        </fieldset>
                    </div>

                    <!-- Custom caldav settings inputs -->
                    <div id="caldav-inputs" class="{{ (!$hasDefaultCaldav || !$useDefaultCaldav) ? '' : 'hidden' }}">
                        <fieldset class="form-element">
                            <div class="form-field">
                                <label for="dav_url" class="form-element-title">{{ __('CalDAV URL') }}</label>
                                <input
                                    type="text"
                                    id="dav_url"
                                    name="dav_url"
                                    value="{{ old('dav_url', $owner?->dav_url) }}"
                                >
                                @error('dav_url')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </fieldset>

                        <fieldset class="form-element">
                            <div class="form-element-row">
                                <div class="form-field">
                                    <label for="dav_user" class="form-element-title">{{ __('CalDAV user') }}</label>
                                    <input
                                        type="text"
                                        id="dav_user"
                                        name="dav_user"
                                        value="{{ old('dav_user', $owner?->dav_user) }}"
                                    >
                                    @error('dav_user')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-field">
                                    <label for="dav_pass" class="form-element-title">
                                        {{ __('CalDAV password') }}
                                        @if(isset($owner) && $owner->dav_pass)
                                            <span class="text-xs text-gray-500">({{ __('leave empty to keep current') }})</span>
                                        @endif
                                    </label>
                                    <input
                                        type="password"
                                        id="dav_pass"
                                        name="dav_pass"
                                        value="{{ old('dav_pass') }}"
                                        @if(isset($owner) && $owner->dav_pass)
                                            placeholder="***************"
                                        @endif
                                    >
                                    @error('dav_pass')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <div id="caldav-status" class="config-status hidden"></div>
                </div>
            </div>

            <!-- Configuration WebDAV -->
            <div class="form-group">
                <h3 class="form-group-title">{{ __('WebDAV configuration') }}</h3>
                <p>{{ __('If a WebDAV configuration is provided, generated PDF documents (confirmations and invoices) will be stored on the WebDAV server.') }}</p>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label class="flex items-center gap-2">
                            <input type="hidden" name="use_webdav" value="0">
                            <input
                                type="checkbox"
                                id="use_webdav"
                                name="use_webdav"
                                value="1"
                                @checked($useWebdav)
                            >
                            <span class="font-medium">{{ __('Enable WebDAV') }}</span>
                        </label>
                    </div>
                </fieldset>

                <div id="webdav-config" class="{{ $useWebdav ? '' : 'hidden' }}">
                    <fieldset class="form-element">
                        <div class="form-field">
                            <label class="flex items-center gap-2">
                                <input type="hidden" name="use_default_webdav" value="0">
                                <input
                                    type="checkbox"
                                    id="use_default_webdav"
                                    name="use_default_webdav"
                                    value="1"
                                    @checked($hasDefaultWebdav && $useDefaultWebdav)
                                    @disabled(!$hasDefaultWebdav)
                                >
                                <span class="font-medium">
                                    {{ __('Use the default configuration defined for the system') }}
                                    @if(!$hasDefaultWebdav)
                                        <span class="text-red-600 text-sm">({{ __('no default configuration available') }})</span>
                                    @endif
                                </span>
                            </label>
                        </div>
                    </fieldset>

                    <!-- Default webdav settings display -->
                    <div id="webdav-defaults" class="{{ ($hasDefaultWebdav && $useDefaultWebdav) ? '' : 'hidden' }}">
                        <fieldset class="form-element">
                            <div class="bg-gray-50 p-4 rounded-md text-sm">
                                <p><strong>{{ __('Endpoint') }}:</strong> {{ $systemSettings?->webdav_endpoint }}</p>
                                <p><strong>{{ __('User') }}:</strong> {{ $systemSettings?->webdav_user }}</p>
                                <p><strong>{{ __('Password') }}:</strong> ••••••••</p>
                                <p><strong>{{ __('Path') }}:</strong> {{ $systemSettings?->webdav_save_path }}</p>
                            </div>
                        </fieldset>
                    </div>

                    <!-- Custom webdav settings inputs -->
                    <div id="webdav-inputs" class="{{ (!$hasDefaultWebdav || !$useDefaultWebdav) ? '' : 'hidden' }}">
                        <fieldset class="form-element">
                            <div class="form-field">
                                <label for="webdav_endpoint" class="form-element-title">{{ __('WebDAV endpoint') }}</label>
                                <input
                                    type="text"
                                    id="webdav_endpoint"
                                    name="webdav_endpoint"
                                    value="{{ old('webdav_endpoint', $owner?->webdav_endpoint) }}"
                                >
                                @error('webdav_endpoint')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </fieldset>

                        <fieldset class="form-element">
                            <div class="form-element-row">
                                <div class="form-field">
                                    <label for="webdav_user" class="form-element-title">{{ __('WebDAV user') }}</label>
                                    <input
                                        type="text"
                                        id="webdav_user"
                                        name="webdav_user"
                                        value="{{ old('webdav_user', $owner?->webdav_user) }}"
                                    >
                                    @error('webdav_user')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-field">
                                    <label for="webdav_pass" class="form-element-title">
                                        {{ __('WebDAV password') }}
                                        @if(isset($owner) && $owner->webdav_pass)
                                            <span class="text-xs text-gray-500">({{ __('leave empty to keep current') }})</span>
                                        @endif
                                    </label>
                                    <input
                                        type="password"
                                        id="webdav_pass"
                                        name="webdav_pass"
                                        value="{{ old('webdav_pass') }}"
                                        @if(isset($owner) && $owner->webdav_pass)
                                            placeholder="***************"
                                        @endif
                                    >
                                    @error('webdav_pass')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="form-element">
                            <div class="form-field">
                                <label for="webdav_save_path" class="form-element-title">{{ __('Save path') }}</label>
                                <input
                                    type="text"
                                    id="webdav_save_path"
                                    name="webdav_save_path"
                                    value="{{ old('webdav_save_path', $owner?->webdav_save_path) }}"
                                >
                                @error('webdav_save_path')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </fieldset>
                    </div>

                    <div id="webdav-status" class="config-status hidden"></div>
                </div>
            </div>

            <!-- Paramètres régionaux -->
            <div class="form-group">
                <h3 class="form-group-title">{{ __('Regional settings (optional)') }}</h3>
                <p class="text-sm text-gray-600 mb-4">{{ __('Leave empty to use system default settings') }}</p>

                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field">
                            <label for="timezone" class="form-element-title">{{ __('Timezone') }}</label>
                            @include('partials._timezone_select', [
                                'name' => 'timezone',
                                'id' => 'timezone',
                                'value' => old('timezone') ?? $owner?->timezone,
                                'defaultTimezone' => $systemSettings?->getTimezone() ?? __('Not defined'),
                            ])
                            @error('timezone')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label for="currency" class="form-element-title">{{ __('Currency') }}</label>
                            @include('partials._currency_select', [
                                'name' => 'currency',
                                'id' => 'currency',
                                'value' => old('currency', $owner?->currency),
                                'defaultCurrency' => $systemSettings->getCurrency(),
                            ])
                            @error('currency')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="locale" class="form-element-title">{{ __('Language') }}</label>
                        @include('partials._locale_select', [
                            'name' => 'locale',
                            'id' => 'locale',
                            'value' => old('locale', $owner?->locale),
                            'defaultLocale' => $systemSettings->getLocale(),
                        ])
                        @error('locale')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>
            </div>

            <div class="btn-group justify-end mt-6">
                <a href="{{ redirect_back_url('owners.index') }}" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($owner) ? __('Update') : __('Create') }}
                </button>
                @if(isset($owner))
                    @php
                        $user = auth()->user();
                        $otherUsers = $owner->users->where('id', '!=', $user->id);
                    @endphp
                    @if($otherUsers->count() > 0 && !$user->is_global_admin)
                        <button type="button" onclick="confirmDeleteOwner()" class="btn btn-delete">
                            {{ __('Remove') }}
                        </button>
                    @else
                        <button type="button" onclick="confirmDeleteOwner()" class="btn btn-delete">
                            {{ __('Delete') }}
                        </button>
                    @endif
                @endif
            </div>
        </form>
        @if(isset($owner))
            <form id="delete-owner-form" action="{{ route('owners.destroy', [$owner] + redirect_back_query()) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</div>
<script>
    @if(isset($owner))
    function confirmDeleteOwner() {
        @if($otherUsers->count() > 0 && !$user->is_global_admin)
            if (confirm('{{ __('Are you sure you want to remove this owner from your list? Other users also have access to it, it will not be permanently deleted.') }}')) {
                document.getElementById('delete-owner-form').submit();
            }
        @else
            if (confirm('{{ __('Are you sure you want to permanently delete this owner? This action cannot be undone and will also delete all associated rooms.') }}')) {
                document.getElementById('delete-owner-form').submit();
            }
        @endif
    }
    @endif
</script>
@endsection
