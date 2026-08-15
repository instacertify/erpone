<?php

namespace App\Filament\Resources\ProjectTimeEntries;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\ProjectTimeEntries\Pages\ManageProjectTimeEntries;
use App\Models\ProjectTimeEntry;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ProjectTimeEntryResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = ProjectTimeEntry::class;

    protected static ?string $erpModule = 'projects';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Projects';

    protected static ?string $navigationLabel = 'Time Entries';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')->relationship('project', 'name')->required()->searchable()->preload(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->default(fn (): ?int => auth()->id())
                    ->required()
                    ->searchable()
                    ->preload(),
                DatePicker::make('work_date')->default(today())->required(),
                TextInput::make('hours')
                    ->numeric()
                    ->minValue(0.25)
                    ->maxValue(24)
                    ->step(0.25)
                    ->suffix('hours')
                    ->required(),
                Textarea::make('description')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project.name')->searchable()->sortable(),
                TextColumn::make('user.name')->searchable()->sortable(),
                TextColumn::make('work_date')->date()->sortable(),
                TextColumn::make('hours')->numeric(decimalPlaces: 2)->suffix(' h')->sortable(),
                TextColumn::make('description')->limit(50)->toggleable(),
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
            'index' => ManageProjectTimeEntries::route('/'),
        ];
    }
}
