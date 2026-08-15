<?php

namespace App\Filament\Resources\Quotations;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\Quotations\Pages\ManageQuotations;
use App\Models\Quotation;
use App\Services\Settings\ErpSettings;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class QuotationResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = Quotation::class;

    protected static ?string $erpModule = 'sales';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Quotations';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        $currencies = app(ErpSettings::class)->currencyOptions();

        return $schema
            ->components([
                TextInput::make('number')->required()->unique(ignoreRecord: true),
                Select::make('customer_id')->relationship('customer', 'name')->required()->searchable()->preload(),
                Select::make('lead_id')->relationship('lead', 'title')->searchable()->preload(),
                Select::make('status')->options([
                    'draft' => 'Draft',
                    'sent' => 'Sent',
                    'accepted' => 'Accepted',
                    'rejected' => 'Rejected',
                    'expired' => 'Expired',
                ])->default('draft')->required(),
                DatePicker::make('quote_date')->required()->default(now()),
                DatePicker::make('valid_until'),
                Select::make('currency')
                    ->options($currencies)
                    ->default('INR')
                    ->required()
                    ->helperText('Primary currency is INR. USD quotes are supported for international customers.'),
                TextInput::make('exchange_rate')->numeric()->default(1)->helperText('USD→INR rate when quoting in USD'),
                TextInput::make('subtotal')->numeric()->default(0),
                TextInput::make('tax_total')->numeric()->default(0),
                TextInput::make('total')->numeric()->default(0),
                TextInput::make('total_inr')->numeric()->default(0)->label('Total (INR)'),
                Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('customer.name')->sortable()->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('currency')->badge(),
                TextColumn::make('quote_date')->date(),
                TextColumn::make('total')->money(fn ($record) => $record->currency ?? 'INR'),
                TextColumn::make('total_inr')->money('INR')->label('INR total'),
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
            'index' => ManageQuotations::route('/'),
        ];
    }
}
