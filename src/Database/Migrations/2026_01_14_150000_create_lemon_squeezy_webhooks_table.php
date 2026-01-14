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
        Schema::create('lemon_squeezy_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('event_name')->index();
            $table->string('event_id')->nullable();
            $table->string('signature')->nullable();
            $table->json('payload');
            $table->timestamp('received_at')->useCurrent();
            $table->timestamps();

            $table->index(['event_name', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lemon_squeezy_webhooks');
    }
};
