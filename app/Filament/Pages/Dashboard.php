<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ErpStatsOverview;
use App\Filament\Widgets\GreetingWidget;
use App\Filament\Widgets\OngoingProjectsWidget;
use App\Filament\Widgets\PendingTasksWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int|array
    {
        return 12;
    }

    /**
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        return [
            GreetingWidget::class,
            ErpStatsOverview::class,
            OngoingProjectsWidget::class,
            PendingTasksWidget::class,
        ];
    }
}
