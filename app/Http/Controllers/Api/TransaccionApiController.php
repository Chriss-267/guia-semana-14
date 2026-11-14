<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarTransaccionRequest;
use App\Http\Resources\TransaccionResource;
use App\Models\Transaccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Semana 13 · Transacciones por la API. Sin vistas: todo responde JSON
 * a través de TransaccionResource.
 *
 * Semana 14: ya hay token. Todo sale de $request->user(); una
 * transacción ajena responde 404 (no existe para ti).
 */
class TransaccionApiController extends Controller
{
    /**
     * Una transacción es del usuario si su comercio lo es.
     */
    private function autorizar(Request $request, Transaccion $t): void
    {
        abort_unless($t->comercio->user_id === $request->user()->id, 404);
    }

    /**
     * GET /api/transacciones
     *
     * Reto: ?estado=Completada filtra. when() aplica el where solo si
     * viene el parámetro; un estado sin coincidencias da "data": [] con 200.
     * Las columnas van con el nombre de la tabla porque la consulta
     * pasa por comercios (usuario → comercio → transacción).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // Semana 16 · MT-013 (correctivo): un estado que no existe ("Pagada")
        // respondía 200 con "data": [] y parecía que no había cobros. Ahora
        // responde 422. Sin ?estado y con un estado válido, igual que antes.
        $datos = $request->validate([
            'estado' => ['nullable', Rule::in(['Iniciada', 'Completada', 'Fallida'])],
        ]);
        $estado = $datos['estado'] ?? null;

        return TransaccionResource::collection(
            $request->user()->transacciones()
                ->when($estado, fn ($q) => $q->where('transacciones.estado', $estado))
                ->latest('transacciones.created_at')
                ->get()
        );
    }

    /**
     * GET /api/transacciones/filtrar
     *
     * Semana 16 (jueves): filtro revisado con el método de seis pasos. Todos los
     * parámetros son OPCIONALES:
     *  - estado: solo Iniciada, Completada o Fallida (otro valor → 422; así
     *    tampoco entra "x' OR '1'='1").
     *  - desde / hasta: fechas; "hasta" debe ser >= "desde" solo si vienen los dos.
     *    whereDate compara solo la FECHA: ?hasta=hoy incluye las de hoy (con
     *    whereBetween y la fecha sin hora, las de hoy quedaban fuera).
     *  - monto_min / monto_max (viernes, MT-014): números >= 0; monto_max >= monto_min
     *    solo si vienen los dos.
     *
     * Solo las transacciones del usuario del token (usuario → comercios →
     * transacciones), con Eloquent (nada de SQL armado a mano) y por el Resource.
     * Las columnas van con el nombre de la tabla porque comercios también tiene
     * created_at.
     */
    public function filtrar(Request $request): AnonymousResourceCollection
    {
        $datos = $request->validate([
            'estado' => ['nullable', Rule::in(['Iniciada', 'Completada', 'Fallida'])],
            'desde' => ['nullable', 'date'],
            // Sin la condición, ?hasta=hoy SOLO (sin "desde") respondería 422.
            'hasta' => ['nullable', 'date', Rule::when($request->filled('desde'), ['after_or_equal:desde'])],
            // MT-014
            'monto_min' => ['nullable', 'numeric', 'min:0'],
            'monto_max' => ['nullable', 'numeric', 'min:0',
                Rule::when($request->filled('monto_min'), ['gte:monto_min'])],
        ]);

        return TransaccionResource::collection(
            $request->user()->transacciones()
                ->when($datos['estado'] ?? null, fn ($q, $estado) => $q->where('transacciones.estado', $estado))
                ->when($datos['desde'] ?? null, fn ($q, $desde) => $q->whereDate('transacciones.created_at', '>=', $desde))
                ->when($datos['hasta'] ?? null, fn ($q, $hasta) => $q->whereDate('transacciones.created_at', '<=', $hasta))
                // MT-014. isset() y no "?? null": con when($valor) un monto_max=0
                // se tomaría como "no vino" y devolvería TODO en vez de nada.
                ->when(isset($datos['monto_min']), fn ($q) => $q->where('transacciones.monto', '>=', $datos['monto_min']))
                ->when(isset($datos['monto_max']), fn ($q) => $q->where('transacciones.monto', '<=', $datos['monto_max']))
                ->latest('transacciones.created_at')
                ->get()
        );
    }

    /**
     * POST /api/transacciones
     *
     * GuardarTransaccionRequest solo acepta un comercio_id propio: el
     * comercio de otro usuario responde 422.
     *
     * Semana 15: la transacción y su evento de creación se guardan juntos.
     * Si algo falla entre los dos pasos, DB::transaction() hace ROLLBACK y
     * no queda una transacción sin bitácora (experimento del laboratorio 5).
     */
    public function store(GuardarTransaccionRequest $request): JsonResponse
    {
        $t = DB::transaction(function () use ($request) {
            $t = Transaccion::create($request->validated());

            $t->eventos()->create([
                'tipo_evento' => 'creacion',
                'detalle' => 'Transacción iniciada',
            ]);

            return $t;
        });

        return (new TransaccionResource($t->refresh()))->response()->setStatusCode(201);
    }

    public function show(Request $request, Transaccion $transaccion): TransaccionResource
    {
        $this->autorizar($request, $transaccion);

        return new TransaccionResource($transaccion);
    }

    public function update(GuardarTransaccionRequest $request, Transaccion $transaccion): TransaccionResource
    {
        $this->autorizar($request, $transaccion);

        $transaccion->update($request->validated());

        return new TransaccionResource($transaccion);
    }

    /**
     * DELETE /api/transacciones/{transaccion} — 204 sin cuerpo.
     */
    public function destroy(Request $request, Transaccion $transaccion): Response
    {
        $this->autorizar($request, $transaccion);

        $transaccion->delete();

        return response()->noContent();
    }
}
