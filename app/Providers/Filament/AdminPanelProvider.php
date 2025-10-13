<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\OverviewStats;
use App\Filament\Widgets\RecentRegistrations;
use App\Filament\Widgets\RecentScans;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->globalSearch(false)
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn () => view('filament.topbar.scan-now')
            )
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn () => view('filament.topbar.event-switcher')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                OverviewStats::class,
                RecentRegistrations::class,
                RecentScans::class,
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
            ->renderHook('panels::head.end', fn () => view('filament.partials.custom-styles'))
            ->renderHook('panels::body.end', fn () => view('filament.partials.seat-picker-js'))
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

