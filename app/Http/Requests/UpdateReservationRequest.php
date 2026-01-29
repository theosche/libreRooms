<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Validation\ContactRules;
use App\Validation\CustomFieldValuesRules;
use App\Validation\ReservationRules;
use App\Validation\ReservationEventsValidator;
use App\Enums\ReservationStatus;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            app(ReservationEventsValidator::class)->validate(
                validator: $validator,
                room: $this->reservation->room,
                user: $this->user(),
                events: $this->input('events')
            );
        });
    }

    protected function prepareForValidation(): void
    {
        ContactRules::prepare($this);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $reservation = $this->route('reservation'); // route model binding

        $rules = array_merge(
            ContactRules::rules($this,$reservation->tenant),
            CustomFieldValuesRules::updateRules($reservation),
            ReservationRules::updateRules($reservation, $this->input('contact_type')),
        );
        if ($this->user()?->owners()->whereKey($reservation->room->owner_id)->exists()
            || $this->user()?->is_global_admin) {
            $rules = array_merge(
                $rules,
                ReservationRules::adminRules(),
            );
        }

        return $rules;
    }
}
