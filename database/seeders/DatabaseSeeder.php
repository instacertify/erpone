<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Consultant;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Lab;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationTemplate;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Services\Settings\ErpSettings;
use App\Support\QrCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $qr = app(QrCodeService::class);

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

        $allOps = User::query()->updateOrCreate(
            ['email' => 'opslead@instacertify.com'],
            [
                'name' => 'All Ops Manager',
                'password' => Hash::make('Legal@123'),
                'job_title' => 'All Ops Manager',
                'is_active' => true,
                'role' => UserRole::AllOpsManager,
                'email_verified_at' => now(),
            ]
        );

        $sales = User::query()->updateOrCreate(
            ['email' => 'sales@instacertify.com'],
            [
                'name' => 'Sales Executive',
                'password' => Hash::make('Legal@123'),
                'job_title' => 'Sales Person',
                'is_active' => true,
                'role' => UserRole::Sales,
                'email_verified_at' => now(),
            ]
        );

        $operations = User::query()->updateOrCreate(
            ['email' => 'operations@instacertify.com'],
            [
                'name' => 'Operations Manager',
                'password' => Hash::make('Legal@123'),
                'job_title' => 'Operations Manager',
                'is_active' => true,
                'role' => UserRole::Operations,
                'email_verified_at' => now(),
            ]
        );

        app(ErpSettings::class)->seedDefaults();

        $opsDept = Department::query()->updateOrCreate(
            ['code' => 'OPS'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Operations',
                'manager_id' => $operations->id,
                'description' => 'Core operations department',
            ]
        );

        Employee::query()->updateOrCreate(
            ['employee_code' => 'IC-0001'],
            [
                'uuid' => (string) Str::uuid(),
                'user_id' => $admin->id,
                'department_id' => $opsDept->id,
                'first_name' => 'Nikhil',
                'last_name' => 'Tiwari',
                'email' => 'nikhil@instacertify.com',
                'job_title' => 'Super Administrator',
                'status' => 'active',
                'hired_at' => now()->subYears(2)->toDateString(),
                'salary' => 0,
                'currency' => 'INR',
                'joining_letter_qr' => $qr->makeToken('JL'),
            ]
        );

        $consultant = Consultant::query()->updateOrCreate(
            ['email' => 'partner@consult.test'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Rajesh Consulting Partner',
                'company' => 'RKP Advisors',
                'phone' => '9876500001',
                'is_active' => true,
            ]
        );

        $service = ServiceCatalog::query()->updateOrCreate(
            ['code' => 'BIS-CRS'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'BIS CRS Certification',
                'category' => 'certification',
                'description' => 'Bureau of Indian Standards Compulsory Registration Scheme support.',
                'default_timeline_days' => 90,
                'is_active' => true,
            ]
        );

        ServiceCatalog::query()->updateOrCreate(
            ['code' => 'LAB-TEST'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Product Safety Testing',
                'category' => 'testing',
                'description' => 'Accredited lab testing package.',
                'default_timeline_days' => 21,
                'is_active' => true,
            ]
        );

        $lab = Lab::query()->updateOrCreate(
            ['code' => 'LAB-NCR-01'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'NCR Accredited Test Lab',
                'location' => 'Okhla Phase II',
                'city' => 'New Delhi',
                'state' => 'DL',
                'country' => 'India',
                'accreditation' => 'NABL',
                'accreditation_number' => 'TC-12345',
                'scope_summary' => 'Electrical safety, EMC, environmental tests',
                'contact_email' => 'lab@example.test',
                'is_active' => true,
                'price_sheet' => [
                    ['test' => 'Dielectric strength', 'price' => 8500],
                    ['test' => 'Leakage current', 'price' => 4500],
                ],
            ]
        );

        $customer = Customer::query()->updateOrCreate(
            ['code' => 'CUST-001'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Acme Electronics Pvt Ltd',
                'legal_name' => 'Acme Electronics Private Limited',
                'email' => 'procurement@acme.test',
                'phone' => '9811111111',
                'industry' => 'Electronics',
                'company_size' => 'medium',
                'status' => 'active',
                'preferred_currency' => 'INR',
                'gstin' => '07AAAAA0000A1Z5',
                'state' => 'DL',
                'country' => 'India',
                'account_manager_id' => $sales->id,
                'notes' => 'Key account for BIS CRS renewals and testing.',
            ]
        );

        Lead::query()->updateOrCreate(
            ['title' => 'BIS CRS for LED drivers'],
            [
                'uuid' => (string) Str::uuid(),
                'person_name' => 'Anita Sharma',
                'company_name' => 'Acme Electronics Pvt Ltd',
                'request_type' => 'service',
                'company_size' => 'medium',
                'country' => 'India',
                'state' => 'DL',
                'contact_number' => '9811111111',
                'customer_id' => $customer->id,
                'owner_id' => $sales->id,
                'lead_source' => 'indiamart',
                'consultant_id' => null,
                'source' => 'IndiaMART',
                'stage' => 'proposal',
                'estimated_value' => 185000,
                'currency' => 'INR',
                'expected_timeline' => '8-10 weeks',
                'company_address' => 'Okhla Industrial Area, New Delhi',
                'gst_details' => '07AAAAA0000A1Z5',
                'notes' => 'Needs certification timeline and testing cost split.',
            ]
        );

        $template = QuotationTemplate::query()->updateOrCreate(
            ['name' => 'BIS CRS Standard Package'],
            [
                'uuid' => (string) Str::uuid(),
                'category' => 'service',
                'service_id' => $service->id,
                'created_by' => $sales->id,
                'currency' => 'INR',
                'certification_timeline' => 'Document review 2 weeks · Testing 3 weeks · Grant 4-6 weeks',
                'terms_and_conditions' => "1. Quote valid for 30 days.\n2. Government fees are passthrough.\n3. Consulting fees are payable to Instacertify.",
                'force_majeure' => 'Timelines may shift due to lab backlog, regulatory changes, or force majeure events.',
                'is_active' => true,
                'items_payload' => [
                    [
                        'description' => 'Consulting & documentation support',
                        'item_type' => 'consulting',
                        'pay_to' => 'us',
                        'counts_as_revenue' => true,
                        'currency' => 'INR',
                        'quantity' => 1,
                        'unit_price' => 75000,
                        'tax_rate' => 18,
                        'line_total' => 88500,
                    ],
                    [
                        'description' => 'BIS government fees (estimate)',
                        'item_type' => 'government_fee',
                        'pay_to' => 'government',
                        'counts_as_revenue' => false,
                        'currency' => 'INR',
                        'quantity' => 1,
                        'unit_price' => 25000,
                        'tax_rate' => 0,
                        'line_total' => 25000,
                    ],
                    [
                        'description' => 'Safety testing package',
                        'item_type' => 'testing',
                        'pay_to' => 'us',
                        'counts_as_revenue' => true,
                        'currency' => 'INR',
                        'quantity' => 1,
                        'samples_count' => 2,
                        'lab_id' => null,
                        'applicable_standard' => 'IS 15885',
                        'lab_accreditation' => 'NABL',
                        'testing_timeline' => '15-21 working days',
                        'tests_required' => 'Dielectric, leakage, abnormal operation',
                        'unit_price' => 45000,
                        'tax_rate' => 18,
                        'line_total' => 53100,
                    ],
                ],
            ]
        );

        $quote = Quotation::query()->updateOrCreate(
            ['number' => 'QT-2026-0001'],
            [
                'uuid' => (string) Str::uuid(),
                'customer_id' => $customer->id,
                'created_by' => $sales->id,
                'assigned_to' => $sales->id,
                'status' => 'sent',
                'category' => 'service',
                'service_id' => $service->id,
                'template_id' => $template->id,
                'quote_date' => now()->toDateString(),
                'valid_until' => now()->addDays(30)->toDateString(),
                'currency' => 'INR',
                'exchange_rate' => 1,
                'subtotal' => 145000,
                'tax_total' => 21600,
                'total' => 166600,
                'total_inr' => 166600,
                'share_token' => Str::random(40),
                'barcode' => $qr->makeToken('QTE'),
                'certification_timeline' => $template->certification_timeline,
                'terms_and_conditions' => $template->terms_and_conditions,
                'force_majeure' => $template->force_majeure,
                'shared_at' => now(),
            ]
        );

        if ($quote->items()->count() === 0) {
            foreach ($template->items_payload as $index => $item) {
                $item['lab_id'] = ($item['item_type'] ?? null) === 'testing' ? $lab->id : null;
                QuotationItem::query()->create(array_merge($item, [
                    'quotation_id' => $quote->id,
                    'sort_order' => $index,
                ]));
            }
        }

        $quote->recalculateRevenueTotals();

        Project::query()->updateOrCreate(
            ['code' => 'PRJ-ACME-BIS'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Acme BIS CRS rollout',
                'customer_id' => $customer->id,
                'manager_id' => $operations->id,
                'sales_owner_id' => $sales->id,
                'status' => 'active',
                'priority' => 'high',
                'progress' => 35,
                'color' => '#065175',
                'start_date' => now()->subWeeks(2)->toDateString(),
                'due_date' => now()->addWeeks(8)->toDateString(),
                'description' => 'End-to-end BIS CRS consulting and lab coordination.',
            ]
        );

        Project::query()->updateOrCreate(
            ['code' => 'PRJ-ACME-TEST'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Acme safety testing batch',
                'customer_id' => $customer->id,
                'manager_id' => $operations->id,
                'sales_owner_id' => $sales->id,
                'status' => 'in_progress',
                'priority' => 'medium',
                'progress' => 55,
                'color' => '#ec6820',
                'start_date' => now()->subWeek()->toDateString(),
                'due_date' => now()->addWeeks(3)->toDateString(),
            ]
        );

        Holiday::query()->updateOrCreate(
            ['date' => '2026-08-15', 'name' => 'Independence Day'],
            ['uuid' => (string) Str::uuid(), 'is_optional' => false, 'region' => 'India']
        );

        Holiday::query()->updateOrCreate(
            ['date' => '2026-10-02', 'name' => 'Gandhi Jayanti'],
            ['uuid' => (string) Str::uuid(), 'is_optional' => false, 'region' => 'India']
        );

        // Silence unused in some runs
        unset($allOps, $consultant);
    }
}
