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
            'monto' => 'required|numeric|min:0.01',
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
