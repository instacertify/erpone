<?php

namespace App\Filament\Resources\Employees;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\Employees\Pages\ManageEmployees;
use App\Models\Employee;
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

class EmployeeResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = Employee::class;

    protected static ?string $erpModule = 'hrms';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'HR';

    protected static ?string $navigationLabel = 'Employees';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('employee_code')->required()->unique(ignoreRecord: true),
                TextInput::make('first_name')->required(),
                TextInput::make('last_name')->required(),
                TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                TextInput::make('phone')->tel(),
                TextInput::make('job_title'),
                Select::make('department_id')->relationship('department', 'name')->searchable()->preload(),
                Select::make('user_id')->relationship('user', 'name')->searchable()->preload(),
                Select::make('employment_type')->options([
                    'full_time' => 'Full time',
                    'part_time' => 'Part time',
                    'contract' => 'Contract',
                    'intern' => 'Intern',
                ])->default('full_time'),
                Select::make('status')->options([
                    'active' => 'Active',
                    'on_leave' => 'On leave',
                    'terminated' => 'Terminated',
                ])->default('active'),
                DatePicker::make('hired_at'),
                TextInput::make('salary')->numeric(),
                TextInput::make('currency')->default('USD')->maxLength(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_code')->searchable()->sortable(),
                TextColumn::make('first_name')->searchable(),
                TextColumn::make('last_name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('department.name'),
                TextColumn::make('status')->badge(),
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
            'index' => ManageEmployees::route('/'),
        ];
    }
}
