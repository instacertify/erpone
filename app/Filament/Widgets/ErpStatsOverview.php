<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\Sample;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ErpStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = auth()->user();
        $quoteQuery = Quotation::query();
        $projectQuery = Project::query()->whereIn('status', ['planning', 'active', 'in_progress']);

        if ($user && ! $user->canViewEverything()) {
            if ($user->isSales()) {
                $quoteQuery->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)->orWhere('assigned_to', $user->id);
                });
                $projectQuery->where(function ($q) use ($user) {
                    $q->where('sales_owner_id', $user->id)->orWhere('manager_id', $user->id);
                });
            }
        }

        return [
            Stat::make('Open leads', Lead::query()->whereNotIn('stage', ['won', 'lost'])->count())
                ->description('CRM pipeline')
                ->descriptionIcon('heroicon-m-funnel')
                ->color('primary')
                ->chart([3, 5, 4, 8, 6, 9, 7]),
            Stat::make('My / open quotes', (clone $quoteQuery)->whereIn('status', ['draft', 'sent'])->count())
                ->description('Awaiting customer action')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning')
                ->chart([2, 3, 5, 4, 6, 5, 8]),
            Stat::make('Ongoing projects', (clone $projectQuery)->count())
                ->description('Active delivery work')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary')
                ->chart([4, 4, 5, 6, 7, 8, 9]),
            Stat::make('Customers', Customer::query()->count())
                ->description('Accounts on file')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),
            Stat::make('Receivables', Invoice::query()->whereIn('status', ['sent', 'partial', 'overdue'])->count())
                ->description('Open invoices')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
            Stat::make('Samples moving', Sample::query()->whereIn('tracking_status', ['received', 'dispatched', 'in_testing'])->count())
                ->description('Lab / testing flow')
                ->descriptionIcon('heroicon-m-beaker')
                ->color('primary'),
        ];
    }
}
