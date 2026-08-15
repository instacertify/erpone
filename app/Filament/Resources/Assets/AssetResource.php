<?php

namespace App\Filament\Resources\Assets;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\Assets\Pages\ManageAssets;
use App\Models\Asset;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AssetResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = Asset::class;

    protected static ?string $erpModule = 'storage';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'Assets';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('asset_code')
                    ->label('Asset code')
                    ->disabled()
                    ->placeholder('Generated automatically'),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('category')->maxLength(100),
                TextInput::make('value')->numeric()->minValue(0),
                Select::make('currency')->options([
                    'INR' => 'INR',
                    'USD' => 'USD',
                    'EUR' => 'EUR',
                ])->default('INR')->required(),
                Select::make('assigned_to')->relationship('assignee', 'name')->searchable()->preload(),
                Hidden::make('registered_by')->default(fn (): ?int => auth()->id()),
                DatePicker::make('acquired_on'),
                Select::make('status')->options([
                    'available' => 'Available',
                    'assigned' => 'Assigned',
                    'maintenance' => 'Maintenance',
                    'retired' => 'Retired',
                    'lost' => 'Lost',
                ])->default('available')->required(),
                Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asset_code')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category')->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('assignee.name')->label('Assigned to'),
                TextColumn::make('value')->money(fn (Asset $record): string => $record->currency),
                TextColumn::make('acquired_on')->date()->sortable(),
                TextColumn::make('registeredBy.name')->label('Registered by')->toggleable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAssets::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
