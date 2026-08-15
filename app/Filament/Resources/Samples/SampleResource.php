<?php

namespace App\Filament\Resources\Samples;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\Samples\Pages\ManageSamples;
use App\Models\Sample;
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

class SampleResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = Sample::class;

    protected static ?string $erpModule = 'samples';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'Quality';

    protected static ?string $navigationLabel = 'Samples';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sample_id')->required()->unique(ignoreRecord: true)->label('Sample ID'),
                TextInput::make('name')->required(),
                Select::make('customer_id')->relationship('customer', 'name')->searchable()->preload(),
                Select::make('project_id')->relationship('project', 'name')->searchable()->preload(),
                TextInput::make('type')->maxLength(100),
                Select::make('status')->options([
                    'received' => 'Received',
                    'in_testing' => 'In testing',
                    'completed' => 'Completed',
                    'disposed' => 'Disposed',
                    'returned' => 'Returned',
                ])->default('received')->required(),
                DatePicker::make('received_at'),
                DatePicker::make('due_at'),
                TextInput::make('storage_location')->maxLength(120),
                Select::make('custodian_id')->relationship('custodian', 'name')->searchable()->preload(),
                Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sample_id')->label('Sample ID')->searchable()->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('customer.name'),
                TextColumn::make('status')->badge(),
                TextColumn::make('received_at')->date(),
                TextColumn::make('due_at')->date(),
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
            'index' => ManageSamples::route('/'),
        ];
    }
}
