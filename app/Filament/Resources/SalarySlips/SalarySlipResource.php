<?php

namespace App\Filament\Resources\SalarySlips;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\SalarySlips\Pages\ManageSalarySlips;
use App\Models\Employee;
use App\Models\SalarySlip;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SalarySlipResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = SalarySlip::class;

    protected static ?string $erpModule = 'hrms';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'HR';

    protected static ?string $navigationLabel = 'Salary Slips';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->relationship('employee', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (Employee $record): string => $record->full_name)
                    ->searchable(['first_name', 'last_name', 'employee_code'])
                    ->preload()
                    ->required(),
                TextInput::make('period')
                    ->placeholder('YYYY-MM')
                    ->regex('/^\d{4}-(0[1-9]|1[0-2])$/')
                    ->required()
                    ->maxLength(7),
                TextInput::make('gross')->numeric()->minValue(0)->default(0)->required(),
                TextInput::make('deductions')->numeric()->minValue(0)->default(0)->required(),
                TextInput::make('net')->numeric()->minValue(0)->default(0)->required(),
                Select::make('currency')->options([
                    'INR' => 'INR',
                    'USD' => 'USD',
                    'EUR' => 'EUR',
                ])->default('INR')->required(),
                FileUpload::make('file_path')
                    ->label('Salary slip')
                    ->disk('local')
                    ->directory('salary-slips')
                    ->downloadable(),
                Hidden::make('disk')->default('local'),
                DateTimePicker::make('published_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.full_name')->label('Employee')->searchable(['first_name', 'last_name']),
                TextColumn::make('period')->sortable(),
                TextColumn::make('gross')->money(fn (SalarySlip $record): string => $record->currency),
                TextColumn::make('deductions')->money(fn (SalarySlip $record): string => $record->currency),
                TextColumn::make('net')->money(fn (SalarySlip $record): string => $record->currency),
                TextColumn::make('published_at')->dateTime()->sortable(),
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
            'index' => ManageSalarySlips::route('/'),
        ];
    }
}
