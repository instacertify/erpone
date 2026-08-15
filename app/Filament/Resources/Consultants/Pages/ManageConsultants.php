<?php

namespace App\Filament\Resources\Consultants\Pages;

use App\Filament\Resources\Consultants\ConsultantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageConsultants extends ManageRecords
{
    protected static string $resource = ConsultantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
