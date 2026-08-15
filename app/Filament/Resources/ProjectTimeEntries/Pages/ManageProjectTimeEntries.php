<?php

namespace App\Filament\Resources\ProjectTimeEntries\Pages;

use App\Filament\Resources\ProjectTimeEntries\ProjectTimeEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProjectTimeEntries extends ManageRecords
{
    protected static string $resource = ProjectTimeEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
