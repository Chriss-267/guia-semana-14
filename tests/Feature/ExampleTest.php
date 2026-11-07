<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Desde la Semana 12 la raíz manda a /comercios, que exige sesión:
     * sin login termina en /login, que sí responde 200.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')->assertRedirect(route('comercios.index'));

        $this->get('/login')->assertStatus(200);
    }
}
