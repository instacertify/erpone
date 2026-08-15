<?php

namespace App\Filament\Resources\ProjectTasks;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\ProjectTasks\Pages\ManageProjectTasks;
use App\Models\ProjectTask;
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

class ProjectTaskResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = ProjectTask::class;

    protected static ?string $erpModule = 'projects';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Projects';

    protected static ?string $navigationLabel = 'Tasks';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')->relationship('project', 'name')->required()->searchable()->preload(),
                TextInput::make('title')->required()->maxLength(255),
                Select::make('assignee_id')->relationship('assignee', 'name')->searchable()->preload(),
                Select::make('status')->options([
                    'todo' => 'To do',
                    'in_progress' => 'In progress',
                    'blocked' => 'Blocked',
                    'done' => 'Done',
                ])->default('todo')->required(),
                Select::make('priority')->options([
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                ])->default('medium'),
                DatePicker::make('due_date'),
                TextInput::make('estimate_hours')->numeric(),
                Textarea::make('description')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project.name')->sortable(),
                TextColumn::make('title')->searchable(),
                TextColumn::make('assignee.name'),
                TextColumn::make('status')->badge(),
                TextColumn::make('priority')->badge(),
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
            'index' => ManageProjectTasks::route('/'),
        ];
    }
}
