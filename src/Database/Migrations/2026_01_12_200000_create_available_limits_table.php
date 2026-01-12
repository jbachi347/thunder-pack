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
        Schema::create('available_limits', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // max_installations, staff_limit, etc.
            $table->string('name'); // "Máximo de Instalaciones"
            $table->string('category')->default('general'); // general, storage, communication, etc.
            $table->text('description')->nullable();
            $table->bigInteger('default_value')->default(0);
            $table->string('unit')->nullable(); // bytes, count, MB, etc.
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('available_limits');
    }
};