<?php

namespace App\Filament\Resources\Quotations\Pages;

use App\Filament\Resources\Quotations\QuotationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageQuotations extends ManageRecords
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by'] = auth()->id();

                    if (($data['currency'] ?? 'INR') === 'USD') {
                        $rate = (float) ($data['exchange_rate'] ?? app(\App\Services\Settings\ErpSettings::class)->get('usd_to_inr_rate', 83.5));
                        $data['exchange_rate'] = $rate;
                        $data['total_inr'] = round(((float) ($data['total'] ?? 0)) * $rate, 2);
                    } else {
                        $data['exchange_rate'] = 1;
                        $data['total_inr'] = $data['total'] ?? 0;
                    }

                    return $data;
                }),
        ];
    }
}
