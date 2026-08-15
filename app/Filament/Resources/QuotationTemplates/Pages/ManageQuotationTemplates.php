<?php

namespace App\Filament\Resources\QuotationTemplates\Pages;

use App\Filament\Resources\QuotationTemplates\QuotationTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageQuotationTemplates extends ManageRecords
{
    protected static string $resource = QuotationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by'] = auth()->id();

                    return $data;
                }),
        ];
    }
}
