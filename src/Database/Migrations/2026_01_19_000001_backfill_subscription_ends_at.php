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
        // Actualizar suscripciones existentes donde ends_at es NULL pero trial_ends_at tiene valor
        // Esto corrige suscripciones de prueba creadas antes de este fix
        DB::table('subscriptions')
            ->whereNull('ends_at')
            ->whereNotNull('trial_ends_at')
            ->update([
                'ends_at' => DB::raw('trial_ends_at'),
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversible - no queremos eliminar ends_at de trials existentes
        // ya que rompe la validación de los agentes
    }
};
