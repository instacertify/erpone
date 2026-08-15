<?php

namespace App\Filament\Resources\CalendarEvents;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\CalendarEvents\Pages\ManageCalendarEvents;
use App\Models\CalendarEvent;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CalendarEventResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = CalendarEvent::class;

    protected static ?string $erpModule = 'calendar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Calendar';

    protected static ?string $navigationLabel = 'Events';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->required()->maxLength(255),
                Select::make('organizer_id')->relationship('organizer', 'name')->searchable()->preload(),
                Select::make('customer_id')->relationship('customer', 'name')->searchable()->preload(),
                Select::make('project_id')->relationship('project', 'name')->searchable()->preload(),
                DateTimePicker::make('starts_at')->required(),
                DateTimePicker::make('ends_at'),
                Toggle::make('all_day')->default(false),
                TextInput::make('location')->maxLength(255),
                TextInput::make('color')->maxLength(32),
                Textarea::make('description')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('starts_at')->dateTime()->sortable(),
                TextColumn::make('ends_at')->dateTime(),
                TextColumn::make('organizer.name'),
                IconColumn::make('all_day')->boolean(),
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
            'index' => ManageCalendarEvents::route('/'),
        ];
    }
}
