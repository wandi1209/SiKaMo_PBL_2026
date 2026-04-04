<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
}
