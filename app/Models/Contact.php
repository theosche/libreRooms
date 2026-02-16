<?php

namespace App\Models;

use App\Enums\ContactTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    /** @use HasFactory<\Database\Factories\ContactFactory> */
    use HasFactory;

    protected $fillable = [
        'type',
        'first_name',
        'last_name',
        'entity_name',
        'email',
        'invoice_email',
        'phone',
        'street',
        'zip',
        'city',
    ];

    protected $casts = [
        'type' => ContactTypes::class,
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'tenant_id');
    }

    public function owners(): HasMany
    {
        return $this->hasMany(Owner::class);
    }

    public function display_name(): string
    {
        return $this->type === ContactTypes::ORGANIZATION
            ? $this->entity_name
            : "{$this->first_name} {$this->last_name}";
    }

    public function invoiceEmail(): string
    {
        return $this->invoice_email ?? $this->email;
    }

    public function bothEmailsUnique(): array
    {
        $emails = $this->invoice_email ? [$this->email, $this->invoice_email] : [$this->email];

        return array_unique($emails);
    }
}
