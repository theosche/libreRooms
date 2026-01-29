<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Validation\ContactRules;
use App\Validation\CustomFieldValuesRules;
use App\Validation\ReservationRules;
use App\Validation\ReservationEventsValidator;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        parent::failedValidation($validator);
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            app(ReservationEventsValidator::class)->validate(
                validator: $validator,
                room: $this->route('room'),
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
        $room = $this->route('room'); // route model binding

        $rules = array_merge(
            ContactRules::rules($this),
            CustomFieldValuesRules::createRules($room),
            ReservationRules::createRules($room, $this->input('contact_type')),
        );
        if ($this->user()?->owners()->whereKey($room->owner_id)->exists()
            || $this->user()?->is_global_admin) {
            $rules = array_merge(
                $rules,
                ReservationRules::adminRules(),
            );
        }

        return $rules;
    }
}
