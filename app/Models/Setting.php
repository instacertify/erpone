<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'is_encrypted',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("erp.setting.{$key}", 300, function () use ($key) {
            return static::query()->where('key', $key)->first();
        });

        if (! $setting) {
            return $default;
        }

        $value = $setting->is_encrypted && filled($setting->value)
            ? Crypt::decryptString($setting->value)
            : $setting->value;

        return match ($setting->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json', 'array' => json_decode((string) $value, true) ?? $default,
            default => $value ?? $default,
        };
    }

    public static function setValue(
        string $key,
        mixed $value,
        string $group = 'general',
        string $type = 'string',
        bool $encrypt = false,
        ?string $description = null,
    ): self {
        if (is_array($value)) {
            $type = 'json';
            $store = json_encode($value);
        } elseif (is_bool($value)) {
            $type = 'boolean';
            $store = $value ? '1' : '0';
        } else {
            $store = $value === null ? null : (string) $value;
        }

        if ($encrypt && filled($store)) {
            $store = Crypt::encryptString($store);
        }

        $setting = static::query()->updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => $store,
                'type' => $type,
                'is_encrypted' => $encrypt,
                'description' => $description,
            ]
        );

        Cache::forget("erp.setting.{$key}");

        return $setting;
    }
}
