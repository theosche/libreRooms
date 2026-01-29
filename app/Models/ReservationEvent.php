<?php

namespace App\Models;

use App\DTO\EventDTO;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;
use App\Enums\ReservationStatus;

class ReservationEvent extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationEventFactory> */
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'start',
        'end',
        'uid',
        'price',
        'price_label',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    public function eventDTO(bool $addStatusInTitle = false, ?ReservationStatus $forceStatus = null): EventDTO
    {
        $title = $addStatusInTitle ?
            $this->reservation->title . ' - ' . ($forceStatus?->label() ?? $this->reservation->status->label()) :
            $this->reservation->title;
        return new EventDTO(
            title: $title ,
            status: $this->reservation->status->icalStatus(),
            start: $this->start,
            end: $this->end,
            uid: $this->uid,
            created: $this->created_at,
            updated: $this->updated_at,
            location: $this->reservation->room->name
        );
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(RoomOption::class, 'reservation_event_option')
            ->withPivot('price');
    }

    public function startLocalTz(): Carbon
    {
        $timezone = $this->reservation->room->getTimezone();
        return $this->start->copy()->setTimezone($timezone);
    }
    public function endLocalTz(): Carbon
    {
        $timezone = $this->reservation->room->getTimezone();
        return $this->end->copy()->setTimezone($timezone);
    }
}
