<?php

namespace App\Filament\Resources\Samples;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\Samples\Pages\ManageSamples;
use App\Models\Sample;
use App\Support\QrCodeService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class SampleResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = Sample::class;

    protected static ?string $erpModule = 'samples';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'Quality';

    protected static ?string $navigationLabel = 'Samples';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sample_id')->required()->unique(ignoreRecord: true)->label('Sample ID'),
                TextInput::make('name')->required(),
                Select::make('customer_id')->relationship('customer', 'name')->searchable()->preload(),
                Select::make('project_id')->relationship('project', 'name')->searchable()->preload(),
                Select::make('quotation_id')->relationship('quotation', 'number')->searchable()->preload(),
                Select::make('lab_id')->relationship('lab', 'name')->searchable()->preload(),
                TextInput::make('type')->maxLength(100),
                Select::make('tracking_status')->label('Tracking status')->options([
                    'pending' => 'Pending receipt',
                    'received' => 'Sample received',
                    'dispatched' => 'Dispatched to lab',
                    'in_testing' => 'Testing in process',
                    'report_available' => 'Report available',
                    'report_uploaded' => 'Report uploaded',
                    'shared' => 'Shared with customer',
                ])->default('pending')->required()->live(),
                Select::make('status')->options([
                    'received' => 'Received',
                    'in_testing' => 'In testing',
                    'completed' => 'Completed',
                    'disposed' => 'Disposed',
                    'returned' => 'Returned',
                ])->default('received'),
                DatePicker::make('received_at'),
                DateTimePicker::make('dispatched_at'),
                DateTimePicker::make('testing_started_at'),
                DateTimePicker::make('report_available_at'),
                DateTimePicker::make('report_uploaded_at'),
                FileUpload::make('report_path')->label('Test report')->directory('sample-reports')->acceptedFileTypes([
                    'application/pdf', 'image/*',
                ]),
                DatePicker::make('due_at'),
                TextInput::make('storage_location')->maxLength(120),
                Select::make('custodian_id')->relationship('custodian', 'name')->searchable()->preload(),
                TextInput::make('qr_code')->disabled()->dehydrated(false),
                Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sample_id')->label('Sample ID')->searchable()->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('customer.name'),
                TextColumn::make('lab.name')->toggleable(),
                TextColumn::make('tracking_status')->badge()->color(fn (string $state): string => match ($state) {
                    'received' => 'primary',
                    'dispatched', 'in_testing' => 'warning',
                    'report_available', 'report_uploaded', 'shared' => 'success',
                    default => 'gray',
                }),
                TextColumn::make('qr_code')->toggleable(),
                TextColumn::make('received_at')->date(),
            ])
            ->recordActions([
                Action::make('markReceived')
                    ->label('Mark received + QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('primary')
                    ->action(function (Sample $record): void {
                        $qr = app(QrCodeService::class);
                        $token = $record->share_token ?: Str::random(40);
                        $code = $record->qr_code ?: $qr->makeToken('SMP');

                        $record->update([
                            'tracking_status' => 'received',
                            'status' => 'received',
                            'received_at' => $record->received_at ?: now(),
                            'qr_code' => $code,
                            'share_token' => $token,
                        ]);

                        Notification::make()
                            ->title('Sample marked received')
                            ->body('Tracking link: '.url('/sample/'.$token))
                            ->success()
                            ->send();
                    }),
                Action::make('shareReport')
                    ->label('Share link')
                    ->icon('heroicon-o-link')
                    ->color('warning')
                    ->action(function (Sample $record): void {
                        $token = $record->share_token ?: Str::random(40);
                        $record->update([
                            'share_token' => $token,
                            'tracking_status' => $record->report_path ? 'shared' : $record->tracking_status,
                        ]);

                        Notification::make()
                            ->title('Customer sample link')
                            ->body(url('/sample/'.$token))
                            ->success()
                            ->send();
                    }),
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
            'index' => ManageSamples::route('/'),
        ];
    }
}
