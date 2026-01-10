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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();

            // Opcional: branding
            $table->string('brand_name')->nullable();
            $table->string('brand_logo_path')->nullable();
            $table->string('brand_primary_color')->nullable();

            // Control de costos (S3)
            $table->unsignedBigInteger('storage_quota_bytes')->default(5 * 1024 * 1024 * 1024); // 5GB
            $table->unsignedBigInteger('storage_used_bytes')->default(0);

            // Flexible
            $table->json('data')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
