<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE subscriptions MODIFY COLUMN status ENUM('trialing','active','past_due','canceled','paused') DEFAULT 'trialing'");
        }
        
        // PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TYPE subscription_status ADD VALUE IF NOT EXISTS 'paused'");
        }
        
        // SQLite - Recreate the check constraint
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support ENUM, uses CHECK constraints
            // Would need to recreate the table, but for development we can skip
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot remove ENUM values safely, would truncate data
        // Best practice is to leave the value in place
    }
};
