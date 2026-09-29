<?php

namespace App\Http\Controllers;

use App\Jobs\CancelExpiredReservation;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Transaction;
use App\Services\MidtransService;
use App\Services\TableAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(Request $request): View
    {
        $reservations = Reservation::with(['restaurant', 'table', 'transactions'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('reservations.index', compact('reservations'));
    }

    public function create(Request $request): View
    {
        $restaurants = Restaurant::orderBy('name')->get();

        $selectedRestaurant = $restaurants->first(
            fn ($restaurant) => $restaurant->id == $request->query('restaurant_id')
        ) ?? $restaurants->first();

        $slots = $selectedRestaurant ? $this->generateSlots($selectedRestaurant) : collect();

        return view('reservations.create', compact('restaurants', 'selectedRestaurant', 'slots'));
    }

    public function store(Request $request, MidtransService $midtrans, TableAvailabilityService $availability): RedirectResponse
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|date_format:H:i',
            'guest_count' => 'required|integer|min:1|max:50',
            'estimated_total' => 'required|numeric|min:1000|max:100000000',
            'notes' => 'nullable|string|max:500',
        ]);

        $restaurant = Restaurant::findOrFail($validated['restaurant_id']);

        if (! $availability->isWithinOperatingHours($restaurant, $validated['reservation_time'])) {
            throw ValidationException::withMessages([
                'reservation_time' => 'Jam di luar jam operasional restoran.',
            ]);
        }

        $reservation = DB::transaction(function () use ($validated, $request, $restaurant, $midtrans, $availability) {
            $table = $availability->findAvailableTable(
                $restaurant->id,
                $validated['reservation_date'],
                $validated['reservation_time'],
                (int) $validated['guest_count']
            );

            if (! $table) {
                throw ValidationException::withMessages([
                    'reservation_time' => 'Tidak ada meja tersedia untuk jam tersebut.',
                ]);
            }

            $reservation = Reservation::create([
                'user_id' => $request->user()->id,
                'restaurant_id' => $restaurant->id,
                'table_id' => $table->id,
                'reservation_date' => $validated['reservation_date'],
                'reservation_time' => $validated['reservation_time'],
                'guest_count' => $validated['guest_count'],
                'estimated_total' => $validated['estimated_total'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'waiting_payment',
            ]);

            $reservation->logs()->create([
                'status_from' => null,
                'status_to' => 'waiting_payment',
                'changed_by' => $request->user()->id,
                'note' => 'Reservasi dibuat, menunggu pembayaran DP.',
            ]);

            $dpAmount = $restaurant->dp_type === 'percentage'
                ? max(1000, (int) ceil(($restaurant->dp_value / 100) * (float) $reservation->estimated_total))
                : (int) $restaurant->dp_value;

            $orderId = 'DP-'.$reservation->id.'-'.time();

            try {
                $snapToken = $midtrans->createSnapToken($orderId, $dpAmount, $request->user());
            } catch (\Throwable $e) {
                report($e);

                $reservation->update(['status' => 'cancelled']);
                $reservation->logs()->create([
                    'status_from' => 'waiting_payment',
                    'status_to' => 'cancelled',
                    'changed_by' => $request->user()->id,
                    'note' => 'Gagal membuat token pembayaran.',
                ]);

                throw ValidationException::withMessages([
                    'message' => 'Gagal memuat pembayaran. Coba lagi nanti.',
                ]);
            }

            Transaction::create([
                'reservation_id' => $reservation->id,
                'type' => 'dp',
                'payment_method' => 'midtrans',
                'midtrans_order_id' => $orderId,
                'amount' => $dpAmount,
                'status' => 'pending',
                'snap_token' => $snapToken,
            ]);

            dispatch(new CancelExpiredReservation($reservation->id))->delay(now()->addMinutes(15));

            return $reservation;
        });

        return redirect()->route('reservations.pay', $reservation);
    }

    public function pay(Reservation $reservation): View
    {
        $this->authorizeReservation($reservation);

        $dpTransaction = $reservation->transactions()
            ->where('type', 'dp')
            ->latest()
            ->firstOrFail();

        if ($dpTransaction->status === 'settlement') {
            return view('reservations.show', [
                'reservation' => $reservation->load(['restaurant', 'table', 'transactions', 'logs']),
                'dpTransaction' => $dpTransaction,
            ]);
        }

        return view('reservations.pay', [
            'reservation' => $reservation,
            'snapToken' => $dpTransaction->snap_token,
        ]);
    }

    public function show(Reservation $reservation): View
    {
        $this->authorizeReservation($reservation);

        $reservation->load([
            'restaurant',
            'table',
            'transactions',
            'logs' => fn ($query) => $query->latest(),
        ]);

        $dpTransaction = $reservation->transactions()
            ->where('type', 'dp')
            ->latest()
            ->first();

        return view('reservations.show', compact('reservation', 'dpTransaction'));
    }

    private function authorizeReservation(Reservation $reservation): void
    {
        $user = auth()->user();

        abort_unless(
            $reservation->user_id === $user->id || $user->hasAnyRole(['admin', 'staff']),
            403
        );
    }

    private function generateSlots(Restaurant $restaurant): Collection
    {
        $slots = collect();
        $current = $this->toMinutes($restaurant->open_time);
        $close = $this->toMinutes($restaurant->close_time);
        $duration = (int) $restaurant->slot_duration;

        while ($current + $duration <= $close) {
            $slots->push(sprintf('%02d:%02d', intdiv($current, 60), $current % 60));
            $current += $duration;
        }

        return $slots;
    }

    private function toMinutes(string $time): int
    {
        [$hour, $minute] = array_map('intval', explode(':', substr($time, 0, 5)));

        return $hour * 60 + $minute;
    }
}
