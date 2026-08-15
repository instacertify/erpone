<?php

namespace App\Services\Settings;

use App\Models\Setting;
use App\Support\Modules\ModuleRegistry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class ErpSettings
{
    public const PRIMARY_CURRENCY = 'INR';

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return [
            'company_name' => 'Instacertify',
            'company_email' => 'nikhil@instacertify.com',
            'company_phone' => '',
            'company_address' => '',
            'company_website' => 'https://instacertify.in',
            'primary_currency' => self::PRIMARY_CURRENCY,
            'quote_currencies' => ['INR', 'USD'],
            'usd_to_inr_rate' => 83.50,
            'timezone' => 'Asia/Kolkata',
            'date_format' => 'd-m-Y',
            'gst_enabled' => false,
            'gst_api_provider' => '',
            'gst_api_base_url' => '',
            'gst_api_key' => '',
            'gst_api_secret' => '',
            'gst_company_gstin' => '',
            'bulk_export_enabled' => true,
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $defaults = $this->defaults();

        return Setting::getValue($key, $default ?? ($defaults[$key] ?? null));
    }

    public function set(string $key, mixed $value, string $group = 'general', bool $encrypt = false): void
    {
        $type = match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'json',
            default => 'string',
        };

        Setting::setValue($key, $value, $group, $type, $encrypt);
    }

    /**
     * @return list<string>
     */
    public function quoteCurrencies(): array
    {
        $currencies = $this->get('quote_currencies', ['INR', 'USD']);

        if (! is_array($currencies)) {
            $currencies = ['INR', 'USD'];
        }

        if (! in_array(self::PRIMARY_CURRENCY, $currencies, true)) {
            array_unshift($currencies, self::PRIMARY_CURRENCY);
        }

        return array_values(array_unique($currencies));
    }

    /**
     * @return array<string, string>
     */
    public function currencyOptions(): array
    {
        return collect($this->quoteCurrencies())
            ->mapWithKeys(fn (string $code): array => [$code => $code])
            ->all();
    }

    public function optimizeApplication(): array
    {
        $commands = [
            'config:cache',
            'route:cache',
            'view:cache',
            'event:cache',
            'optimize',
        ];

        $ran = [];

        foreach ($commands as $command) {
            Artisan::call($command);
            $ran[] = $command;
        }

        Cache::flush();

        return $ran;
    }

    public function clearCaches(): array
    {
        $commands = [
            'optimize:clear',
            'config:clear',
            'route:clear',
            'view:clear',
            'cache:clear',
        ];

        $ran = [];

        foreach ($commands as $command) {
            Artisan::call($command);
            $ran[] = $command;
        }

        return $ran;
    }

    public function seedDefaults(): void
    {
        foreach ($this->defaults() as $key => $value) {
            if (Setting::query()->where('key', $key)->exists()) {
                continue;
            }

            $group = str_starts_with($key, 'gst_') ? 'gst' : 'general';
            $encrypt = in_array($key, ['gst_api_key', 'gst_api_secret'], true);

            $this->set($key, $value, $group, $encrypt);
        }
    }

    public function syncModulesFromConfig(): void
    {
        /** @var ModuleRegistry $registry */
        $registry = app(ModuleRegistry::class);

        foreach ($registry->all() as $key => $module) {
            $this->set("module_{$key}_enabled", (bool) ($module['enabled'] ?? true), 'modules');
        }
    }
}
