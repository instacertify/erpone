<?php

namespace App\Filament\Resources\Leads;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\Leads\Pages\ManageLeads;
use App\Models\Lead;
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

class LeadResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = Lead::class;

    protected static ?string $erpModule = 'sales';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Leads';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->required()->maxLength(255),
                Select::make('customer_id')->relationship('customer', 'name')->searchable()->preload(),
                Select::make('owner_id')->relationship('owner', 'name')->searchable()->preload(),
                TextInput::make('source')->maxLength(100),
                Select::make('stage')->options([
                    'new' => 'New',
                    'qualified' => 'Qualified',
                    'proposal' => 'Proposal',
                    'negotiation' => 'Negotiation',
                    'won' => 'Won',
                    'lost' => 'Lost',
                ])->required()->default('new'),
                TextInput::make('estimated_value')->numeric()->prefix('₹'),
                TextInput::make('currency')->default('INR')->maxLength(3),
                DatePicker::make('expected_close_date'),
                TextInput::make('probability')->numeric()->minValue(0)->maxValue(100)->suffix('%'),
                Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('customer.name')->sortable(),
                TextColumn::make('stage')->badge(),
                TextColumn::make('estimated_value')->money(fn ($record) => $record->currency ?? 'INR'),
                TextColumn::make('owner.name')->label('Owner'),
                TextColumn::make('expected_close_date')->date(),
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
            'index' => ManageLeads::route('/'),
        ];
    }
}
