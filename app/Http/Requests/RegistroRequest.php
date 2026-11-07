<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Semana 12 · Validación del formulario "Crear cuenta".
 */
class RegistroRequest extends FormRequest
{
    /**
     * Registrarse es público: cualquiera sin sesión puede intentarlo.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * confirmed exige un campo password_confirmation igual a password.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Debes indicar tu nombre.',
            'email.required' => 'Debes indicar un correo.',
            'email.email' => 'El correo no tiene un formato válido.',
            'email.unique' => 'Ese correo ya está registrado.',
            'password.required' => 'Debes indicar una contraseña.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
