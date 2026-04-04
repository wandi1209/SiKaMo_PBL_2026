<?php

namespace App\Providers\Filament;

use App\Models\Merchant;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class MerchantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('merchant')
            ->path('merchant')
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::hex('#1D9E75'),
                'success' => Color::hex('#639922'),
                'warning' => Color::hex('#BA7517'),
                'danger'  => Color::hex('#E24B4A'),
                'info'    => Color::hex('#378ADD'),
                'gray'    => Color::Slate,
            ])
            // ─── Multi-Tenancy ──────────────────────────────────────
            // Filament akan otomatis scope semua query berdasarkan Merchant
            // yang dimiliki owner yang sedang login.
            ->tenant(Merchant::class, ownershipRelationship: 'merchant')
            ->tenantProfile(\App\Filament\Merchant\Pages\EditMerchantProfile::class)
            ->tenantRoutePrefix('toko') // URL: /merchant/toko/{slug}/...
            // Tambahkan menu Profil Toko ke Sidebar secara manual
            ->navigationItems([
                \Filament\Navigation\NavigationItem::make('Profil Toko')
                    ->icon('heroicon-o-building-storefront')
                    ->url(fn () => \App\Filament\Merchant\Pages\EditMerchantProfile::getUrl())
                    ->sort(2),
            ])
            // ─── Discovery ─────────────────────────────────────────
            ->discoverResources(
                in: app_path('Filament/Merchant/Resources'),
                for: 'App\\Filament\\Merchant\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Merchant/Pages'),
                for: 'App\\Filament\\Merchant\\Pages'
            )
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Merchant/Widgets'),
                for: 'App\\Filament\\Merchant\\Widgets'
            )
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

