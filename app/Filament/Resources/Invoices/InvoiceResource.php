<?php

namespace App\Filament\Resources\Invoices;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\Invoices\Pages\ManageInvoices;
use App\Models\Invoice;
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

class InvoiceResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = Invoice::class;

    protected static ?string $erpModule = 'billing';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Invoices';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')->required()->unique(ignoreRecord: true),
                Select::make('customer_id')->relationship('customer', 'name')->required()->searchable()->preload(),
                Select::make('project_id')->relationship('project', 'name')->searchable()->preload(),
                Select::make('status')->options([
                    'draft' => 'Draft',
                    'sent' => 'Sent',
                    'partial' => 'Partially paid',
                    'paid' => 'Paid',
                    'void' => 'Void',
                    'overdue' => 'Overdue',
                ])->default('draft')->required(),
                DatePicker::make('issue_date')->required()->default(now()),
                DatePicker::make('due_date'),
                Select::make('currency')
                    ->options(app(\App\Services\Settings\ErpSettings::class)->currencyOptions())
                    ->default('INR')
                    ->required(),
                TextInput::make('exchange_rate')->numeric()->default(1),
                TextInput::make('subtotal')->numeric()->default(0),
                TextInput::make('tax_total')->numeric()->default(0),
                TextInput::make('total')->numeric()->default(0),
                TextInput::make('total_inr')->numeric()->default(0)->label('Total (INR)'),
                TextInput::make('amount_paid')->numeric()->default(0),
                TextInput::make('gstin')->label('Customer GSTIN')->maxLength(20),
                TextInput::make('place_of_supply'),
                Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('customer.name')->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('issue_date')->date(),
                TextColumn::make('due_date')->date(),
                TextColumn::make('currency')->badge(),
                TextColumn::make('total')->money(fn ($record) => $record->currency ?? 'INR'),
                TextColumn::make('amount_paid')->money(fn ($record) => $record->currency ?? 'INR'),
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
            'index' => ManageInvoices::route('/'),
        ];
    }
}
