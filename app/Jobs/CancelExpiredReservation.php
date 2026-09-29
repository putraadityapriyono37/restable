<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CancelExpiredReservation implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $reservationId,
    ) {}

    public function handle(): void
    {
        $reservation = Reservation::with(['transactions', 'user'])
            ->find($this->reservationId);

        if (! $reservation || $reservation->status !== 'waiting_payment') {
            return;
        }

        $reservation->update(['status' => 'cancelled']);

        $reservation->transactions()
            ->where('type', 'dp')
            ->where('status', 'pending')
            ->update(['status' => 'expire']);

        $reservation->logs()->create([
            'status_from' => 'waiting_payment',
            'status_to' => 'cancelled',
            'changed_by' => null,
            'note' => 'Dibatalkan otomatis: DP tidak dibayar dalam 15 menit.',
        ]);

        Log::info('Auto-cancelled expired reservation', ['id' => $reservation->id]);
    }
}
