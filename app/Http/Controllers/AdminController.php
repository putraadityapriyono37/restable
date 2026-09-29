<?php

namespace App\Http\Controllers;

use App\Models\CashReconciliation;
use App\Models\Restaurant;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(Request $request): View
    {
        $restaurant = $this->restaurant($request);
        $today = now()->toDateString();

        $statusCounts = $restaurant
            ->reservations()
            ->whereDate('reservation_date', $today)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $revenueToday = (float) Transaction::whereIn(
            'reservation_id',
            $restaurant->reservations()->select('id')
        )
            ->where('status', 'settlement')
            ->whereDate('created_at', $today)
            ->sum('amount');

        $revenueByMethod = Transaction::whereIn(
            'reservation_id',
            $restaurant->reservations()->select('id')
        )
            ->where('status', 'settlement')
            ->whereDate('created_at', $today)
            ->selectRaw('payment_method, type, SUM(amount) as total')
            ->groupBy('payment_method', 'type')
            ->get();

        return view('admin.dashboard', compact('restaurant', 'statusCounts', 'revenueToday', 'revenueByMethod'));
    }

    public function editSettings(Request $request): View
    {
        return view('admin.settings', ['restaurant' => $this->restaurant($request)]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i|after:open_time',
            'slot_duration' => 'required|integer|min:15|max:480',
            'dp_type' => 'required|in:fixed,percentage',
            'dp_value' => 'required|numeric|min:0',
        ]);

        $this->restaurant($request)->update($validated);

        return redirect()
            ->route('admin.restaurant-settings.edit')
            ->with('status', 'Pengaturan restoran diperbarui.');
    }

    public function reconciliation(Request $request): View
    {
        $restaurant = $this->restaurant($request);
        $date = $request->query('date', now()->toDateString());

        $totalCashSystem = (float) Transaction::whereIn(
            'reservation_id',
            $restaurant->reservations()->select('id')
        )
            ->where('payment_method', 'cash')
            ->where('status', 'settlement')
            ->whereDate('created_at', $date)
            ->sum('amount');

        $existing = CashReconciliation::where('restaurant_id', $restaurant->id)
            ->where('date', $date)
            ->first();

        return view('admin.reconciliation', compact('restaurant', 'date', 'totalCashSystem', 'existing'));
    }

    public function storeReconciliation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'total_cash_physical' => 'required|numeric|min:0',
            'staff_id' => 'required|exists:users,id',
            'note' => 'nullable|string|max:500',
        ]);

        $restaurant = $this->restaurant($request);

        $totalCashSystem = (float) Transaction::whereIn(
            'reservation_id',
            $restaurant->reservations()->select('id')
        )
            ->where('payment_method', 'cash')
            ->where('status', 'settlement')
            ->whereDate('created_at', $validated['date'])
            ->sum('amount');

        $selisih = (float) $validated['total_cash_physical'] - $totalCashSystem;

        if (abs($selisih) > 0.01 && empty($validated['note'])) {
            throw ValidationException::withMessages([
                'note' => 'Catatan wajib diisi jika ada selisih.',
            ]);
        }

        $alreadyConfirmed = CashReconciliation::where('restaurant_id', $restaurant->id)
            ->where('date', $validated['date'])
            ->whereNotNull('confirmed_by')
            ->exists();

        if ($alreadyConfirmed) {
            throw ValidationException::withMessages([
                'date' => 'Rekonsiliasi tanggal tersebut sudah dikonfirmasi.',
            ]);
        }

        DB::transaction(function () use ($restaurant, $validated, $totalCashSystem, $selisih, $request) {
            CashReconciliation::updateOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'date' => $validated['date'],
                ],
                [
                    'staff_id' => $validated['staff_id'],
                    'total_cash_system' => $totalCashSystem,
                    'total_cash_physical' => $validated['total_cash_physical'],
                    'selisih' => $selisih,
                    'note' => $validated['note'] ?? null,
                    'confirmed_by' => $request->user()->id,
                ]
            );
        });

        return redirect()
            ->route('admin.reconciliation', ['date' => $validated['date']])
            ->with('status', 'Rekonsiliasi kas berhasil disimpan.');
    }

    private function restaurant(Request $request): Restaurant
    {
        return Restaurant::where('owner_id', $request->user()->id)->firstOrFail();
    }
}
