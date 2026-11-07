<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Semana 12 · Formulario "Crear cuenta".
     */
    public function mostrarRegistro(): View
    {
        return view('auth.registro');
    }

    /**
     * Crea el usuario y le abre sesión de una vez.
     *
     * La clave no se cifra aquí: el cast 'hashed' de User lo hace solo.
     * regenerate() cambia el ID de sesión justo al autenticar para
     * evitar la fijación de sesión.
     */
    public function registrar(RegistroRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('comercios.index')
            ->with('mensaje', "¡Bienvenido, {$user->name}!");
    }

    public function mostrarLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Auth::attempt() busca por correo y compara el hash.
     *
     * intended() regresa a la página que se pidió antes de que el
     * middleware auth mandara a /login. El error es uno solo y
     * genérico para no revelar si el correo existe.
     */
    public function login(Request $request): RedirectResponse
    {
        $datos = $request->only('email', 'password');

        if (Auth::attempt($datos, $request->boolean('recordar'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('comercios.index'));
        }

        return back()
            ->withErrors(['email' => 'Las credenciales no coinciden.'])
            ->onlyInput('email');
    }

    /**
     * Cerrar sesión es destruir los datos en el servidor, no solo la
     * cookie. regenerateToken() renueva también el token CSRF.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
