<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\CustomFieldTypes;

class CustomField extends Model
{
    /** @use HasFactory<\Database\Factories\CustomFieldFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id',
        'key',
        'label',
        'type',
        'options',
        'required',
        'active',
    ];

    protected $casts = [
        'type' => CustomFieldTypes::class,
        'options' => 'array',
        'required' => 'boolean',
        'active' => 'boolean',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
