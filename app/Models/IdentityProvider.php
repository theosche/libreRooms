<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IdentityProvider extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'driver',
        'issuer_url',
        'client_id',
        'client_secret',
        'enabled',
    ];
    protected $hidden = [
        'client_secret',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'client_secret' => 'encrypted',
    ];

    function userAuth(): HasMany
    {
        return $this->hasMany(UserAuthProvider::class);
    }
}
