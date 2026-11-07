{{-- auth/login.blade.php · Semana 12 --}}
@extends('layouts.app')
@section('titulo', 'Iniciar sesión')

@section('contenido')
    <h1>Iniciar sesión</h1>

    {{-- @csrf también aquí: el login se puede atacar igual que cualquier formulario --}}
    <form method="POST" action="{{ route('login.attempt') }}" class="card">
        @csrf

        <label for="email">Correo</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}">
        {{-- Un solo error genérico: no dice si el correo existe --}}
        @error('email') <span class="error">{{ $message }}</span> @enderror
        <br>

        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password">
        <br>

        <label><input type="checkbox" name="recordar"> Recordarme</label>
        <br>

        <button type="submit">Entrar</button>
    </form>

    <p class="meta">¿No tienes cuenta? <a href="{{ route('registro') }}">Regístrate</a></p>
@endsection
