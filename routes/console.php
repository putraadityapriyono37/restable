<?php

use App\Jobs\CancelExpiredReservation;
use App\Models\Reservation;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Reservation::where('status', 'waiting_payment')
        ->where('created_at', '<=', now()->subMinutes(15))
        ->whereDoesntHave('transactions', function ($query) {
            $query->where('type', 'dp')->where('status', 'settlement');
        })
        ->pluck('id')
        ->each(fn ($id) => CancelExpiredReservation::dispatch($id));
})->everyMinute()->name('cancel-expired-reservations')->onOneServer();
