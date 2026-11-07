<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Semana 6 · Eloquent ORM
     * Tabla base del dominio: cada fila representa un comercio afiliado
     * a la Pasarela de Pagos (TaskBoard).
     */
    public function up(): void
    {
        Schema::create('comercios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_comercio');
            $table->string('rubro');
            $table->string('telefono')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comercios');
    }
};
