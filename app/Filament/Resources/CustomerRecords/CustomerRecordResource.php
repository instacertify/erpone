<?php

namespace App\Filament\Resources\CustomerRecords;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\CustomerRecords\Pages\ManageCustomerRecords;
use App\Models\CustomerRecord;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CustomerRecordResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = CustomerRecord::class;

    protected static ?string $erpModule = 'crm';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolderOpen;

    protected static string|UnitEnum|null $navigationGroup = 'CRM';

    protected static ?string $navigationLabel = 'Customer Records';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')->relationship('customer', 'name')->required()->searchable()->preload(),
                Select::make('project_id')->relationship('project', 'name')->searchable()->preload(),
                Select::make('type')->options([
                    'document' => 'Document',
                    'progress' => 'Progress',
                    'credential' => 'Credential',
                    'commitment' => 'Commitment',
                    'incident' => 'Incident',
                    'deliverable' => 'Deliverable',
                ])->default('document')->required(),
                TextInput::make('title')->required()->maxLength(255),
                Textarea::make('body')->label('Details')->rows(5)->columnSpanFull(),
                FileUpload::make('file_path')
                    ->label('Attachment')
                    ->disk('local')
                    ->directory('customer-records')
                    ->downloadable()
                    ->columnSpanFull(),
                Toggle::make('is_sensitive')->default(false),
                Hidden::make('disk')->default('local'),
                Hidden::make('created_by')->default(fn (): ?int => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('customer.name')->searchable()->sortable(),
                TextColumn::make('project.name')->toggleable(),
                TextColumn::make('type')->badge(),
                IconColumn::make('is_sensitive')->boolean(),
                TextColumn::make('creator.name')->label('Created by'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomerRecords::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
