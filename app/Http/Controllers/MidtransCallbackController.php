<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Notifications\ReservationStatusNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransCallbackController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (! $this->isValidSignature($payload)) {
            Log::warning('Midtrans: invalid signature', ['order_id' => $payload['order_id'] ?? null]);

            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderId = $payload['order_id'] ?? null;

        if (! $orderId) {
            return response()->json(['message' => 'Missing order_id'], 422);
        }

        $transaction = Transaction::where('midtrans_order_id', $orderId)->first();

        if (! $transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        if (in_array($transaction->status, ['settlement', 'cancel', 'deny'], true)) {
            return response()->json(['message' => 'Already processed'], 200);
        }

        $status = $this->mapStatus($payload['transaction_status'] ?? '');

        DB::transaction(function () use ($transaction, $payload, $status) {
            $transaction->update([
                'status' => $status,
                'raw_response' => $payload,
            ]);

            $reservation = $transaction->reservation()->lockForUpdate()->first();

            if (! $reservation) {
                return;
            }

            $previous = $reservation->status;
            $newStatus = null;
            $note = null;

            if ($status === 'settlement') {
                if ($transaction->type === 'dp') {
                    $newStatus = 'confirmed';
                    $note = 'Pembayaran DP berhasil.';
                } else {
                    $newStatus = 'completed';
                    $note = 'Pelunasan via sistem berhasil.';
                }
            } elseif (in_array($status, ['cancel', 'deny', 'expire'], true) && $transaction->type === 'dp') {
                $newStatus = 'cancelled';
                $note = 'Pembayaran DP gagal ('.$status.').';
            }

            if ($newStatus && $previous !== $newStatus) {
                $reservation->update(['status' => $newStatus]);
                $reservation->logs()->create([
                    'status_from' => $previous,
                    'status_to' => $newStatus,
                    'changed_by' => null,
                    'note' => $note,
                ]);

                $reservation->user->notify(new ReservationStatusNotification($reservation, $newStatus));
            }
        });

        return response()->json(['message' => 'OK']);
    }

    private function isValidSignature(array $payload): bool
    {
        $serverKey = config('services.midtrans.server_key');

        if (! $serverKey) {
            return false;
        }

        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expected, $signatureKey);
    }

    private function mapStatus(string $transactionStatus): string
    {
        return match ($transactionStatus) {
            'capture', 'settlement' => 'settlement',
            'pending' => 'pending',
            'deny' => 'deny',
            'cancel' => 'cancel',
            'expire' => 'expire',
            default => 'pending',
        };
    }
}
