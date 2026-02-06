<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomUnavailability extends Model
{
    protected $fillable = [
        'room_id',
        'title',
        'start',
        'end',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function startLocalTz(): Carbon
    {
        $timezone = $this->room->getTimezone();

        return $this->start->copy()->setTimezone($timezone);
    }

    public function endLocalTz(): Carbon
    {
        $timezone = $this->room->getTimezone();

        return $this->end->copy()->setTimezone($timezone);
    }
}
