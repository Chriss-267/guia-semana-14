{{-- auth/registro.blade.php · Semana 12 --}}
@extends('layouts.app')
@section('titulo', 'Crear cuenta')

@section('contenido')
    <h1>Crear cuenta</h1>

    <form method="POST" action="{{ route('registro.store') }}" class="card">
        @csrf

        <label for="name">Nombre</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}">
        @error('name') <span class="error">{{ $message }}</span> @enderror
        <br>

        <label for="email">Correo</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}">
        @error('email') <span class="error">{{ $message }}</span> @enderror
        <br>

        {{-- La contraseña nunca se rellena con old() --}}
        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password">
        @error('password') <span class="error">{{ $message }}</span> @enderror
        <br>

        <label for="password_confirmation">Confirmar contraseña</label>
        <input id="password_confirmation" type="password" name="password_confirmation">
        <br>

        <button type="submit">Crear cuenta</button>
    </form>

    <p class="meta">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
@endsection
