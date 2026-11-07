<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Semana 6 · Eloquent ORM
     * Bitácora de eventos de cada transacción (auditoría/trazabilidad).
     * Una transacción tiene muchos eventos (hasMany).
     */
    public function up(): void
    {
        Schema::create('eventos_transaccion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaccion_id')
                ->constrained('transacciones')
                ->cascadeOnDelete();
            $table->string('tipo_evento');
            $table->text('detalle')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_transaccion');
    }
};
