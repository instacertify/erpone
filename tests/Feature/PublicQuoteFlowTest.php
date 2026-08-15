<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Quotation;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicQuoteFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_and_accept_shared_quote(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $customer = Customer::query()->create([
            'uuid' => (string) Str::uuid(),
            'code' => 'CUST-T1',
            'name' => 'Test Co',
            'status' => 'active',
            'preferred_currency' => 'INR',
        ]);

        $token = Str::random(32);

        $quote = Quotation::query()->create([
            'uuid' => (string) Str::uuid(),
            'number' => 'QT-TEST-1',
            'customer_id' => $customer->id,
            'created_by' => $sales->id,
            'assigned_to' => $sales->id,
            'status' => 'sent',
            'category' => 'service',
            'quote_date' => now()->toDateString(),
            'currency' => 'INR',
            'share_token' => $token,
            'barcode' => 'QTE-TEST',
            'total' => 1000,
            'total_inr' => 1000,
        ]);

        $this->get('/quote/'.$token)->assertOk()->assertSee('QT-TEST-1');

        $this->post('/quote/'.$token.'/accept')->assertRedirect();

        $this->assertSame('accepted', $quote->fresh()->status);
        $this->assertNotNull($quote->fresh()->accepted_at);
    }
}
