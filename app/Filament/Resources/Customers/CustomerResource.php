<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\Customers\Pages\ManageCustomers;
use App\Models\Customer;
use App\Services\Settings\ErpSettings;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CustomerResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = Customer::class;

    protected static ?string $erpModule = 'crm';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string|UnitEnum|null $navigationGroup = 'CRM';

    protected static ?string $navigationLabel = 'Customers';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')->required()->maxLength(50)->unique(ignoreRecord: true),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('legal_name')->maxLength(255),
                TextInput::make('email')->email()->maxLength(255),
                TextInput::make('phone')->tel()->maxLength(50),
                TextInput::make('website')->url()->maxLength(255),
                TextInput::make('industry')->maxLength(100),
                Select::make('company_size')
                    ->options([
                        'micro' => 'Micro',
                        'small' => 'Small',
                        'medium' => 'Medium',
                        'large' => 'Large',
                    ]),
                Select::make('status')->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'prospect' => 'Prospect',
                ])->required()->default('active'),
                Select::make('preferred_currency')
                    ->options(app(ErpSettings::class)->currencyOptions())
                    ->default('INR')
                    ->required()
                    ->helperText('INR is primary. Choose USD for international quotes.'),
                TextInput::make('gstin')->label('GSTIN')->maxLength(20),
                Select::make('account_manager_id')->relationship('accountManager', 'name')->searchable()->preload(),
                Textarea::make('notes')
                    ->helperText('Documents, progress, credentials, commitments, incidents, and deliverables are managed in CRM → Customer Records.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('company_size')->label('Size')->badge(),
                TextColumn::make('preferred_currency')->label('Currency')->badge(),
                TextColumn::make('gstin')->toggleable(),
                TextColumn::make('records_count')->counts('records')->label('Records'),
                TextColumn::make('status')->badge(),
                TextColumn::make('accountManager.name')->label('Account manager'),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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
            'index' => ManageCustomers::route('/'),
        ];
    }
}
