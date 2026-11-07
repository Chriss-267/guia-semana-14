<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarTransaccionRequest;
use App\Models\Comercio;
use App\Models\Transaccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransaccionController extends Controller
{
    /**
     * Semana 12 · Una transacción es del usuario si su comercio lo es.
     * Si no, 404 (como si no existiera). Se llama al inicio de cada
     * método que recibe una transacción por la URL.
     */
    private function autorizar(Transaccion $t): void
    {
        abort_unless($t->comercio->user_id === Auth::id(), 404);
    }

    /**
     * Muestra el formulario de nueva transacción.
     * {comercio} llega ya convertido en instancia del modelo.
     * Semana 12: solo en un comercio propio.
     */
    public function create(Comercio $comercio): View
    {
        abort_unless($comercio->user_id === Auth::id(), 404);

        return view('transacciones.create', compact('comercio'));
    }

    /**
     * Guarda la transacción y vuelve al comercio con un mensaje.
     *
     * El type-hint GuardarTransaccionRequest valida antes de entrar
     * aquí, por eso ya no hay validate() en este método.
     *
     * only() toma solo esos 3 campos: si alguien inyecta un "estado"
     * desde el navegador, se ignora. Con all() sí habría pasado.
     *
     * Se redirige en lugar de devolver la vista para que F5 no duplique.
     */
    public function store(GuardarTransaccionRequest $request): RedirectResponse
    {
        $transaccion = Transaccion::create($request->only([
            'comercio_id', 'cliente_nombre', 'monto',
        ]));

        return redirect()
            ->route('comercios.show', $transaccion->comercio_id)
            ->with('mensaje', 'Transacción registrada con éxito.');
    }

    /**
     * Formulario de edición, ya lleno con los datos actuales.
     * No hay find($id): Route Model Binding busca la transacción
     * por el {transaccion} de la ruta y da 404 si no existe.
     * Semana 12: el <select> solo ofrece mis comercios.
     */
    public function edit(Transaccion $transaccion): View
    {
        $this->autorizar($transaccion);

        $comercios = Auth::user()->comercios()->orderBy('nombre_comercio')->get();

        return view('transacciones.edit', compact('transaccion', 'comercios'));
    }

    /**
     * Guarda la edición reutilizando el mismo Form Request de store().
     * validated() solo trae los campos de rules(), así que "estado"
     * no se puede colar desde el formulario.
     */
    public function update(GuardarTransaccionRequest $request, Transaccion $transaccion): RedirectResponse
    {
        $this->autorizar($transaccion);

        $transaccion->update($request->validated());

        return redirect()
            ->route('comercios.show', $transaccion->comercio_id)
            ->with('mensaje', 'Transacción actualizada correctamente.');
    }

    /**
     * Elimina la transacción. Sus eventos se borran solos por el
     * cascadeOnDelete() de la migración de eventos_transaccion.
     *
     * Reto (Misión B): el comercio y el cliente se guardan antes del
     * delete() para poder usarlos en el redirect y en el mensaje.
     */
    public function destroy(Transaccion $transaccion): RedirectResponse
    {
        $this->autorizar($transaccion);

        $comercioId = $transaccion->comercio_id;
        $cliente = $transaccion->cliente_nombre;

        $transaccion->delete();

        return redirect()
            ->route('comercios.show', $comercioId)
            ->with('mensaje', "Transacción de {$cliente} eliminada.");
    }

    /**
     * Cambia solo el estado, sin pasar por el formulario de edición.
     *
     * Reto: no basta con que el estado exista; la transición desde el
     * estado actual también tiene que tener sentido. Una Completada ya
     * se cobró, así que no se mueve a ningún lado.
     */
    public function moverEstado(Transaccion $transaccion, string $estado): RedirectResponse
    {
        $this->autorizar($transaccion);

        $validos = ['Iniciada', 'Completada', 'Fallida'];

        if (! in_array($estado, $validos)) {
            abort(400, 'Estado no válido.');
        }

        $transiciones = [
            'Iniciada' => ['Completada', 'Fallida'],
            'Completada' => [],
            'Fallida' => ['Iniciada'],
        ];

        if (! in_array($estado, $transiciones[$transaccion->estado] ?? [])) {
            abort(400, "No se puede mover de {$transaccion->estado} a {$estado}.");
        }

        $transaccion->update(['estado' => $estado]);

        return redirect()
            ->route('comercios.show', $transaccion->comercio_id)
            ->with('mensaje', "Transacción movida a {$estado}.");
    }

    /*
     * Reto: el mismo Form Request en otro método, sin repetir reglas.
     * validated() devuelve solo los campos que pasaron por rules().
     * Ya probado; se deja comentado como pide la guía.
     *
     * public function duplicarUltima(GuardarTransaccionRequest $request)
     * {
     *     dd($request->validated());
     * }
     */
}
