<?php

namespace App\Filament\Resources\QuotationTemplates;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\QuotationTemplates\Pages\ManageQuotationTemplates;
use App\Models\QuotationTemplate;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Repeater;
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

class QuotationTemplateResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = QuotationTemplate::class;

    protected static ?string $erpModule = 'sales';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Quotation Templates';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(255),
                Select::make('category')->options([
                    'service' => 'Service',
                    'testing' => 'Testing',
                    'renewal' => 'Renewal',
                    'certificate' => 'Certificate',
                ])->default('service')->required(),
                Select::make('service_id')->relationship('service', 'name')->searchable()->preload(),
                Select::make('currency')->options([
                    'INR' => 'INR',
                    'USD' => 'USD',
                    'EUR' => 'EUR',
                ])->default('INR')->required(),
                TextInput::make('certification_timeline')->maxLength(255),
                Toggle::make('is_active')->default(true),
                Textarea::make('terms_and_conditions')->rows(5)->columnSpanFull(),
                Textarea::make('force_majeure')->rows(4)->columnSpanFull(),
                Repeater::make('items_payload')
                    ->label('Default line items')
                    ->schema([
                        TextInput::make('description')->required()->columnSpanFull(),
                        Select::make('item_type')->options([
                            'consulting' => 'Consulting',
                            'government_fee' => 'Government fee',
                            'testing' => 'Testing',
                            'lab' => 'Lab',
                            'other' => 'Other',
                        ])->default('consulting')->required(),
                        Select::make('pay_to')->options([
                            'us' => 'Us',
                            'government' => 'Government',
                            'lab' => 'Lab',
                        ])->default('us')->required(),
                        Toggle::make('counts_as_revenue')->default(true),
                        TextInput::make('quantity')->numeric()->default(1)->required(),
                        TextInput::make('unit_price')->numeric()->default(0)->required(),
                        TextInput::make('tax_rate')->numeric()->suffix('%')->default(18),
                        TextInput::make('line_total')->numeric()->default(0),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->cloneable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category')->badge(),
                TextColumn::make('service.name')->label('Service')->searchable(),
                TextColumn::make('currency')->badge(),
                TextColumn::make('certification_timeline')->label('Timeline')->toggleable(),
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
            'index' => ManageQuotationTemplates::route('/'),
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
