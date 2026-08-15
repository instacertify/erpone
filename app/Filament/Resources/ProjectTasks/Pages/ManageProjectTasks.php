<?php

namespace App\Filament\Resources\ProjectTasks\Pages;

use App\Filament\Resources\ProjectTasks\ProjectTaskResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProjectTasks extends ManageRecords
{
    protected static string $resource = ProjectTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
