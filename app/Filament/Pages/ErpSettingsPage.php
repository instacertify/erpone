<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Services\Export\BulkExcelExporter;
use App\Services\Gst\GstApiClient;
use App\Services\Settings\ErpSettings;
use App\Support\Modules\ModuleRegistry;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class ErpSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'ERP Settings';

    protected static ?string $title = 'ERP Settings';

    protected static ?string $slug = 'erp-settings';

    protected static ?int $navigationSort = 100;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        $role = $user->role instanceof UserRole
            ? $user->role
            : UserRole::tryFrom((string) $user->role);

        return $role?->canManageSettings() ?? false;
    }

    public function mount(ErpSettings $settings): void
    {
        abort_unless(static::canAccess(), 403);

        $data = [
            'company_name' => $settings->get('company_name'),
            'company_email' => $settings->get('company_email'),
            'company_phone' => $settings->get('company_phone'),
            'company_address' => $settings->get('company_address'),
            'company_website' => $settings->get('company_website'),
            'primary_currency' => $settings->get('primary_currency', 'INR'),
            'quote_currencies' => $settings->quoteCurrencies(),
            'usd_to_inr_rate' => $settings->get('usd_to_inr_rate', 83.50),
            'timezone' => $settings->get('timezone', 'Asia/Kolkata'),
            'gst_enabled' => (bool) $settings->get('gst_enabled', false),
            'gst_api_provider' => $settings->get('gst_api_provider'),
            'gst_api_base_url' => $settings->get('gst_api_base_url'),
            'gst_api_key' => $settings->get('gst_api_key'),
            'gst_api_secret' => $settings->get('gst_api_secret'),
            'gst_company_gstin' => $settings->get('gst_company_gstin'),
            'bulk_export_enabled' => (bool) $settings->get('bulk_export_enabled', true),
            'export_datasets' => array_keys(app(BulkExcelExporter::class)->datasets()),
        ];

        foreach (app(ModuleRegistry::class)->all() as $key => $module) {
            $data["module_{$key}"] = (bool) ($module['enabled'] ?? true);
        }

        $this->form->fill($data);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        $moduleToggles = [];

        foreach (app(ModuleRegistry::class)->all() as $key => $module) {
            $moduleToggles[] = Toggle::make("module_{$key}")
                ->label($module['label'] ?? ucfirst($key))
                ->helperText($module['description'] ?? null);
        }

        return $schema
            ->components([
                Section::make('Company')
                    ->description('Organisation profile shown across the ERP.')
                    ->schema([
                        TextInput::make('company_name')->required(),
                        TextInput::make('company_email')->email()->required(),
                        TextInput::make('company_phone')->tel(),
                        TextInput::make('company_website')->url(),
                        TextInput::make('company_address')->columnSpanFull(),
                        TextInput::make('timezone')->required(),
                    ])
                    ->columns(2),

                Section::make('Currency')
                    ->description('Primary books currency is INR. Customer quotes can also be issued in USD.')
                    ->schema([
                        TextInput::make('primary_currency')->disabled()->dehydrated(),
                        Select::make('quote_currencies')
                            ->multiple()
                            ->options([
                                'INR' => 'INR — Indian Rupee',
                                'USD' => 'USD — US Dollar',
                            ])
                            ->required(),
                        TextInput::make('usd_to_inr_rate')
                            ->numeric()
                            ->label('USD → INR rate')
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('GST API')
                    ->description('Attach your GST e-invoice / verification provider API credentials.')
                    ->schema([
                        Toggle::make('gst_enabled')->label('Enable GST integration'),
                        TextInput::make('gst_api_provider')->label('Provider name'),
                        TextInput::make('gst_api_base_url')->label('API base URL')->url(),
                        TextInput::make('gst_api_key')->label('API key')->password()->revealable(),
                        TextInput::make('gst_api_secret')->label('API secret')->password()->revealable(),
                        TextInput::make('gst_company_gstin')->label('Company GSTIN')->maxLength(20),
                    ])
                    ->columns(2),

                Section::make('Modules')
                    ->description('Toggle ERP modules for your team.')
                    ->schema($moduleToggles)
                    ->columns(2),

                Section::make('Bulk Excel export')
                    ->description('Admin-only download of operational data.')
                    ->schema([
                        Toggle::make('bulk_export_enabled')->label('Allow admin bulk Excel download'),
                        Select::make('export_datasets')
                            ->label('Datasets to include')
                            ->multiple()
                            ->options(
                                collect(app(BulkExcelExporter::class)->datasets())
                                    ->mapWithKeys(fn ($class, $key) => [$key => str_replace('_', ' ', ucfirst($key))])
                                    ->all()
                            ),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Save settings')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testGst')
                ->label('Test GST API')
                ->color('warning')
                ->action('testGstApi'),

            Action::make('optimize')
                ->label('Optimize app')
                ->color('gray')
                ->requiresConfirmation()
                ->action('optimizeApp'),

            Action::make('clearCache')
                ->label('Clear caches')
                ->color('gray')
                ->action('clearCaches'),

            Action::make('exportExcel')
                ->label('Download Excel')
                ->color('warning')
                ->visible(fn (): bool => $this->canExport())
                ->action('exportExcel'),
        ];
    }

    public function save(ErpSettings $settings): void
    {
        abort_unless(static::canAccess(), 403);

        $data = $this->form->getState();

        $settings->set('company_name', $data['company_name'] ?? 'Instacertify');
        $settings->set('company_email', $data['company_email'] ?? '');
        $settings->set('company_phone', $data['company_phone'] ?? '');
        $settings->set('company_address', $data['company_address'] ?? '');
        $settings->set('company_website', $data['company_website'] ?? '');
        $settings->set('timezone', $data['timezone'] ?? 'Asia/Kolkata');
        $settings->set('primary_currency', 'INR');
        $settings->set('quote_currencies', $data['quote_currencies'] ?? ['INR', 'USD']);
        $settings->set('usd_to_inr_rate', (float) ($data['usd_to_inr_rate'] ?? 83.5));
        $settings->set('gst_enabled', (bool) ($data['gst_enabled'] ?? false), 'gst');
        $settings->set('gst_api_provider', $data['gst_api_provider'] ?? '', 'gst');
        $settings->set('gst_api_base_url', $data['gst_api_base_url'] ?? '', 'gst');
        $settings->set('gst_api_key', $data['gst_api_key'] ?? '', 'gst', encrypt: true);
        $settings->set('gst_api_secret', $data['gst_api_secret'] ?? '', 'gst', encrypt: true);
        $settings->set('gst_company_gstin', $data['gst_company_gstin'] ?? '', 'gst');
        $settings->set('bulk_export_enabled', (bool) ($data['bulk_export_enabled'] ?? true));

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    public function testGstApi(GstApiClient $client): void
    {
        abort_unless(static::canAccess(), 403);

        $this->save(app(ErpSettings::class));
        $result = $client->testConnection();

        Notification::make()
            ->title($result['ok'] ? 'GST API OK' : 'GST API failed')
            ->body($result['message'])
            ->{$result['ok'] ? 'success' : 'danger'}()
            ->send();
    }

    public function optimizeApp(ErpSettings $settings): void
    {
        abort_unless(static::canAccess(), 403);

        $ran = $settings->optimizeApplication();

        Notification::make()
            ->title('Application optimized')
            ->body('Ran: '.implode(', ', $ran))
            ->success()
            ->send();
    }

    public function clearCaches(ErpSettings $settings): void
    {
        abort_unless(static::canAccess(), 403);

        $ran = $settings->clearCaches();

        Notification::make()
            ->title('Caches cleared')
            ->body('Ran: '.implode(', ', $ran))
            ->success()
            ->send();
    }

    public function exportExcel(BulkExcelExporter $exporter, ErpSettings $settings)
    {
        abort_unless($this->canExport(), 403);

        if (! $settings->get('bulk_export_enabled', true)) {
            Notification::make()
                ->title('Bulk export disabled')
                ->danger()
                ->send();

            return null;
        }

        $keys = $this->form->getState()['export_datasets'] ?? [];

        return $exporter->download(is_array($keys) ? $keys : []);
    }

    protected function canExport(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        $role = $user->role instanceof UserRole
            ? $user->role
            : UserRole::tryFrom((string) $user->role);

        return $role?->canBulkExport() ?? false;
    }
}
