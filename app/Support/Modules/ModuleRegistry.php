<?php

namespace App\Support\Modules;

class ModuleRegistry
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return config('erp.modules', []);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function enabled(): array
    {
        return array_filter(
            $this->all(),
            fn (array $module): bool => (bool) ($module['enabled'] ?? false)
        );
    }

    public function isEnabled(string $key): bool
    {
        return (bool) (config("erp.modules.{$key}.enabled") ?? false);
    }

    public function navigationGroup(string $key): ?string
    {
        return config("erp.modules.{$key}.navigation_group");
    }
}
