<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('preferred_currency', 3)->default('INR')->after('tax_id');
            $table->string('gstin', 20)->nullable()->after('preferred_currency');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['preferred_currency', 'gstin']);
        });
    }
};
