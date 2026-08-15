<?php

namespace App\Filament\Resources\CustomerRecords\Pages;

use App\Filament\Resources\CustomerRecords\CustomerRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerRecords extends ManageRecords
{
    protected static string $resource = CustomerRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
