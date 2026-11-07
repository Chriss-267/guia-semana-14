{{-- Semana 7 · Blade --}}
{{-- resources/views/comercios/index.blade.php --}}
@extends('layouts.app')

@section('titulo', 'Comercios afiliados')

@section('contenido')
    <h1>Comercios afiliados a la pasarela</h1>

    {{-- Buscador. Es GET, por eso no lleva @csrf.
         request('buscar') conserva lo escrito al buscar. --}}
    <form action="{{ route('comercios.index') }}" method="GET">
        <input type="text" name="buscar"
               placeholder="Buscar comercio..."
               value="{{ request('buscar') }}">

        {{-- Segundo filtro, por rubro --}}
        <select name="rubro">
            <option value="">Todos los rubros</option>
            @foreach ($rubros as $rubro)
                <option value="{{ $rubro }}" @selected(request('rubro') === $rubro)>
                    {{ $rubro }}
                </option>
            @endforeach
        </select>

        <button type="submit">Buscar</button>
    </form>

    <ul class="comercios">
        @forelse ($comercios as $comercio)
            <li class="card">
                <a href="{{ route('comercios.show', $comercio) }}">
                    {{ $comercio->nombre_comercio }}
                </a>
                <span class="meta">— {{ $comercio->rubro }}
                    ({{ $comercio->transacciones_count }} transacciones)</span>
                <br>
                <x-badge-actividad :totalTransacciones="$comercio->transacciones_count" />
            </li>
        @empty
            <li class="card">Aún no hay comercios afiliados.</li>
        @endforelse
    </ul>
@endsection
