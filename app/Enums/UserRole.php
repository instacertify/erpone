<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Employee = 'employee';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Employee => 'Employee',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::SuperAdmin || $this === self::Admin;
    }

    public function canBulkExport(): bool
    {
        return $this === self::SuperAdmin || $this === self::Admin;
    }

    public function canManageSettings(): bool
    {
        return $this === self::SuperAdmin || $this === self::Admin;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role): array => [$role->value => $role->label()])
            ->all();
    }
}
