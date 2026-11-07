<?php

namespace App\Http\Controllers;

use App\Models\Comercio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ComercioController extends Controller
{
    /**
     * Semana 5 · Routing y Controladores
     * GET /comercios
     *
     * Lista todos los comercios afiliados junto con el conteo de sus
     * transacciones. withCount() evita el problema N+1: en vez de
     * disparar una consulta extra por cada comercio dentro de la vista,
     * trae el conteo ya resuelto en la consulta principal.
     *
     * Filtra por GET: buscar (nombre) y rubro. when() solo aplica el
     * where si el campo trae valor, así que vacíos muestran todo.
     *
     * Semana 12: ya no es Comercio::all() sino Auth::user()->comercios(),
     * así cada usuario lista solo lo suyo. El dueño sale de la sesión.
     */
    public function index(Request $request): View
    {
        $comercios = Auth::user()
            ->comercios()
            ->when($request->buscar,
                fn ($q) => $q->where('nombre_comercio',
                    'like', "%{$request->buscar}%"))
            ->when($request->rubro,
                fn ($q) => $q->where('rubro', $request->rubro))
            ->withCount('transacciones')
            ->orderBy('nombre_comercio')
            ->get();

        // Llena el <select> de rubros (solo los de mis comercios)
        $rubros = Auth::user()
            ->comercios()
            ->select('rubro')
            ->distinct()
            ->orderBy('rubro')
            ->pluck('rubro');

        return view('comercios.index', compact('comercios', 'rubros'));
    }

    /**
     * GET /comercios/{comercio}
     *
     * Route Model Binding: Laravel convierte automáticamente el
     * parámetro {comercio} de la ruta en una instancia de Comercio
     * (o lanza 404 si no existe).
     *
     * load('transacciones') trae la relación en una sola consulta
     * adicional (no una por cada transacción), evitando el N+1.
     *
     * Semana 12: listar solo lo propio no basta, la URL se puede escribir
     * a mano. Si no es suyo, 404: no revela que el comercio existe.
     */
    public function show(Comercio $comercio): View
    {
        abort_unless($comercio->user_id === Auth::id(), 404);

        $comercio->load('transacciones');

        return view('comercios.show', compact('comercio'));
    }
}
