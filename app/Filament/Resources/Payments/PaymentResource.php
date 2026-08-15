<?php

namespace App\Filament\Resources\Payments;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\Payments\Pages\ManagePayments;
use App\Models\Payment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PaymentResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = Payment::class;

    protected static ?string $erpModule = 'billing';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Payments';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('invoice_id')->relationship('invoice', 'number')->required()->searchable()->preload(),
                TextInput::make('amount')->numeric()->required(),
                TextInput::make('currency')->default('INR')->maxLength(3),
                Select::make('method')->options([
                    'bank_transfer' => 'Bank transfer',
                    'card' => 'Card',
                    'upi' => 'UPI',
                    'cash' => 'Cash',
                    'cheque' => 'Cheque',
                    'other' => 'Other',
                ])->default('bank_transfer'),
                TextInput::make('reference')->maxLength(120),
                DateTimePicker::make('paid_at')->required()->default(now()),
                Select::make('received_by')->relationship('receiver', 'name')->searchable()->preload(),
                Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice.number')->label('Invoice')->sortable(),
                TextColumn::make('amount')->money(fn ($record) => $record->currency ?? 'INR'),
                TextColumn::make('method')->badge(),
                TextColumn::make('reference'),
                TextColumn::make('paid_at')->dateTime()->sortable(),
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
            'index' => ManagePayments::route('/'),
        ];
    }
}
