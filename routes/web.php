<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditLogDownloadController;
use App\Http\Controllers\TransactionController;
use App\Livewire\Merchant\SalesPage;
use App\Models\User;

// Landing page — selalu tampil, tombol adaptif sesuai status login (@auth di Blade)
Route::get('/', function () {
    return view('landing');
});

// Merchant Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/leave-impersonation', function () {
        $adminId = session()->pull('impersonate_original_id');

        abort_if(!$adminId, 404);

        $admin = User::where('role', 'admin')->findOrFail($adminId);

        auth()->login($admin);
        session()->put('password_hash_web', $admin->getAuthPassword());

        return redirect('/admin');
    })->name('admin.leave-impersonation');

    // Custom Sales Page without Filament sidebar — redirect to the cashier launcher page in Filament
    Route::get('/merchant/sales', function () {
        $merchant = auth()->user()?->merchant;
        if ($merchant && $merchant->slug) {
            return redirect()->to("/merchant/toko/{$merchant->slug}/sales");
        }
        return redirect()->route('merchant.sales.catalog');
    })->name('merchant.sales');

    // Dedicated catalog page that mounts the Livewire sales component
    Route::get('/merchant/sales/catalog', SalesPage::class)->name('merchant.sales.catalog');

    // Download single audit log entry (PDF)
    Route::get('/merchant/audit-logs/{auditLog}/download', AuditLogDownloadController::class)
        ->name('merchant.audit-logs.download');

    // Transaction Routes
    Route::prefix('merchant/transactions')->name('transactions.')->group(function () {
        Route::post('/checkout', [TransactionController::class, 'checkout'])->name('checkout');
        Route::get('/{invoiceNumber}', [TransactionController::class, 'show'])->name('show');
    });
});
