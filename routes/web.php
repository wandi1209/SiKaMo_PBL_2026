<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;

// Landing page — selalu tampil, tombol adaptif sesuai status login (@auth di Blade)
Route::get('/', function () {
    return view('landing');
});

// Merchant Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Custom Sales Page without Filament sidebar
    Route::get('/merchant/sales', App\Livewire\Merchant\SalesPage::class)->name('merchant.sales');

    // Transaction Routes
    Route::prefix('merchant/transactions')->name('transactions.')->group(function () {
        Route::post('/checkout', [TransactionController::class, 'checkout'])->name('checkout');
        Route::get('/{invoiceNumber}', [TransactionController::class, 'show'])->name('show');
    });
});
