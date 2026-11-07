<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación del formulario "Nueva Transacción".
 * Antes estaba dentro de store(); aquí queda ordenada y se puede reutilizar.
 */
class GuardarTransaccionRequest extends FormRequest
{
    /**
     * ¿Tiene permiso de hacer esto? Es distinto a si los datos son válidos.
     * Artisan lo genera en false (eso da 403). Va en true porque la
     * propiedad ya se revisa en rules(): comercio_id tiene que ser propio.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación.
     *
     * Semana 12: no basta con que el comercio exista; tiene que ser del
     * usuario de la sesión. Así no se cuela un comercio_id ajeno ni
     * editando el campo oculto con las herramientas del navegador.
     *
     * Semana 13: la API todavía no tiene usuario (no usa sesión), así que
     * el filtro por dueño solo se aplica cuando hay alguien autenticado.
     *
     * Semana 15: correcciones de la bitácora de casos límite.
     *  - E-01 (CL-07): max igual al tope de la columna decimal(10,2).
     *  - E-02 (CL-09): decimal:0,2 acepta 25, 25.5 y 25.50, pero no 25.999.
     *    (decimal:2 exigiría EXACTAMENTE dos decimales y rechazaría 25.)
     *  - E-03 (CL-12): el estado no se envía; toda transacción nace Iniciada.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'comercio_id' => [
                'required',
                Rule::exists('comercios', 'id')
                    ->when($this->user(), fn ($regla, $user) => $regla->where('user_id', $user->id)),
            ],
            'cliente_nombre' => 'required|string|max:255',
            'monto' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999.99'],
            'estado' => ['prohibited'],
        ];
    }

    /**
     * Mensajes propios, en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cliente_nombre.required' => 'Debes indicar el nombre del cliente.',
            'monto.required' => 'Debes indicar un monto.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.min' => 'El monto debe ser mayor a cero.',
            'comercio_id.exists' => 'El comercio no existe o no es tuyo.',
            'monto.max' => 'El monto no puede superar 99,999,999.99.',
            'monto.decimal' => 'El monto admite como máximo dos decimales.',
            'estado.prohibited' => 'El estado no se envía al crear: toda transacción nace Iniciada.',
            // E-04: estos salían en inglés ("The nombre del cliente field must not be greater...").
            'comercio_id.required' => 'Debes indicar el comercio.',
            'cliente_nombre.string' => 'El nombre del cliente debe ser texto.',
            'cliente_nombre.max' => 'El nombre del cliente no puede superar 255 caracteres.',
        ];
    }

    /**
     * Nombres bonitos para los mensajes que no personalicé arriba.
     * Así se lee "comercio" y no "comercio_id".
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'cliente_nombre' => 'nombre del cliente',
            'monto' => 'monto de la transacción',
            'comercio_id' => 'comercio',
        ];
    }
}
