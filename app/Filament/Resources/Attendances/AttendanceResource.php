<?php

namespace App\Filament\Resources\Attendances;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\Attendances\Pages\ManageAttendances;
use App\Models\Attendance;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AttendanceResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = Attendance::class;

    protected static ?string $erpModule = 'hrms';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'HR';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->default(fn (): ?int => auth()->id())
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('work_date')->default(today())->required(),
                TimePicker::make('check_in')->seconds(false),
                TimePicker::make('check_out')->seconds(false),
                Select::make('status')->options([
                    'present' => 'Present',
                    'absent' => 'Absent',
                    'leave' => 'Leave',
                    'half_day' => 'Half day',
                    'remote' => 'Remote',
                ])->default('present')->required(),
                Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->searchable()->sortable(),
                TextColumn::make('work_date')->date()->sortable(),
                TextColumn::make('check_in')->time(),
                TextColumn::make('check_out')->time(),
                TextColumn::make('status')->badge(),
                TextColumn::make('notes')->limit(40)->toggleable(),
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
            'index' => ManageAttendances::route('/'),
        ];
    }
}
