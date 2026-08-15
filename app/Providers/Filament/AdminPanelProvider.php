<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\ErpStatsOverview;
use App\Filament\Widgets\GreetingWidget;
use App\Filament\Widgets\OngoingProjectsWidget;
use App\Filament\Widgets\PendingTasksWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
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
            ->login()
            ->brandName(config('erp.name', 'Instacertify ERP'))
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::hex('#065175'),
                'warning' => Color::hex('#ec6820'),
                'danger' => Color::hex('#ec6820'),
                'gray' => Color::Slate,
            ])
            ->font('Figtree')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                GreetingWidget::class,
                ErpStatsOverview::class,
                OngoingProjectsWidget::class,
                PendingTasksWidget::class,
            ])
            ->navigationGroups([
                'CRM',
                'Sales',
                'Projects',
                'Calendar',
                'Collaboration',
                'Storage',
                'Libraries',
                'Assets',
                'Quality',
                'HR',
                'Finance',
                'My Profile',
                'Settings',
            ])
            ->sidebarCollapsibleOnDesktop()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
