<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Transaction;
use App\Services\MidtransService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function activeReservations(Request $request): View
    {
        $date = $request->query('date', now()->toDateString());

        $reservations = Reservation::with(['restaurant', 'table', 'user', 'transactions'])
            ->where('status', 'confirmed')
            ->where('reservation_date', $date)
            ->orderBy('reservation_time')
            ->paginate(20);

        return view('staff.reservations', compact('reservations', 'date'));
    }

    public function inputPelunasan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'amount' => 'required|numeric|min:1|max:100000000',
            'payment_method' => 'required|in:cash,midtrans',
            'confirm_cash' => 'required_if:payment_method,cash|accepted',
        ]);

        $reservation = Reservation::with(['transactions', 'restaurant'])
            ->findOrFail($validated['reservation_id']);

        if ($reservation->status !== 'confirmed') {
            throw ValidationException::withMessages([
                'reservation_id' => 'Hanya reservasi terkonfirmasi yang bisa dilunasi.',
            ]);
        }

        if ((float) $validated['amount'] < $reservation->remainingBalance() - 0.01) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal pelunasan kurang dari sisa tagihan.',
            ]);
        }

        if ($validated['payment_method'] === 'cash') {
            DB::transaction(function () use ($validated, $reservation, $request) {
                Transaction::create([
                    'reservation_id' => $reservation->id,
                    'type' => 'pelunasan',
                    'payment_method' => 'cash',
                    'midtrans_order_id' => null,
                    'amount' => $validated['amount'],
                    'status' => 'settlement',
                    'snap_token' => null,
                    'recorded_by' => $request->user()->id,
                    'raw_response' => ['recorded_via' => 'staff_cash'],
                ]);

                $this->completeReservation($reservation, $request->user()->id, 'Pelunasan cash diterima.');
            });

            return redirect()
                ->route('staff.reservations')
                ->with('status', 'Reservasi #' . $reservation->id . ' dilunasi (cash).');
        }

        return $this->createSnapPelunasan($request, $validated['reservation_id'], $validated['amount']);
    }

    public function createSnapForPelunasan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'amount' => 'required|numeric|min:1',
        ]);

        return $this->createSnapPelunasan($request, $validated['reservation_id'], $validated['amount']);
    }

    private function createSnapPelunasan(Request $request, int $reservationId, float $amount): RedirectResponse
    {
        $reservation = Reservation::findOrFail($reservationId);

        if ($reservation->status !== 'confirmed') {
            throw ValidationException::withMessages([
                'reservation_id' => 'Hanya reservasi terkonfirmasi yang bisa dilunasi.',
            ]);
        }

        $midtrans = app(MidtransService::class);
        $orderId = 'PL-' . $reservation->id . '-' . time();

        try {
            $snapToken = $midtrans->createSnapToken($orderId, (int) ceil($amount), $request->user());
        } catch (\Throwable $e) {
            report($e);

            throw ValidationException::withMessages([
                'message' => 'Gagal membuat token pembayaran pelunasan.',
            ]);
        }

        $transaction = Transaction::create([
            'reservation_id' => $reservation->id,
            'type' => 'pelunasan',
            'payment_method' => 'midtrans',
            'midtrans_order_id' => $orderId,
            'amount' => $amount,
            'status' => 'pending',
            'snap_token' => $snapToken,
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()->route('reservations.pay-pelunasan', [
            'reservation' => $reservation,
            'transaction' => $transaction,
        ]);
    }

    private function completeReservation(Reservation $reservation, int $userId, string $note): void
    {
        $previous = $reservation->status;
        $reservation->update(['status' => 'completed']);

        $reservation->logs()->create([
            'status_from' => $previous,
            'status_to' => 'completed',
            'changed_by' => $userId,
            'note' => $note,
        ]);
    }
}