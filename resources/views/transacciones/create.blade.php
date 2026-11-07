{{-- transacciones/create.blade.php --}}
@extends('layouts.app')
@section('titulo', 'Nueva transacción')

@section('contenido')
    <p><a href="{{ route('comercios.show', $comercio) }}">&larr; Volver al comercio</a></p>

    <h1>Nueva transacción</h1>
    <p class="meta">Comercio: {{ $comercio->nombre_comercio }}</p>

    {{-- Lista todos los errores juntos --}}
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

    <form action="{{ route('transacciones.store') }}" method="POST">
        @csrf
        {{-- El comercio va oculto; la regla exists evita que lo cambien a mano --}}
        <input type="hidden" name="comercio_id" value="{{ $comercio->id }}">

        {{-- old() devuelve lo escrito para no perderlo si falla la validación --}}
        <label for="cliente">Cliente</label>
        <input id="cliente" name="cliente_nombre" type="text"
               value="{{ old('cliente_nombre') }}">
        @error('cliente_nombre')
            <span class="error">{{ $message }}</span>
        @enderror
        <br>

        <label for="monto">Monto</label>
        <input id="monto" name="monto" type="number" step="0.01"
               value="{{ old('monto') }}">
        @error('monto')
            <span class="error">{{ $message }}</span>
        @enderror
        <br>

        {{-- El comercio_id es oculto, su error solo sale en el resumen --}}
        @error('comercio_id')
            <span class="error">{{ $message }}</span><br>
        @enderror

        <button type="submit">Registrar</button>
    </form>
@endsection
