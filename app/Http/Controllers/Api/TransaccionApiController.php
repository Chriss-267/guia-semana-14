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
        $estado = $request->query('estado');

        return TransaccionResource::collection(
            $request->user()->transacciones()
                ->when($estado, fn ($q) => $q->where('transacciones.estado', $estado))
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
