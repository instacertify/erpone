<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case AllOpsManager = 'all_ops_manager';
    case Sales = 'sales';
    case Operations = 'operations';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::AllOpsManager => 'All Ops Manager',
            self::Sales => 'Sales Person',
            self::Operations => 'Operations Manager',
        };
    }

    public function isAdmin(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin], true);
    }

    public function canViewEverything(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin, self::AllOpsManager], true);
    }

    public function canBulkExport(): bool
    {
        return $this->isAdmin();
    }

    public function canManageSettings(): bool
    {
        return $this->isAdmin();
    }

    public function canAuthorizeOperations(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin, self::AllOpsManager], true);
    }

    public function canManageQuotes(): bool
    {
        return in_array($this, [
            self::SuperAdmin,
            self::Admin,
            self::AllOpsManager,
            self::Sales,
        ], true);
    }

    public function canManageProjects(): bool
    {
        return in_array($this, [
            self::SuperAdmin,
            self::Admin,
            self::AllOpsManager,
            self::Operations,
            self::Sales,
        ], true);
    }

    public function canEnterHours(): bool
    {
        return in_array($this, [
            self::SuperAdmin,
            self::Admin,
            self::AllOpsManager,
            self::Operations,
        ], true);
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
