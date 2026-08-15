<?php

namespace App\Filament\Resources\TestPlans\Pages;

use App\Filament\Resources\TestPlans\TestPlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTestPlans extends ManageRecords
{
    protected static string $resource = TestPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
