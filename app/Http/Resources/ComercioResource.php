<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Semana 13 · El contrato público de un comercio.
 * La clave del arreglo es el nombre público; user_id y las fechas
 * se quedan adentro aunque existan en la tabla.
 */
class ComercioResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre_comercio,
            'rubro' => $this->rubro,
            // Solo aparece si se cargó con load()/with(): evita el N+1 en el listado.
            'transacciones' => TransaccionResource::collection(
                $this->whenLoaded('transacciones')
            ),
        ];
    }
}
