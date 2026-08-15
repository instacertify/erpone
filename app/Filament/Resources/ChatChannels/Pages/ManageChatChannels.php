<?php

namespace App\Filament\Resources\ChatChannels\Pages;

use App\Filament\Resources\ChatChannels\ChatChannelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageChatChannels extends ManageRecords
{
    protected static string $resource = ChatChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
