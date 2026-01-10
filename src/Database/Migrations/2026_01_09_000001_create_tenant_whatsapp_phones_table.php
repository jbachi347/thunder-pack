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
        Schema::create('tenant_whatsapp_phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('phone_number', 20);
            $table->string('instance_name')->nullable();
            $table->boolean('is_default')->default(false);
            $table->json('notification_types')->nullable(); // Array of notification types enabled
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Prevent duplicate phone numbers per tenant
            $table->unique(['tenant_id', 'phone_number']);
            
            // Index for quick lookups
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_whatsapp_phones');
    }
};
