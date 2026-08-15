<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\ProjectTask;
use App\Models\Quotation;
use Filament\Widgets\Widget;

class PendingTasksWidget extends Widget
{
    protected string $view = 'filament.widgets.pending-tasks-widget';

    protected int|string|array $columnSpan = [
        'md' => 4,
        'xl' => 4,
    ];

    protected static ?int $sort = 4;

    /**
     * @return array{items: list<array{label: string, meta: string, href: string, tone: string}>}
     */
    protected function getViewData(): array
    {
        $user = auth()->user();
        $items = [];

        $quotes = Quotation::query()
            ->whereIn('status', ['draft', 'sent'])
            ->when($user && ! $user->canViewEverything(), function ($q) use ($user) {
                $q->where(function ($inner) use ($user) {
                    $inner->where('created_by', $user->id)->orWhere('assigned_to', $user->id);
                });
            })
            ->latest()
            ->limit(4)
            ->get();

        foreach ($quotes as $quote) {
            $items[] = [
                'label' => 'Quote '.$quote->number,
                'meta' => ucfirst($quote->status).' · '.($quote->customer?->name ?? 'Customer'),
                'href' => url('/admin/quotations'),
                'tone' => 'orange',
            ];
        }

        $tasks = ProjectTask::query()
            ->whereIn('status', ['todo', 'doing', 'in_progress'])
            ->when($user && ! $user->canViewEverything(), fn ($q) => $q->where('assignee_id', $user?->id))
            ->latest()
            ->limit(4)
            ->get();

        foreach ($tasks as $task) {
            $items[] = [
                'label' => $task->title,
                'meta' => 'Task · '.ucfirst(str_replace('_', ' ', $task->status)),
                'href' => url('/admin/project-tasks'),
                'tone' => 'blue',
            ];
        }

        $leads = Lead::query()
            ->whereNotIn('stage', ['won', 'lost'])
            ->when($user && $user->isSales(), fn ($q) => $q->where('owner_id', $user->id))
            ->latest()
            ->limit(3)
            ->get();

        foreach ($leads as $lead) {
            $items[] = [
                'label' => $lead->company_name ?: $lead->title,
                'meta' => 'Lead · '.ucfirst($lead->stage),
                'href' => url('/admin/leads'),
                'tone' => 'blue',
            ];
        }

        return ['items' => array_slice($items, 0, 8)];
    }
}
