<?php

namespace App\Validation;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Enums\ContactTypes;
use App\Models\Contact;

class ContactRules
{
    public static function prepare(Request $request): void
    {
        if ($request->input('contact_type') !== ContactTypes::ORGANIZATION->value) {
            $request->merge(['entity_name' => null]);
        }
        if (!$request->boolean('has_invoice_email')) {
            $request->merge(['invoice_email' => null]);
        }
    }

    public static function rules(Request $request, ?Contact $contact = null): array
    {
        $userId = auth()->id();

        return [
            'contact_id'        => ['nullable', 'integer',
                                    function ($attribute, $value, $fail) use ($userId, $contact) {
                                        // Allow keeping the existing contact
                                        if ($contact && $value == $contact->id) {
                                            return;
                                        }
                                        // Otherwise, must be a contact the user has access to
                                        $exists = DB::table('contact_user')
                                            ->where('contact_id', $value)
                                            ->where('user_id', $userId)
                                            ->exists();
                                        if (!$exists) {
                                            $fail('Le contact sélectionné n\'est pas valide.');
                                        }
                                    },
                                    ],
            'contact_type'      => ['required', Rule::in(array_map(fn ($case) => $case->value,ContactTypes::cases())),],
            'entity_name'       => ['nullable', 'string', 'max:255',
                                    Rule::requiredIf(fn () => $request->input('contact_type') === ContactTypes::ORGANIZATION->value),
                                    ],
            'first_name'        => ['required', 'string', 'max:255'],
            'last_name'         => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email'],
            'invoice_email'     => ['nullable', 'email'],
            'phone'             => ['required', 'string', 'max:25'],
            'street'            => ['required', 'string', 'max:255'],
            'zip'               => ['required', 'string', 'max:25'],
            'city'              => ['required', 'string', 'max:255'],
        ];
    }
}
