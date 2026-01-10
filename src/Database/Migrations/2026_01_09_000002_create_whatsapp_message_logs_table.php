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
        Schema::create('whatsapp_message_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_whatsapp_phone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone_number', 20); // Redundant but useful for logs after phone deletion
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'failed', 'error'])->default('pending');
            $table->text('response')->nullable();
            $table->string('notification_type')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            // Indexes for efficient queries
            $table->index(['tenant_id', 'status', 'created_at']);
            $table->index(['tenant_whatsapp_phone_id', 'created_at']);
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_message_logs');
    }
};
