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
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();

            $table->string('provider'); // stripe | paddle | paguelo_facil
            $table->string('event_type'); // invoice.paid, subscription.canceled, payment.failed
            $table->string('provider_event_id')->unique();

            $table->integer('amount_cents')->nullable();
            $table->string('currency')->nullable();

            $table->enum('status', ['success','failed','pending'])->nullable();

            $table->json('payload'); // respuesta cruda del proveedor

            $table->timestamps();

            $table->index(['provider', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_events');
    }
};
