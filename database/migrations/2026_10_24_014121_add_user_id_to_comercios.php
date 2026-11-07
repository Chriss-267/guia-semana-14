<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Semana 12 · Aislamiento por usuario
     * Cada comercio pertenece a un usuario. Si se borra el usuario,
     * se van con él sus comercios (y en cascada sus transacciones).
     */
    public function up(): void
    {
        Schema::table('comercios', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->after('id')
                ->constrained()
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('comercios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
