<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Semana 15 · E-01
     * La columna nació como decimal(8,2): tope 999,999.99. La regla
     * max:99999999.99 de la guía asume decimal(10,2); sin este cambio,
     * en MySQL un monto de 1,000,000 seguiría provocando un 500.
     * Regla y columna deben decir lo mismo.
     */
    public function up(): void
    {
        Schema::table('transacciones', function (Blueprint $table) {
            $table->decimal('monto', 10, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('transacciones', function (Blueprint $table) {
            $table->decimal('monto', 8, 2)->change();
        });
    }
};
