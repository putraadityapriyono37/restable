<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Transaction;
use Illuminate\View\View;

class ReservationPayController extends Controller
{
    public function __invoke(Reservation $reservation, Transaction $transaction): View
    {
        abort_unless($transaction->reservation_id === $reservation->id, 404);
        abort_unless($transaction->type === 'pelunasan', 404);

        $user = auth()->user();
        abort_unless(
            $reservation->user_id === $user->id || $user->hasAnyRole(['admin', 'staff']),
            403
        );

        return view('reservations.pay-pelunasan', [
            'reservation' => $reservation,
            'transaction' => $transaction,
            'snapToken' => $transaction->snap_token,
        ]);
    }
}
