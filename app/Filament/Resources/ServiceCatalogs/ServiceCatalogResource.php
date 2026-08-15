<?php

namespace App\Filament\Resources\ServiceCatalogs;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\ServiceCatalogs\Pages\ManageServiceCatalogs;
use App\Models\ServiceCatalog;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ServiceCatalogResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = ServiceCatalog::class;

    protected static ?string $erpModule = 'sales';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Services';

    protected static ?string $modelLabel = 'Service';

    protected static ?string $pluralModelLabel = 'Services';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')->required()->unique(ignoreRecord: true)->maxLength(50),
                TextInput::make('name')->required()->maxLength(255),
                Select::make('category')->options([
                    'certification' => 'Certification',
                    'testing' => 'Testing',
                    'renewal' => 'Renewal',
                    'other' => 'Other',
                ])->default('certification')->required(),
                TextInput::make('default_timeline_days')
                    ->label('Default timeline (days)')
                    ->numeric()
                    ->minValue(1),
                Toggle::make('is_active')->default(true),
                Textarea::make('description')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category')->badge(),
                TextColumn::make('default_timeline_days')->label('Timeline')->suffix(' days'),
                TextColumn::make('quotationTemplates_count')->counts('quotationTemplates')->label('Templates'),
                TextColumn::make('quotations_count')->counts('quotations')->label('Quotes'),
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
            'index' => ManageServiceCatalogs::route('/'),
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
