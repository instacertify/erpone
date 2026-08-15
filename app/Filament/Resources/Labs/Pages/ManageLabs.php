<?php

namespace App\Filament\Resources\Labs\Pages;

use App\Filament\Resources\Labs\LabResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLabs extends ManageRecords
{
    protected static string $resource = LabResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
