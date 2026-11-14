<?php

use App\Http\Controllers\Api\TransaccionApiController;
use App\Http\Resources\ComercioResource;
use App\Models\Comercio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| API Routes — TaskBoard (Pasarela de Pagos)
|--------------------------------------------------------------------------
| Semana 13 · API REST con API Resources. Todas llevan el prefijo /api.
|
| Semana 14 · API protegida con Sanctum. Solo el login es público; el
| resto exige "Authorization: Bearer <token>" y "Accept: application/json".
| Regla de oro: todo sale de $request->user(). Así Ana nunca ve ni toca
| lo de Carlos, y lo ajeno responde 404 (no existe para ti).
*/

// Login: devuelve un token por dispositivo. 201 porque se crea un token.
Route::post('/login', function (Request $request) {
    $datos = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
        'dispositivo' => 'nullable|string|max:100',
    ]);

    // Límite: 5 intentos fallidos por correo + IP; el 6.º responde 429.
    $llave = Str::lower($datos['email']).'|'.$request->ip();

    if (RateLimiter::tooManyAttempts($llave, 5)) {
        return response()->json([
            'message' => 'Demasiados intentos. Espera '.RateLimiter::availableIn($llave).' segundos.',
        ], 429);
    }

    $user = User::where('email', $datos['email'])->first();

    if (! $user || ! Hash::check($datos['password'], $user->password)) {
        RateLimiter::hit($llave, 60);

        throw ValidationException::withMessages([
            'email' => 'Las credenciales no son correctas.',
        ]);
    }

    RateLimiter::clear($llave);

    $token = $user->createToken($datos['dispositivo'] ?? 'api')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user->only(['id', 'name', 'email']),
    ], 201);
})->name('api.login');

Route::middleware('auth:sanctum')->group(function () {

    // Quién soy según el token.
    Route::get('/yo', function (Request $request) {
        return $request->user()->only(['id', 'name', 'email']);
    });

    // Antes (Semana 13) era Comercio::all(): ahora solo los del token.
    Route::get('/comercios', function (Request $request) {
        return ComercioResource::collection(
            $request->user()->comercios()->orderBy('id')->get()
        );
    });

    // Ajeno o inexistente: 404 en ambos casos.
    Route::get('/comercios/{comercio}', function (Request $request, Comercio $comercio) {
        abort_unless($comercio->user_id === $request->user()->id, 404);

        return new ComercioResource($comercio->load('transacciones'));
    });

    // Semana 16 (jueves): filtro por estado y rango de fechas (viernes: + monto).
    // Va DENTRO de auth:sanctum y ANTES de apiResource: si fuera después,
    // "filtrar" caería en transacciones/{transaccion} y respondería 404.
    Route::get('/transacciones/filtrar', [TransaccionApiController::class, 'filtrar'])
        ->name('api.transacciones.filtrar');

    // index, store, show, update y destroy. El parámetro se llama
    // {transaccion}; sin parameters() Laravel lo nombraría {transaccione}.
    // names(): "transacciones.store" ya es de los formularios web.
    Route::apiResource('transacciones', TransaccionApiController::class)
        ->parameters(['transacciones' => 'transaccion'])
        ->names('api.transacciones');

    // Revoca SOLO el token con el que se hizo esta petición.
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    });
});
