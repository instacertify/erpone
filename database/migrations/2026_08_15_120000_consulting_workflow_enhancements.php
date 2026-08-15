<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category', 32)->default('certification'); // certification|testing|renewal|other
            $table->text('description')->nullable();
            $table->unsignedInteger('default_timeline_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('labs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('India');
            $table->string('accreditation')->nullable();
            $table->string('accreditation_number')->nullable();
            $table->text('scope_summary')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('price_sheet')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lab_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('lab_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type', 32)->default('other'); // scope_sheet|accreditation|price_list|other
            $table->string('file_path');
            $table->string('disk')->default('local');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->string('person_name')->nullable()->after('title');
            $table->string('company_name')->nullable()->after('person_name');
            $table->string('request_type', 32)->default('service')->after('company_name'); // service|testing
            $table->string('company_size', 32)->nullable()->after('request_type'); // micro|small|medium|large
            $table->string('country')->default('India')->after('company_size');
            $table->string('state')->nullable()->after('country');
            $table->string('contact_number', 50)->nullable()->after('state');
            $table->string('lead_source', 64)->nullable()->after('source');
            $table->foreignId('consultant_id')->nullable()->after('lead_source')->constrained('consultants')->nullOnDelete();
            $table->string('expected_timeline')->nullable()->after('expected_close_date');
            $table->string('company_address')->nullable()->after('notes');
            $table->string('gst_details')->nullable()->after('company_address');
        });

        Schema::create('quotation_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('category', 32)->default('service'); // service|testing|renewal|certificate
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('currency', 3)->default('INR');
            $table->text('terms_and_conditions')->nullable();
            $table->text('force_majeure')->nullable();
            $table->string('certification_timeline')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('items_payload')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->string('category', 32)->default('service')->after('status');
            $table->foreignId('service_id')->nullable()->after('category')->constrained()->nullOnDelete();
            $table->foreignId('template_id')->nullable()->after('service_id')->constrained('quotation_templates')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->string('share_token', 64)->nullable()->unique()->after('uuid');
            $table->string('barcode', 64)->nullable()->unique()->after('share_token');
            $table->string('certification_timeline')->nullable()->after('notes');
            $table->text('terms_and_conditions')->nullable()->after('certification_timeline');
            $table->text('force_majeure')->nullable()->after('terms_and_conditions');
            $table->text('customer_remarks')->nullable()->after('force_majeure');
            $table->timestamp('shared_at')->nullable()->after('customer_remarks');
            $table->timestamp('accepted_at')->nullable()->after('shared_at');
            $table->timestamp('rejected_at')->nullable()->after('accepted_at');
            $table->foreignId('project_id')->nullable()->after('lead_id')->constrained()->nullOnDelete();
            $table->decimal('consulting_revenue', 15, 2)->default(0)->after('total_inr');
            $table->decimal('lab_revenue', 15, 2)->default(0)->after('consulting_revenue');
            $table->decimal('passthrough_total', 15, 2)->default(0)->after('lab_revenue');
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->string('item_type', 32)->default('consulting')->after('description');
            // consulting|government_fee|testing|lab|other
            $table->string('pay_to', 32)->default('us')->after('item_type');
            // us|government|lab
            $table->boolean('counts_as_revenue')->default(true)->after('pay_to');
            $table->string('currency', 3)->default('INR')->after('counts_as_revenue');
            $table->unsignedInteger('samples_count')->nullable()->after('quantity');
            $table->foreignId('lab_id')->nullable()->after('samples_count')->constrained('labs')->nullOnDelete();
            $table->string('applicable_standard')->nullable()->after('lab_id');
            $table->string('lab_accreditation')->nullable()->after('applicable_standard');
            $table->string('testing_timeline')->nullable()->after('lab_accreditation');
            $table->text('tests_required')->nullable()->after('testing_timeline');
        });

        Schema::table('samples', function (Blueprint $table) {
            $table->foreignId('quotation_id')->nullable()->after('project_id')->constrained()->nullOnDelete();
            $table->foreignId('lab_id')->nullable()->after('quotation_id')->constrained('labs')->nullOnDelete();
            $table->string('qr_code', 64)->nullable()->unique()->after('sample_id');
            $table->string('share_token', 64)->nullable()->unique()->after('qr_code');
            $table->timestamp('dispatched_at')->nullable()->after('received_at');
            $table->timestamp('testing_started_at')->nullable()->after('dispatched_at');
            $table->timestamp('report_available_at')->nullable()->after('testing_started_at');
            $table->timestamp('report_uploaded_at')->nullable()->after('report_available_at');
            $table->string('report_path')->nullable()->after('report_uploaded_at');
            $table->string('tracking_status', 32)->default('pending')->after('status');
            // pending|received|dispatched|in_testing|report_available|report_uploaded|shared
        });

        Schema::create('customer_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 32)->default('document');
            // document|progress|credential|commitment|incident|deliverable
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_mime')->nullable();
            $table->string('disk')->default('local');
            $table->boolean('is_sensitive')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('document_checklists', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('share_token', 64)->nullable()->unique();
            $table->boolean('includes_test_request_form')->default(false);
            $table->string('status', 32)->default('open');
            $table->json('items')->nullable(); // [{key,label,required,uploaded}]
            $table->timestamps();
        });

        Schema::create('checklist_uploads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('document_checklist_id')->constrained()->cascadeOnDelete();
            $table->string('item_key');
            $table->string('original_name')->nullable();
            $table->string('file_path');
            $table->string('disk')->default('local');
            $table->string('uploaded_by_name')->nullable();
            $table->string('uploaded_by_email')->nullable();
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->decimal('value', 15, 2)->nullable();
            $table->string('currency', 3)->default('INR');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('acquired_on')->nullable();
            $table->string('status', 32)->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->date('date');
            $table->boolean('is_optional')->default(false);
            $table->string('region')->nullable();
            $table->timestamps();
        });

        Schema::create('salary_slips', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('period', 7); // YYYY-MM
            $table->decimal('gross', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('net', 15, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->string('file_path')->nullable();
            $table->string('disk')->default('local');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('status', 32)->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'work_date']);
        });

        Schema::create('project_time_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');
            $table->decimal('hours', 5, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('quotation_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->foreignId('sales_owner_id')->nullable()->after('manager_id')->constrained('users')->nullOnDelete();
            $table->string('color', 16)->nullable()->after('priority');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('joining_letter_qr', 64)->nullable()->after('employee_code');
            $table->string('joining_letter_path')->nullable()->after('joining_letter_qr');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('company_size', 32)->nullable()->after('industry');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['company_size']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['joining_letter_qr', 'joining_letter_path']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quotation_id');
            $table->dropConstrainedForeignId('sales_owner_id');
            $table->dropColumn('color');
        });

        Schema::dropIfExists('project_time_entries');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('salary_slips');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('checklist_uploads');
        Schema::dropIfExists('document_checklists');
        Schema::dropIfExists('customer_records');

        Schema::table('samples', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quotation_id');
            $table->dropConstrainedForeignId('lab_id');
            $table->dropColumn([
                'qr_code', 'share_token', 'dispatched_at', 'testing_started_at',
                'report_available_at', 'report_uploaded_at', 'report_path', 'tracking_status',
            ]);
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lab_id');
            $table->dropColumn([
                'item_type', 'pay_to', 'counts_as_revenue', 'currency', 'samples_count',
                'applicable_standard', 'lab_accreditation', 'testing_timeline', 'tests_required',
            ]);
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_id');
            $table->dropConstrainedForeignId('template_id');
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn([
                'category', 'share_token', 'barcode', 'certification_timeline',
                'terms_and_conditions', 'force_majeure', 'customer_remarks',
                'shared_at', 'accepted_at', 'rejected_at',
                'consulting_revenue', 'lab_revenue', 'passthrough_total',
            ]);
        });

        Schema::dropIfExists('quotation_templates');

        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('consultant_id');
            $table->dropColumn([
                'person_name', 'company_name', 'request_type', 'company_size', 'country', 'state',
                'contact_number', 'lead_source', 'expected_timeline', 'company_address', 'gst_details',
            ]);
        });

        Schema::dropIfExists('lab_documents');
        Schema::dropIfExists('labs');
        Schema::dropIfExists('services');
        Schema::dropIfExists('consultants');
    }
};
