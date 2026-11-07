<?php

namespace Tests\Feature;

use App\Models\Transaccion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Semana 15 · Casos límite convertidos en pruebas automáticas.
 * Jueves: CL-02 (pasaba) y CL-07 (en rojo hasta corregir E-01).
 * Viernes: pruebas de regresión de E-01, E-02, E-03 y del evento de creación.
 * Base SQLite en memoria (phpunit.xml): no toca database.sqlite.
 */
class CrearTransaccionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * POST de una transacción válida como Ana, cambiando solo lo indicado.
     * Siembra una sola vez por prueba, aunque se llame varias veces.
     */
    private function crear(array $datos): TestResponse
    {
        if (User::count() === 0) {
            $this->seed();
        }
        Sanctum::actingAs(User::where('email', 'ana@taskboard.test')->first());

        return $this->postJson('/api/transacciones', array_merge([
            'comercio_id' => 1, 'cliente_nombre' => 'Luis', 'monto' => 25.50,
        ], $datos));
    }

    // ---------------------------------------------------------------- jueves

    public function test_cl02_monto_cero_responde_422(): void
    {
        $this->crear(['monto' => 0])
            ->assertStatus(422)->assertJsonValidationErrors('monto');
    }

    /**
     * Reto del jueves: estaba en ROJO (201). Pasó a verde al corregir E-01,
     * sin tocar la prueba.
     */
    public function test_cl07_monto_sobre_el_maximo_responde_422(): void
    {
        $this->crear(['monto' => 100000000])
            ->assertStatus(422)->assertJsonValidationErrors('monto');
    }

    // ---------------------------------------------------------------- viernes

    public function test_e01_monto_sobre_el_maximo(): void
    {
        $this->crear(['monto' => 100000000])
            ->assertStatus(422)->assertJsonValidationErrors('monto');
    }

    public function test_e01_monto_exacto_en_el_maximo_se_acepta(): void
    {
        $this->crear(['monto' => 99999999.99])->assertStatus(201);
    }

    public function test_e02_tres_decimales(): void
    {
        $this->crear(['monto' => 25.999])
            ->assertStatus(422)->assertJsonValidationErrors('monto');
    }

    /**
     * decimal:0,2 y no decimal:2: 25 y 25.5 deben seguir siendo válidos.
     */
    public function test_e02_cero_o_un_decimal_siguen_siendo_validos(): void
    {
        $this->crear(['monto' => 25])->assertStatus(201);
        $this->crear(['monto' => 25.5])->assertStatus(201);
    }

    public function test_e03_estado_prohibido(): void
    {
        $this->crear(['estado' => 'Completada'])
            ->assertStatus(422)->assertJsonValidationErrors('estado');
    }

    public function test_e04_mensaje_de_longitud_en_espanol(): void
    {
        $this->crear(['cliente_nombre' => str_repeat('a', 256)])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'cliente_nombre' => 'El nombre del cliente no puede superar 255 caracteres.',
            ]);
    }

    public function test_crear_registra_el_evento(): void
    {
        $this->crear([])->assertStatus(201);

        $id = Transaccion::latest('id')->first()->id;
        $this->assertDatabaseHas('eventos_transaccion', [
            'transaccion_id' => $id, 'tipo_evento' => 'creacion',
        ]);
    }
}
