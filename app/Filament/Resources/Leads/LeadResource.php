<?php

namespace App\Filament\Resources\Leads;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\Leads\Pages\ManageLeads;
use App\Models\Contact;
use App\Models\Lead;
use App\Support\IndianStates;
use App\Support\LeadSources;
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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class LeadResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = Lead::class;

    protected static ?string $erpModule = 'sales';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Leads';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Lead title')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Company – requested service'),
                TextInput::make('person_name')->required()->maxLength(255),
                TextInput::make('company_name')->required()->maxLength(255),
                Select::make('request_type')
                    ->options([
                        'service' => 'Service',
                        'testing' => 'Testing',
                    ])
                    ->default('service')
                    ->required(),
                Select::make('company_size')
                    ->options([
                        'micro' => 'Micro',
                        'small' => 'Small',
                        'medium' => 'Medium',
                        'large' => 'Large',
                    ]),
                TextInput::make('country')
                    ->default('India')
                    ->required()
                    ->live(onBlur: true)
                    ->maxLength(100),
                Select::make('state')
                    ->options(IndianStates::options())
                    ->searchable()
                    ->visible(fn (Get $get): bool => strcasecmp((string) $get('country'), 'India') === 0),
                TextInput::make('contact_number')->tel()->maxLength(50),
                Select::make('contact_id')
                    ->label('Contact / email')
                    ->relationship('contact', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (Contact $record): string => trim($record->full_name.($record->email ? " — {$record->email}" : '')))
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->preload()
                    ->helperText('Lead email is stored on the selected contact.'),
                Select::make('customer_id')->relationship('customer', 'name')->searchable()->preload(),
                Select::make('owner_id')
                    ->relationship('owner', 'name')
                    ->default(fn (): ?int => auth()->id())
                    ->searchable()
                    ->preload(),
                Select::make('lead_source')
                    ->options(LeadSources::options())
                    ->default('google')
                    ->required()
                    ->live(),
                Select::make('consultant_id')
                    ->relationship('consultant', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (Get $get): bool => $get('lead_source') === 'consultant'),
                Select::make('stage')->options([
                    'new' => 'New',
                    'qualified' => 'Qualified',
                    'proposal' => 'Proposal',
                    'negotiation' => 'Negotiation',
                    'won' => 'Won',
                    'lost' => 'Lost',
                ])->required()->default('new'),
                TextInput::make('estimated_value')->numeric()->prefix('₹'),
                Select::make('currency')->options([
                    'INR' => 'INR',
                    'USD' => 'USD',
                    'EUR' => 'EUR',
                ])->default('INR')->required(),
                DatePicker::make('expected_close_date'),
                TextInput::make('expected_timeline')->maxLength(255)->placeholder('For example: 4–6 weeks'),
                TextInput::make('probability')->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(10),
                Textarea::make('company_address')->columnSpanFull(),
                TextInput::make('gst_details')->label('GST details')->maxLength(255),
                Textarea::make('notes')->label('Remarks / notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')->label('Company')->searchable()->sortable(),
                TextColumn::make('person_name')->label('Contact person')->searchable(),
                TextColumn::make('request_type')->badge(),
                TextColumn::make('lead_source')->label('Source')->badge(),
                TextColumn::make('stage')->badge(),
                TextColumn::make('estimated_value')->money(fn ($record) => $record->currency ?? 'INR'),
                TextColumn::make('owner.name')->label('Owner'),
                TextColumn::make('expected_timeline')->toggleable(),
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
            'index' => ManageLeads::route('/'),
        ];
    }
}
