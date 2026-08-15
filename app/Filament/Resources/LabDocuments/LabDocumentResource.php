<?php

namespace App\Filament\Resources\LabDocuments;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\LabDocuments\Pages\ManageLabDocuments;
use App\Models\LabDocument;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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

class LabDocumentResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = LabDocument::class;

    protected static ?string $erpModule = 'testing';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Libraries';

    protected static ?string $navigationLabel = 'Lab Documents';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('lab_id')->relationship('lab', 'name')->required()->searchable()->preload(),
                TextInput::make('title')->required()->maxLength(255),
                Select::make('type')->options([
                    'scope_sheet' => 'Scope sheet',
                    'accreditation' => 'Accreditation',
                    'price_list' => 'Price list',
                    'other' => 'Other',
                ])->default('other')->required(),
                FileUpload::make('file_path')
                    ->label('Document')
                    ->disk('local')
                    ->directory('lab-documents')
                    ->downloadable()
                    ->required(),
                Hidden::make('disk')->default('local'),
                Hidden::make('uploaded_by')->default(fn (): ?int => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('lab.name')->searchable()->sortable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('uploader.name')->label('Uploaded by'),
                TextColumn::make('created_at')->dateTime()->sortable(),
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
            'index' => ManageLabDocuments::route('/'),
        ];
    }
}
