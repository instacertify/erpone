<?php

namespace App\Filament\Resources\ChatChannels;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\ChatChannels\Pages\ManageChatChannels;
use App\Models\ChatChannel;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ChatChannelResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = ChatChannel::class;

    protected static ?string $erpModule = 'chat';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Collaboration';

    protected static ?string $navigationLabel = 'Channels';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(100),
                TextInput::make('slug')->maxLength(120)->unique(ignoreRecord: true),
                Select::make('type')->options([
                    'public' => 'Public',
                    'private' => 'Private',
                    'direct' => 'Direct',
                ])->default('public')->required(),
                Select::make('created_by')->relationship('creator', 'name')->searchable()->preload(),
                Textarea::make('description')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('slug'),
                TextColumn::make('type')->badge(),
                TextColumn::make('creator.name')->label('Created by'),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageChatChannels::route('/'),
        ];
    }
}
