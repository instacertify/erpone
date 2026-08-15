<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class GreetingWidget extends Widget
{
    protected string $view = 'filament.widgets.greeting-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $hour = (int) now('Asia/Kolkata')->format('G');
        $greeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        $user = auth()->user();

        return [
            'greeting' => $greeting,
            'name' => $user?->name ?? 'Team',
            'role' => $user?->role?->label() ?? 'Team member',
            'date' => now('Asia/Kolkata')->format('l, d M Y'),
            'time' => now('Asia/Kolkata')->format('h:i A'),
        ];
    }
}
