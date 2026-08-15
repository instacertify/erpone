<?php

namespace App\Filament\Resources\StoredFiles;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\StoredFiles\Pages\ManageStoredFiles;
use App\Models\StoredFile;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class StoredFileResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = StoredFile::class;

    protected static ?string $erpModule = 'storage';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCloudArrowUp;

    protected static string|UnitEnum|null $navigationGroup = 'Storage';

    protected static ?string $navigationLabel = 'Files';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('original_name')->required()->maxLength(255),
                TextInput::make('path')->required()->maxLength(500),
                TextInput::make('disk')->default('s3')->required(),
                TextInput::make('mime_type')->maxLength(120),
                TextInput::make('size_bytes')->numeric()->default(0),
                Select::make('visibility')->options([
                    'private' => 'Private',
                    'public' => 'Public',
                ])->default('private'),
                Select::make('uploaded_by')->relationship('uploader', 'name')->searchable()->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('original_name')->searchable(),
                TextColumn::make('disk'),
                TextColumn::make('mime_type'),
                TextColumn::make('size_bytes')->label('Size'),
                TextColumn::make('visibility')->badge(),
                TextColumn::make('uploader.name')->label('Uploaded by'),
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
            'index' => ManageStoredFiles::route('/'),
        ];
    }
}
