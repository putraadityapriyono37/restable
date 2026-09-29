<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\MidtransCallbackController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationPayController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ==========================
// Webhook Midtrans (Single Action Controller — pakai __invoke())
// ==========================
Route::post('/midtrans/callback', MidtransCallbackController::class)
    ->middleware('throttle:60,1')
    ->name('midtrans.callback');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==========================
// Route Reservasi (Customer)
// ==========================
Route::middleware('auth')->group(function () {
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{reservation}/pay', [ReservationController::class, 'pay'])->name('reservations.pay');
    Route::get('/reservations/{reservation}/pay/{transaction}', ReservationPayController::class)
        ->name('reservations.pay-pelunasan');
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
});

// ==========================
// Route Staff & Admin
// ==========================
Route::middleware(['auth', 'permission:lihat-reservasi-aktif'])->group(function () {
    Route::get('/staff/reservations', [StaffController::class, 'activeReservations'])->name('staff.reservations');
});

Route::middleware(['auth', 'permission:input-pelunasan-cash'])->group(function () {
    Route::post('/staff/pelunasan', [StaffController::class, 'inputPelunasan'])->name('staff.pelunasan');
    Route::post('/staff/pelunasan/snap', [StaffController::class, 'createSnapForPelunasan'])->name('staff.pelunasan.snap');
});

// ==========================
// Route Khusus Admin
// ==========================
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/restaurant-settings', [AdminController::class, 'editSettings'])->name('admin.restaurant-settings.edit');
    Route::put('/admin/restaurant-settings', [AdminController::class, 'updateSettings'])->name('admin.restaurant-settings.update');
    Route::resource('/admin/tables', TableController::class)
        ->parameters(['tables' => 'table'])
        ->names([
            'index' => 'admin.tables.index',
            'create' => 'admin.tables.create',
            'store' => 'admin.tables.store',
            'show' => 'admin.tables.show',
            'edit' => 'admin.tables.edit',
            'update' => 'admin.tables.update',
            'destroy' => 'admin.tables.destroy',
        ]);
    Route::get('/admin/reconciliation', [AdminController::class, 'reconciliation'])->name('admin.reconciliation');
    Route::post('/admin/reconciliation', [AdminController::class, 'storeReconciliation'])->name('admin.reconciliation.store');
});

require __DIR__.'/auth.php';
