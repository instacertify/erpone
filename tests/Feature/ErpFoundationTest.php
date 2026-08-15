<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Settings\ErpSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErpFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_is_seeded_with_expected_credentials(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'nikhil@instacertify.com')->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->is_active);
        $this->assertSame(UserRole::SuperAdmin, $admin->role);
        $this->assertTrue(password_verify('Legal@123', $admin->password));
    }

    public function test_erp_settings_defaults_use_inr_primary_currency(): void
    {
        $settings = app(ErpSettings::class);
        $settings->seedDefaults();

        $this->assertSame('INR', $settings->get('primary_currency'));
        $this->assertContains('USD', $settings->quoteCurrencies());
        $this->assertContains('INR', $settings->quoteCurrencies());
    }

    public function test_admin_panel_login_page_is_reachable(): void
    {
        $response = $this->get('/admin/login');

        $response->assertOk();
    }

    public function test_only_admins_can_access_settings_page(): void
    {
        $employee = User::factory()->create(['role' => UserRole::Employee]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($employee)
            ->get('/admin/erp-settings')
            ->assertForbidden();

        $this->actingAs($admin)
            ->get('/admin/erp-settings')
            ->assertOk();
    }
}
