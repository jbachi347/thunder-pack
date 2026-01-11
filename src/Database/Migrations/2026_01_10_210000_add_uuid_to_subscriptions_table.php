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
            $table->string('uuid', 36)->nullable()->unique()->after('id')->index();
        });

        // Backfill existing subscriptions with UUIDs
        DB::table('subscriptions')
            ->whereNull('uuid')
            ->get()
            ->each(function ($subscription) {
                DB::table('subscriptions')
                    ->where('id', $subscription->id)
                    ->update(['uuid' => (string) Illuminate\Support\Str::uuid()]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
