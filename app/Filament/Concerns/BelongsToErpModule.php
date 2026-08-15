<?php

namespace App\Filament\Concerns;

use App\Support\Modules\ModuleRegistry;

trait BelongsToErpModule
{
    public static function getErpModuleKey(): ?string
    {
        return static::$erpModule ?? null;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $key = static::getErpModuleKey();

        if ($key === null) {
            return true;
        }

        return app(ModuleRegistry::class)->isEnabled($key);
    }

    public static function canAccess(): bool
    {
        return static::shouldRegisterNavigation();
    }
}
