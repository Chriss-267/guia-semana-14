<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Semana 13 · El contrato público de una transacción.
 * El monto sale como número y la fecha en ISO 8601: el formato
 * visual ($25.50, "hace 2 días") lo decide cada cliente.
 */
class TransaccionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // Semana 14: el cliente web agrupa las Completadas por comercio.
            'comercio_id' => $this->comercio_id,
            'cliente' => $this->cliente_nombre,
            'monto' => (float) $this->monto,
            'estado' => $this->estado,
            'fecha' => $this->created_at->toIso8601String(),
        ];
    }
}
