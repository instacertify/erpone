<?php

namespace App\Filament\Resources\TestPlans;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\TestPlans\Pages\ManageTestPlans;
use App\Models\TestPlan;
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

class TestPlanResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = TestPlan::class;

    protected static ?string $erpModule = 'testing';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static string|UnitEnum|null $navigationGroup = 'Quality';

    protected static ?string $navigationLabel = 'Test Plans';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')->required()->unique(ignoreRecord: true),
                TextInput::make('name')->required(),
                Select::make('customer_id')->relationship('customer', 'name')->searchable()->preload(),
                Select::make('project_id')->relationship('project', 'name')->searchable()->preload(),
                Select::make('owner_id')->relationship('owner', 'name')->searchable()->preload(),
                Select::make('status')->options([
                    'draft' => 'Draft',
                    'active' => 'Active',
                    'completed' => 'Completed',
                    'archived' => 'Archived',
                ])->default('draft')->required(),
                DatePicker::make('planned_start'),
                DatePicker::make('planned_end'),
                Textarea::make('scope')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('customer.name'),
                TextColumn::make('project.name'),
                TextColumn::make('status')->badge(),
                TextColumn::make('planned_end')->date(),
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
            'index' => ManageTestPlans::route('/'),
        ];
    }
}
