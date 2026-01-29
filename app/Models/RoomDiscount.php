<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\DiscountTypes;
use App\Enums\ContactTypes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomDiscount extends Model
{
    /** @use HasFactory<\Database\Factories\RoomDiscountFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id',
        'name',
        'description',
        'type',
        'limit_to_contact_type',
        'value',
        'active',
    ];

    protected $casts = [
        'type' => DiscountTypes::class,
        'active' => 'boolean',
        'limit_to_contact_type' => ContactTypes::class,
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
