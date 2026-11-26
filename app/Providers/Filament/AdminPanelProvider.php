<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\RecentOrdersTable;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\QuickActions;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->brandName(function () {
                try {
                    return \App\Models\Setting::get('site_name') ?: config('shop.name');
                } catch (\Throwable $e) {
                    return config('shop.name');
                }
            })
            ->id('admin')
            ->path('admin')
            ->login()
            ->databaseNotifications()
            ->databaseNotificationsPolling('10s')
            ->colors([
                'primary' => \App\Support\Ui::adminTheme() === 'emerald' ? Color::Emerald : Color::Sky,
            ])
            ->navigationGroups([
                NavigationGroup::make('ই-কমার্স'),
                NavigationGroup::make('ওয়েবসাইট'),
                NavigationGroup::make('সেটিংস'),
                NavigationGroup::make('রিপোর্ট'),
            ])
            ->navigationItems([
                NavigationItem::make('স্টোরফ্রন্ট')
                    ->icon('heroicon-o-building-storefront')
                    ->group('ই-কমার্স')
                    ->sort(1)
                    ->url('/', shouldOpenInNewTab: true),
            ])
            ->maxContentWidth(MaxWidth::Full)
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => view('filament.hooks.admin-style'))
            ->renderHook(PanelsRenderHook::PAGE_START, fn () => view('filament.hooks.subscription-banner'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\WelcomeBanner::class,
                StatsOverview::class,
                \App\Filament\Widgets\TopProducts::class,
                \App\Filament\Widgets\CategoryRevenue::class,
                RecentOrdersTable::class,
                SalesChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                \App\Http\Middleware\SubscriptionGuard::class,
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
