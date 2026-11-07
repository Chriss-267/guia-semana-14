<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ComercioSeeder extends Seeder
{
    /**
     * Datos de ejemplo usados en todas las guías de Semana 6-8.
     *
     * Semana 12: cada comercio se crea desde su dueño, así user_id se
     * llena solo. Semana 13-14: Ana tiene 2 comercios y Carlos 1, y el
     * orden importa porque las guías usan los ids:
     * - 1 Café Amanecer (Ana): una Completada y una Iniciada.
     * - 2 Ferretería El Tornillo (Carlos): el "comercio ajeno" para Ana.
     * - 3 Pupusería Doña Marta (Ana): una Fallida.
     */
    public function run(): void
    {
        $ana = User::where('email', 'ana@taskboard.test')->firstOrFail();
        $carlos = User::where('email', 'carlos@taskboard.test')->firstOrFail();

        $cafeAmanecer = $ana->comercios()->create([
            'nombre_comercio' => 'Café Amanecer',
            'rubro' => 'Cafetería',
            'telefono' => '2222-1234',
        ]);

        $cafeAmanecer->transacciones()->createMany([
            [
                'monto' => 45.00,
                'cliente_nombre' => 'María López',
                'estado' => 'Completada',
            ],
            [
                'monto' => 12.50,
                'cliente_nombre' => 'Juan Pérez',
                'estado' => 'Iniciada',
            ],
        ]);

        $ferreteria = $carlos->comercios()->create([
            'nombre_comercio' => 'Ferretería El Tornillo',
            'rubro' => 'Ferretería',
            'telefono' => '2222-5678',
        ]);

        $ferreteria->transacciones()->create([
            'monto' => 30.00,
            'cliente_nombre' => 'Rosa Hernández',
            'estado' => 'Iniciada',
        ]);

        // Comercio adicional para tener más variedad al probar la interfaz.
        $pupuseria = $ana->comercios()->create([
            'nombre_comercio' => 'Pupusería Doña Marta',
            'rubro' => 'Restaurante',
            'telefono' => '2222-9012',
        ]);

        $pupuseria->transacciones()->create([
            'monto' => 8.00,
            'cliente_nombre' => 'Carlos Ramírez',
            'estado' => 'Fallida',
        ]);
    }
}
