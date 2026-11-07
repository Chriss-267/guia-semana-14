{{-- transacciones/edit.blade.php --}}
{{-- Copia de create.blade.php: cambia la ruta, el @method('PUT') y los value --}}
@extends('layouts.app')
@section('titulo', 'Editar transacción')

@section('contenido')
    <p><a href="{{ route('comercios.show', $transaccion->comercio_id) }}">&larr; Volver al comercio</a></p>

    <h1>Editar Transacción</h1>
    <p class="meta">Estado actual: <x-badge-estado :estado="$transaccion->estado" /></p>

    @if ($errors->any())
        <div class="resumen-errores">
            <strong>Revisa estos datos antes de continuar:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- El form solo sabe POST; @method('PUT') le dice a Laravel el verbo real --}}
    <form action="{{ route('transacciones.update', $transaccion) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- old('campo', $transaccion->campo): primero lo que se escribió si falló
             la validación, si no hay nada, el valor guardado --}}
        <label for="comercio">Comercio</label>
        <select id="comercio" name="comercio_id">
            @foreach ($comercios as $comercio)
                <option value="{{ $comercio->id }}"
                    @selected(old('comercio_id', $transaccion->comercio_id) == $comercio->id)>
                    {{ $comercio->nombre_comercio }}
                </option>
            @endforeach
        </select>
        @error('comercio_id')
            <span class="error">{{ $message }}</span>
        @enderror
        <br>

        <label for="cliente">Cliente</label>
        <input id="cliente" name="cliente_nombre" type="text"
               value="{{ old('cliente_nombre', $transaccion->cliente_nombre) }}">
        @error('cliente_nombre')
            <span class="error">{{ $message }}</span>
        @enderror
        <br>

        <label for="monto">Monto</label>
        <input id="monto" name="monto" type="number" step="0.01"
               value="{{ old('monto', $transaccion->monto) }}">
        @error('monto')
            <span class="error">{{ $message }}</span>
        @enderror
        <br>

        <button type="submit">Guardar cambios</button>
    </form>
@endsection
