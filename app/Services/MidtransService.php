<?php

namespace App\Services;

use App\Models\User;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createSnapToken(string $orderId, int|float $amount, User $customer): string
    {
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) ceil($amount),
            ],
            'customer_details' => [
                'first_name' => $customer->name,
                'email' => $customer->email,
            ],
            'expiry' => [
                'unit' => 'minutes',
                'duration' => 15,
            ],
        ];

        return Snap::getSnapToken($params);
    }
}
