<?php

namespace App\Filament\Resources\Projects;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\Projects\Pages\ManageProjects;
use App\Models\Project;
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

class ProjectResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = Project::class;

    protected static ?string $erpModule = 'projects';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static string|UnitEnum|null $navigationGroup = 'Projects';

    protected static ?string $navigationLabel = 'Projects';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')->required()->unique(ignoreRecord: true)->maxLength(50),
                TextInput::make('name')->required()->maxLength(255),
                Select::make('customer_id')->relationship('customer', 'name')->searchable()->preload(),
                Select::make('manager_id')->relationship('manager', 'name')->searchable()->preload(),
                Select::make('status')->options([
                    'planning' => 'Planning',
                    'active' => 'Active',
                    'on_hold' => 'On hold',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ])->required()->default('planning'),
                Select::make('priority')->options([
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                    'critical' => 'Critical',
                ])->default('medium'),
                DatePicker::make('start_date'),
                DatePicker::make('due_date'),
                TextInput::make('progress')->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(0),
                Textarea::make('description')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('customer.name'),
                TextColumn::make('status')->badge(),
                TextColumn::make('priority')->badge(),
                TextColumn::make('progress')->suffix('%'),
                TextColumn::make('due_date')->date(),
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
            'index' => ManageProjects::route('/'),
        ];
    }
}
