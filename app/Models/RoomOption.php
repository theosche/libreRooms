<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomOption extends Model
{
    /** @use HasFactory<\Database\Factories\RoomOptionFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id',
        'name',
        'description',
        'price',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
