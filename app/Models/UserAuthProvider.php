<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAuthProvider extends Model
{
    protected $fillable = [
        'user_id',
        'provider_sub',
        'provider_id',
        'access_token',
        'refresh_token',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function provider(): BelongsTo
    {
        return $this->belongsTo(IdentityProvider::class);
    }
}
