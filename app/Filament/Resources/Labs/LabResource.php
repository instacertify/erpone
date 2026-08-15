<?php

namespace App\Filament\Resources\Labs;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\Labs\Pages\ManageLabs;
use App\Models\Lab;
use App\Support\IndianStates;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class LabResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = Lab::class;

    protected static ?string $erpModule = 'testing';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static string|UnitEnum|null $navigationGroup = 'Libraries';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')->required()->unique(ignoreRecord: true)->maxLength(50),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('location')->maxLength(255),
                TextInput::make('city')->maxLength(100),
                TextInput::make('country')->default('India')->required()->live(onBlur: true),
                Select::make('state')
                    ->options(IndianStates::options())
                    ->searchable()
                    ->visible(fn (Get $get): bool => strcasecmp((string) $get('country'), 'India') === 0),
                TextInput::make('accreditation')->maxLength(255),
                TextInput::make('accreditation_number')->maxLength(255),
                TextInput::make('contact_email')->email()->maxLength(255),
                TextInput::make('contact_phone')->tel()->maxLength(50),
                Toggle::make('is_active')->default(true),
                Textarea::make('scope_summary')->rows(4)->columnSpanFull(),
                KeyValue::make('price_sheet')
                    ->keyLabel('Test / service')
                    ->valueLabel('Price')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('city')->searchable(),
                TextColumn::make('state'),
                TextColumn::make('accreditation')->searchable(),
                TextColumn::make('accreditation_number')->label('Accreditation no.'),
                TextColumn::make('documents_count')->counts('documents')->label('Documents'),
                IconColumn::make('is_active')->boolean(),
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
            'index' => ManageLabs::route('/'),
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
