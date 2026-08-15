<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class OngoingProjectsWidget extends Widget
{
    protected string $view = 'filament.widgets.ongoing-projects-widget';

    protected int|string|array $columnSpan = [
        'md' => 8,
        'xl' => 8,
    ];

    protected static ?int $sort = 3;

    /**
     * @return array{projects: Collection<int, Project>}
     */
    protected function getViewData(): array
    {
        $user = auth()->user();
        $query = Project::query()
            ->with(['customer', 'manager'])
            ->whereIn('status', ['planning', 'active', 'in_progress'])
            ->latest()
            ->limit(8);

        if ($user && ! $user->canViewEverything()) {
            if ($user->isSales()) {
                $query->where(function ($q) use ($user) {
                    $q->where('sales_owner_id', $user->id)->orWhere('manager_id', $user->id);
                });
            } elseif ($user->role?->value === 'operations') {
                $query->where('manager_id', $user->id);
            }
        }

        return [
            'projects' => $query->get(),
        ];
    }
}
