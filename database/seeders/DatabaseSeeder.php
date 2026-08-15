<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Department;
use App\Models\User;
use App\Services\Settings\ErpSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'nikhil@instacertify.com'],
            [
                'name' => 'Nikhil Tiwari',
                'password' => Hash::make('Legal@123'),
                'job_title' => 'Super Administrator',
                'is_active' => true,
                'role' => UserRole::SuperAdmin,
                'email_verified_at' => now(),
            ]
        );

        app(ErpSettings::class)->seedDefaults();

        Department::query()->updateOrCreate(
            ['code' => 'OPS'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Operations',
                'manager_id' => $admin->id,
                'description' => 'Core operations department',
            ]
        );

        Customer::query()->updateOrCreate(
            ['code' => 'CUST-001'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Demo Customer',
                'email' => 'demo@customer.test',
                'status' => 'active',
                'preferred_currency' => 'INR',
                'account_manager_id' => $admin->id,
                'notes' => 'Seeded demo customer for Instacertify ERP.',
            ]
        );
    }
}
