<?php

namespace App\Filament\Resources\LabDocuments\Pages;

use App\Filament\Resources\LabDocuments\LabDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLabDocuments extends ManageRecords
{
    protected static string $resource = LabDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
