@extends('layouts.app')

@section('title', isset($contact) ? __('Edit contact') : __('New contact'))

@section('page-script')
    @vite(['resources/js/contacts/contact-form.js'])
@endsection

@section('content')
<div class="max-w-3xl mx-auto py-6">
    <div class="form-header">
        <h1 class="form-title">
            {{ isset($contact) ? __('Edit contact') : __('New contact') }}
        </h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ isset($contact) ? route('contacts.update', [$contact] + redirect_back_query()) : route('contacts.store', redirect_back_query()) }}" class="styled-form">
            @csrf
            @if(isset($contact))
                @method('PUT')
            @endif

            @php
                $contactType = old('contact_type') ?? $contact?->type->value ?? App\Enums\ContactTypes::INDIVIDUAL->value;
                $isOrganization = $contactType === App\Enums\ContactTypes::ORGANIZATION->value;
                $hasInvoiceEmail = old('has_invoice_email') ?? !is_null($contact?->invoice_email) ?? false;
            @endphp

            <div class="form-group">
                <h3 class="form-group-title">{{ __('Contact type') }}</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="contact_type" class="form-element-title">{{ __('Type') }}</label>
                        <select name="contact_type" id="contact_type" required>
                            @foreach (App\Enums\ContactTypes::cases() as $type)
                                <option value="{{ $type->value }}" @selected($contactType === $type->value)>
                                    {{ $type->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </fieldset>
            </div>

            <div class="form-group">
                <h3 class="form-group-title">{{ __('Informations') }}</h3>

                <fieldset class="form-element {{ $isOrganization ? '' : 'hidden' }}" data-show-when="{{ App\Enums\ContactTypes::ORGANIZATION->value }}">
                    <div class="form-field">
                        <label for="entity_name" class="form-element-title"> {{ __('Organization name') }}</label>
                        <input
                            type="text"
                            id="entity_name"
                            name="entity_name"
                            value="{{ old('entity_name', $contact?->entity_name) }}"
                            @if($isOrganization) required @endif
                        >
                        @error('entity_name')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field">
                            <label for="first_name" class="form-element-title">
                                {{ __('First name') }}
                                <span class="{{ $isOrganization ? '' : 'hidden' }}" data-show-when="{{ App\Enums\ContactTypes::ORGANIZATION->value }}">({{ __('contact person') }})</span>
                            </label>
                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                value="{{ old('first_name', $contact?->first_name) }}"
                                required
                            >
                            @error('first_name')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-field">
                            <label for="last_name" class="form-element-title">
                                {{ __('Last name') }}
                                <span class="{{ $isOrganization ? '' : 'hidden' }}" data-show-when="{{ App\Enums\ContactTypes::ORGANIZATION->value }}">({{ __('contact person') }})</span>
                            </label>
                            <input
                                type="text"
                                id="last_name"
                                name="last_name"
                                value="{{ old('last_name', $contact?->last_name) }}"
                                required
                            >
                            @error('last_name')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="email" class="form-element-title">{{ __('Email') }}</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $contact?->email) }}"
                            required
                        >
                        @error('email')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
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
                        <label for="has_invoice_email">{{ __('Different email for billing') }}</label>
                    </div>

                    <div class="form-field {{ $hasInvoiceEmail ? '' : 'hidden' }}" data-toggle="invoice-email">
                        <label for="invoice_email" class="form-element-title">{{ __('Billing email') }}</label>
                        <input
                            type="email"
                            id="invoice_email"
                            name="invoice_email"
                            value="{{ old('invoice_email', $contact?->invoice_email) }}"
                        >
                        @error('invoice_email')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="phone" class="form-element-title">{{ __('Phone') }}</label>
                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $contact?->phone) }}"
                            required
                        >
                        @error('phone')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>
            </div>

            <div class="form-group">
                <h3 class="form-group-title">{{ __('Address') }}</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="street" class="form-element-title">
                            <span class="{{ $isOrganization ? 'hidden' : '' }}" data-show-when="{{ App\Enums\ContactTypes::INDIVIDUAL->value }}">{{ __('Address') }}</span>
                            <span class="{{ $isOrganization ? '' : 'hidden' }}" data-show-when="{{ App\Enums\ContactTypes::ORGANIZATION->value }}">{{ __('Organization address') }}</span>
                        </label>
                        <textarea
                            rows="2"
                            id="street"
                            name="street"
                            required
                        >{{ old('street', $contact?->street) }}</textarea>
                        @error('street')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field">
                            <label for="zip" class="form-element-title">{{ __('ZIP') }}</label>
                            <input
                                type="text"
                                id="zip"
                                name="zip"
                                value="{{ old('zip', $contact?->zip) }}"
                                required
                            >
                            @error('zip')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-field">
                            <label for="city" class="form-element-title">{{ __('City') }}</label>
                            <input
                                type="text"
                                id="city"
                                name="city"
                                value="{{ old('city', $contact?->city) }}"
                                required
                            >
                            @error('city')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="btn-group justify-end mt-6">
                <a href="{{ redirect_back_url('contacts.index') }}" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($contact) ? __('Update') : __('Create') }}
                </button>
                @if(isset($contact))
                    <button type="button" onclick="confirmDeleteContact()" class="btn btn-delete">
                        {{ __('Delete') }}
                    </button>
                @endif
            </div>
        </form>
        @if(isset($contact))
            <form id="delete-contact-form" action="{{ route('contacts.destroy', [$contact] + redirect_back_query()) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</div>
<script>
    function confirmDeleteContact() {
        if (confirm('{{ __('Are you sure you want to delete this contact?') }}')) {
            document.getElementById('delete-contact-form').submit();
        }
    }
</script>
@endsection
