<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Semana 6 · Eloquent ORM
     * Cada transacción pertenece a un comercio (belongsTo).
     * Un comercio tiene muchas transacciones (hasMany).
     */
    public function up(): void
    {
        Schema::create('transacciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comercio_id')
                ->constrained('comercios')
                ->cascadeOnDelete();
            $table->decimal('monto', 8, 2);
            $table->string('cliente_nombre');
            $table->enum('estado', ['Iniciada', 'Completada', 'Fallida'])
                ->default('Iniciada');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transacciones');
    }
};
