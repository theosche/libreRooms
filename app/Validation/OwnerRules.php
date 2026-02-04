<?php

namespace App\Validation;

use App\Enums\InvoiceDueModes;
use App\Enums\LateInvoicesReminderFrequency;
use App\Models\Owner;
use App\Services\Settings\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OwnerRules
{
    public static function prepare(Request $request): void
    {
        if ($request->input('use_default_mail')) {
            $request->merge([
                'mail_host' => null,
                'mail_port' => null,
                'mail' => null,
                'mail_pass' => null,
            ]);
        }

        if (! $request->input('use_caldav') || $request->input('use_default_caldav')) {
            $request->merge([
                'dav_url' => null,
                'dav_user' => null,
                'dav_pass' => null,
            ]);
        }

        if (! $request->input('use_webdav') || $request->input('use_default_webdav')) {
            $request->merge([
                'webdav_endpoint' => null,
                'webdav_user' => null,
                'webdav_pass' => null,
                'webdav_save_path' => null,
            ]);
        }

        // Build payment_instructions JSON from form fields
        $paymentType = $request->input('payment_type');
        if (empty($paymentType)) {
            $request->merge(['payment_instructions' => null]);
        } else {
            $paymentInstructions = [
                'type' => $paymentType,
                'vat_number' => $request->input('payment_vat_number') ?: null,
                'account_holder' => $request->input('payment_account_holder'),
                'iban' => $request->input('payment_iban'),
                'bic' => $request->input('payment_bic') ?: null,
                'bank_name' => $request->input('payment_bank_name') ?: null,
                'besr_id' => $request->input('payment_besr_id') ?: null,
                'address' => [
                    'street' => $request->input('payment_address_street') ?: null,
                    'zip' => $request->input('payment_address_zip') ?: null,
                    'city' => $request->input('payment_address_city') ?: null,
                    'country' => $request->input('payment_address_country') ?: null,
                ],
            ];
            $request->merge(['payment_instructions' => $paymentInstructions]);
        }
    }

    public static function rules(?Owner $owner = null): array
    {
        $user = auth()->user();
        $userId = $user->id;
        $settingsService = app(SettingsService::class);
        $request = request();

        // Check if using default configs
        $mailFieldsRequired = ! $request->input('use_default_mail');

        // Build contact_id validation rules
        $contactIdRules = ['required', 'integer'];
        if ($user->is_global_admin) {
            // Global admins can use any contact
            $contactIdRules[] = Rule::exists('contacts', 'id');
        } else {
            // Others can use their own contacts OR the current owner's contact (when editing)
            $contactIdRules[] = function ($attribute, $value, $fail) use ($userId, $owner) {
                // Check if contact is in user's contacts
                $userHasContact = \DB::table('contact_user')
                    ->where('user_id', $userId)
                    ->where('contact_id', $value)
                    ->exists();

                if ($userHasContact) {
                    return;
                }

                // When editing, also allow the current owner's contact
                if ($owner && $owner->contact_id == $value) {
                    return;
                }

                $fail(__('The selected contact is not valid.'));
            };
        }

        $rules = [
            'contact_id' => $contactIdRules,
            'slug' => [
                'required',
                'string',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique('owners', 'slug')->ignore($owner?->id),
            ],
            'website' => ['nullable', 'url', 'max:255'],

            // Champs obligatoires de facturation
            'invoice_due_mode' => [
                'required',
                Rule::in(array_map(fn ($case) => $case->value, InvoiceDueModes::cases())),
            ],
            'invoice_due_days' => ['required', 'integer', 'min:0'],
            'invoice_due_days_after_reminder' => ['required', 'integer', 'min:0'],
            'max_nb_reminders' => ['required', 'integer', 'min:0'],
            'late_invoices_reminder' => [
                'required',
                Rule::in(array_map(fn ($case) => $case->value, LateInvoicesReminderFrequency::cases())),
            ],

            // Configuration email
            // If use_default_mail is checked: all fields should be nullable (using system defaults)
            // If use_default_mail is NOT checked: all fields are required
            'mail_host' => [
                $mailFieldsRequired ? 'required' : 'nullable',
                'string',
                'max:255',
            ],
            'mail_port' => [
                $mailFieldsRequired ? 'required' : 'nullable',
                'integer',
                'min:1',
                'max:65535',
            ],
            'mail' => [
                $mailFieldsRequired ? 'required' : 'nullable',
                'string',
                'max:255',
            ],
        ];

        if ($mailFieldsRequired) {
            // Required if no password set yet (otherwise nullable to allow users to keep the same password)
            if (! $owner?->mail_pass || ! empty($request->input('mail_pass'))) {
                $rules['mail_pass'] = ['required', 'string', 'max:255'];
            }
            // Otherwise, keep password unchanged, don't include a new password in validated request
        } else {
            $rules['mail_pass'] = ['nullable'];
        }

        // Configuration CalDAV
        $rules['use_caldav'] = ['boolean'];
        $caldavFieldsRequired = $request->input('use_caldav') && ! $request->input('use_default_caldav');
        $rules['dav_url'] = [$caldavFieldsRequired ? 'required' : 'nullable', 'url', 'max:255'];
        $rules['dav_user'] = [$caldavFieldsRequired ? 'required' : 'nullable', 'string', 'max:255'];
        if ($caldavFieldsRequired) {
            // Required if no password set yet (otherwise nullable to allow users to keep the same password)
            if (! $owner?->dav_pass || ! empty($request->input('dav_pass'))) {
                $rules['dav_pass'] = ['required', 'string', 'max:255'];
            }
            // Otherwise, keep password unchanged, don't include a new password in validated request
        } else {
            $rules['dav_pass'] = ['nullable']; // Set to null in prepare()
        }

        // Configuration WebDAV
        $rules['use_webdav'] = ['boolean'];
        $webdavFieldsRequired = $request->input('use_webdav') && ! $request->input('use_default_webdav');
        $rules['webdav_endpoint'] = [$webdavFieldsRequired ? 'required' : 'nullable', 'url', 'max:255'];
        $rules['webdav_user'] = [$webdavFieldsRequired ? 'required' : 'nullable', 'string', 'max:255'];
        if ($webdavFieldsRequired) {
            // Required if no password set yet (otherwise nullable to allow users to keep the same password)
            if (! $owner?->webdav_pass || ! empty($request->input('webdav_pass'))) {
                $rules['webdav_pass'] = ['required', 'string', 'max:255'];
            }
            // Otherwise, keep password unchanged, don't include a new password in validated request
        } else {
            $rules['webdav_pass'] = ['nullable']; // Set to null in prepare()
        }
        $rules['webdav_save_path'] = [$webdavFieldsRequired ? 'required' : 'nullable', 'string', 'max:255'];

        // Champs optionnels - paramètres régionaux
        $rules['timezone'] = ['nullable', 'string', 'max:100'];
        $rules['currency'] = ['nullable', 'string', 'max:10'];
        $rules['locale'] = ['nullable', 'string', 'max:10'];

        // Payment instructions - validate the built array from prepare()
        $rules['payment_instructions'] = ['nullable', 'array'];

        // Also validate individual fields for user feedback
        $rules['payment_type'] = ['nullable', Rule::in(['international', 'sepa', 'swiss'])];

        $paymentType = $request->input('payment_type');
        if ($paymentType) {
            // Common fields for all payment types
            $rules['payment_vat_number'] = ['nullable', 'string', 'max:50'];
            $rules['payment_account_holder'] = ['required', 'string', 'max:255'];
            $rules['payment_iban'] = ['required', 'string', 'max:34'];

            if ($paymentType === 'international') {
                $rules['payment_bic'] = ['nullable', 'string', 'max:11'];
                $rules['payment_bank_name'] = ['nullable', 'string', 'max:255'];
                $rules['payment_address_street'] = ['nullable', 'string', 'max:255'];
                $rules['payment_address_zip'] = ['nullable', 'string', 'max:20'];
                $rules['payment_address_city'] = ['nullable', 'string', 'max:100'];
                $rules['payment_address_country'] = ['nullable', 'string', 'max:2'];
            } elseif ($paymentType === 'sepa') {
                $rules['payment_bic'] = ['required', 'string', 'max:11'];
            } elseif ($paymentType === 'swiss') {
                // Swiss QR requires address for creditor
                $rules['payment_address_street'] = ['required', 'string', 'max:255'];
                $rules['payment_address_zip'] = ['required', 'string', 'max:20'];
                $rules['payment_address_city'] = ['required', 'string', 'max:100'];
                $rules['payment_address_country'] = ['required', 'string', 'max:2'];
                // BESR-ID is optional (null for PostFinance, otherwise provided by bank)
                $rules['payment_besr_id'] = ['nullable', 'string', 'regex:/^\d{1,6}$/'];
            }
        }
        return $rules;
    }
}
