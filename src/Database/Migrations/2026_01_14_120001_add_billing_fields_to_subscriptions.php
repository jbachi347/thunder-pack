<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Add billing cycle field
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly')->after('status');
            
            // Add next billing date (critical - used in code but missing)
            $table->timestamp('next_billing_date')->nullable()->after('ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['billing_cycle', 'next_billing_date']);
        });
    }
};
