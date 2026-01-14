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
        Schema::table('plans', function (Blueprint $table) {
            // Lemon Squeezy variant IDs for monthly and yearly billing
            $table->string('lemon_monthly_variant_id')->nullable()->after('currency');
            $table->string('lemon_yearly_variant_id')->nullable()->after('lemon_monthly_variant_id');
            
            // Yearly pricing
            $table->unsignedInteger('yearly_price_cents')->nullable()->after('monthly_price_cents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'lemon_monthly_variant_id',
                'lemon_yearly_variant_id',
                'yearly_price_cents',
            ]);
        });
    }
};
