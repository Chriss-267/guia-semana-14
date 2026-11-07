{{-- Semana 7 · Blade — Componente reutilizable --}}
{{-- Uso: <x-badge-actividad :totalTransacciones="$comercio->transacciones_count" /> --}}
@props(['totalTransacciones'])

@if ($totalTransacciones == 0)
    <span class="badge gris">Sin actividad</span>
@else
    <span class="badge verde">Activo</span>
@endif
