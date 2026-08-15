<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Sample;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ErpStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Customers', Customer::query()->count())
                ->description('Active CRM accounts')
                ->icon('heroicon-o-building-office'),
            Stat::make('Projects', Project::query()->where('status', 'active')->count())
                ->description('Currently active')
                ->icon('heroicon-o-folder'),
            Stat::make('Open invoices', Invoice::query()->whereIn('status', ['sent', 'partial', 'overdue'])->count())
                ->description('Awaiting payment')
                ->icon('heroicon-o-banknotes'),
            Stat::make('Samples in lab', Sample::query()->whereIn('status', ['received', 'in_testing'])->count())
                ->description('Sample management')
                ->icon('heroicon-o-cube'),
        ];
    }
}
