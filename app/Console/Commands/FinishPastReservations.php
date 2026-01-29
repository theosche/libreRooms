<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Console\Command;

class FinishPastReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:finish-past';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark confirmed reservations as finished when all events have ended';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Find all CONFIRMED reservations where the last event has ended
        $count = Reservation::where('status', ReservationStatus::CONFIRMED)
            ->whereDoesntHave('events', function ($query) {
                $query->where('end', '>', now());
            })
            ->update(['status' => ReservationStatus::FINISHED]);

        $this->info("$count réservation(s) marquée(s) comme terminée(s).");

        return Command::SUCCESS;
    }
}
