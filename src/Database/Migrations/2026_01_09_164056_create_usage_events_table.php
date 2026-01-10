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
        Schema::create('usage_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('resource_type')->index(); // 'api_calls', 'clients', 'storage', etc.
            $table->decimal('amount', 20, 4)->default(1); // quantity consumed
            $table->string('action')->nullable(); // 'create', 'delete', 'update', etc.
            $table->json('metadata')->nullable(); // additional context
            $table->timestamp('created_at')->index();
            
            $table->index(['tenant_id', 'resource_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_events');
    }
};
