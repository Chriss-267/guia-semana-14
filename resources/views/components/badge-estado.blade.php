{{-- Semana 7 · Blade — Componente reutilizable --}}
{{-- Uso: <x-badge-estado :estado="$transaccion->estado" /> --}}
@props(['estado'])

@if ($estado === 'Completada')
    <span class="badge verde">✔ Completada</span>
@elseif ($estado === 'Fallida')
    <span class="badge rojo">✗ Fallida</span>
@else
    <span class="badge amarillo">⏳ {{ $estado }}</span>
@endif
