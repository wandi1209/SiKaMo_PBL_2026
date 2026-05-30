<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\DailyReport;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use App\Observers\AuditObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \Filament\Http\Responses\Auth\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([Category::class, DailyReport::class, Merchant::class, Product::class, Purchase::class, Supplier::class, Transaction::class, User::class] as $model) {
            $model::observe(AuditObserver::class);
        }

        // Catat aktivitas autentikasi ke Audit Log (login/logout).
        // Ini dibutuhkan karena AuditObserver hanya menangkap perubahan model, bukan event auth.
        Event::listen(Login::class, function (Login $event): void {
            $this->recordAuthEvent('login', $event->user, $event->guard);
        });

        Event::listen(Logout::class, function (Logout $event): void {
            $this->recordAuthEvent('logout', $event->user, $event->guard);
        });

        \Filament\Support\Facades\FilamentView::registerRenderHook(
            \Filament\View\PanelsRenderHook::HEAD_END,
            fn (): string => \Illuminate\Support\Facades\Blade::render('
                <style>
                    /* Memberikan warna putih bersih pada background seluruh sidebar */
                    aside.fi-sidebar {
                        background-color: #ffffff !important;
                    }
                    /* Background untuk mode malam (dark mode) */
                    .dark aside.fi-sidebar {
                        background-color: #111827 !important;
                    }
                </style>
            ')
        );
    }

    private function recordAuthEvent(string $event, mixed $user, string $guard): void
    {
        if (! ($user instanceof User)) {
            return;
        }

        $merchantId = $user->merchant_id
            ?? $user->merchant?->id
            ?? Merchant::query()->where('owner_id', $user->id)->value('id');

        $key = 'audit_request_id';

        if (! app()->bound($key)) {
            app()->instance($key, (string) Str::uuid());
        }

        AuditLog::create([
            'user_id' => $user->id,
            'merchant_id' => $merchantId,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'event' => $event,
            // Untuk login/logout, kita anggap auditable = user yang melakukan aksi.
            'auditable_type' => User::class,
            'auditable_table' => (new User())->getTable(),
            'auditable_id' => $user->id,
            'auditable_label' => $user->name,
            'old_values' => null,
            'new_values' => null,
            'changed_fields' => [],
            'summary' => $event === 'login'
                ? "User {$user->name} login."
                : "User {$user->name} logout.",
            'request_method' => request()?->method(),
            'request_url' => request()?->fullUrl(),
            'route_name' => request()?->route()?->getName(),
            'panel' => filament()?->getCurrentPanel()?->getId(),
            'request_id' => app($key),
            'metadata' => [
                'guard' => $guard,
            ],
            'ip_address' => request()?->ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 255),
        ]);
    }
}
