<?php

namespace App\Filament\Resources\Quotations;

use App\Filament\Concerns\BelongsToErpModule;
use App\Filament\Resources\Quotations\Pages\ManageQuotations;
use App\Models\Quotation;
use App\Models\QuotationTemplate;
use App\Services\Settings\ErpSettings;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use UnitEnum;

class QuotationResource extends Resource
{
    use BelongsToErpModule;

    protected static ?string $model = Quotation::class;

    protected static ?string $erpModule = 'sales';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Quotations';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        $currencies = app(ErpSettings::class)->currencyOptions();

        return $schema
            ->components([
                TextInput::make('number')
                    ->default(fn (): string => 'QTN-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)))
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('customer_id')->relationship('customer', 'name')->required()->searchable()->preload(),
                Select::make('lead_id')->relationship('lead', 'title')->searchable()->preload(),
                Select::make('template_id')
                    ->label('Template')
                    ->relationship('template', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set, mixed $state): void {
                        $template = $state ? QuotationTemplate::find($state) : null;

                        if (! $template) {
                            return;
                        }

                        $set('category', $template->category);
                        $set('service_id', $template->service_id);
                        $set('currency', $template->currency);
                        $set('certification_timeline', $template->certification_timeline);
                        $set('terms_and_conditions', $template->terms_and_conditions);
                        $set('force_majeure', $template->force_majeure);

                        if (filled($template->items_payload)) {
                            $set('items', $template->items_payload);
                        }
                    })
                    ->helperText('Selecting a template copies its service, terms, timeline, currency, and line items.'),
                Select::make('status')->options([
                    'draft' => 'Draft',
                    'sent' => 'Sent',
                    'accepted' => 'Accepted',
                    'rejected' => 'Rejected',
                    'expired' => 'Expired',
                ])->default('draft')->required(),
                Select::make('category')
                    ->options([
                        'service' => 'Service',
                        'testing' => 'Testing',
                        'renewal' => 'Renewal',
                        'certificate' => 'Certificate',
                    ])
                    ->default('service')
                    ->required(),
                Select::make('service_id')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('assigned_to')
                    ->relationship('assignee', 'name')
                    ->default(fn (): ?int => auth()->id())
                    ->searchable()
                    ->preload(),
                Hidden::make('created_by')->default(fn (): ?int => auth()->id()),
                DatePicker::make('quote_date')->required()->default(now()),
                DatePicker::make('valid_until')->default(fn () => now()->addDays(30)),
                Select::make('currency')
                    ->options(array_intersect_key($currencies, array_flip(['INR', 'USD', 'EUR'])) + [
                        'INR' => 'INR',
                        'USD' => 'USD',
                        'EUR' => 'EUR',
                    ])
                    ->default('INR')
                    ->required()
                    ->helperText('Choose INR, USD, or EUR.'),
                TextInput::make('exchange_rate')->numeric()->default(1)->helperText('Rate used to calculate the INR total.'),
                TextInput::make('subtotal')->numeric()->default(0),
                TextInput::make('tax_total')->numeric()->default(0),
                TextInput::make('total')->numeric()->default(0),
                TextInput::make('total_inr')->numeric()->default(0)->label('Total (INR)'),
                TextInput::make('certification_timeline')->maxLength(255),
                Textarea::make('terms_and_conditions')->rows(5)->columnSpanFull(),
                Textarea::make('force_majeure')->rows(4)->columnSpanFull(),
                Textarea::make('notes')->columnSpanFull(),
                Repeater::make('items')
                    ->relationship()
                    ->orderColumn('sort_order')
                    ->schema([
                        TextInput::make('description')->required()->maxLength(255)->columnSpanFull(),
                        Select::make('item_type')
                            ->options([
                                'consulting' => 'Consulting',
                                'government_fee' => 'Government fee',
                                'testing' => 'Testing',
                                'lab' => 'Lab',
                                'other' => 'Other',
                            ])
                            ->default('consulting')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
                                'counts_as_revenue',
                                in_array($state, ['consulting', 'testing', 'lab'], true),
                            )),
                        Select::make('pay_to')
                            ->options([
                                'us' => 'Us',
                                'government' => 'Government',
                                'lab' => 'Lab',
                            ])
                            ->default('us')
                            ->required(),
                        Toggle::make('counts_as_revenue')->default(true),
                        TextInput::make('quantity')
                            ->numeric()
                            ->minValue(0.01)
                            ->default(1)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => $set(
                                'line_total',
                                round((float) $get('quantity') * (float) $get('unit_price') * (1 + ((float) $get('tax_rate') / 100)), 2),
                            )),
                        TextInput::make('unit_price')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => $set(
                                'line_total',
                                round((float) $get('quantity') * (float) $get('unit_price') * (1 + ((float) $get('tax_rate') / 100)), 2),
                            )),
                        TextInput::make('tax_rate')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->default(18)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => $set(
                                'line_total',
                                round((float) $get('quantity') * (float) $get('unit_price') * (1 + ((float) $get('tax_rate') / 100)), 2),
                            )),
                        TextInput::make('line_total')->numeric()->default(0)->readOnly(),
                        TextInput::make('samples_count')->numeric()->minValue(0),
                        Select::make('lab_id')->relationship('lab', 'name')->searchable()->preload(),
                        TextInput::make('applicable_standard')->maxLength(255),
                        TextInput::make('lab_accreditation')->maxLength(255),
                        TextInput::make('testing_timeline')->maxLength(255),
                        Textarea::make('tests_required')->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->defaultItems(1)
                    ->collapsible()
                    ->cloneable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('customer.name')->sortable()->searchable(),
                TextColumn::make('category')->badge(),
                TextColumn::make('service.name')->label('Service')->toggleable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('currency')->badge(),
                TextColumn::make('assignee.name')->label('Assigned to'),
                TextColumn::make('quote_date')->date(),
                TextColumn::make('total')->money(fn ($record) => $record->currency ?? 'INR'),
                TextColumn::make('total_inr')->money('INR')->label('INR total'),
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user || $user->canViewEverything() || ! $user->isSales()) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($user): void {
            $query
                ->where('created_by', $user->getKey())
                ->orWhere('assigned_to', $user->getKey());
        });
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageQuotations::route('/'),
        ];
    }
}
