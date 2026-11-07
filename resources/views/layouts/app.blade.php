<!DOCTYPE html>
{{-- Semana 7 · Blade — Layout maestro --}}
{{-- Toda vista hija hereda esta estructura con @extends('layouts.app') --}}
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Pasarela de Pagos — TaskBoard')</title>

    <style>
        :root {
            --navy: #0B2E59;
            --gold: #F2A900;
            --ink: #13233D;
            --gray: #5B6B85;
            --line: #D7E1EF;
            --bg: #F5F8FC;
            --green: #1E7E52;
            --red: #C23B32;
        }
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, "Segoe UI", Calibri, Arial, sans-serif;
            background: var(--bg);
            color: var(--ink);
            margin: 0;
        }
        nav {
            background: var(--navy);
            color: #fff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }
        nav .sesion {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        nav .tag {
            background: var(--gold);
            color: var(--navy);
            padding: 0.2rem 0.7rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        main {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        h1 { color: var(--navy); }
        h2 { color: var(--navy); font-size: 1.1rem; margin-top: 2rem; }
        .card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 1rem 1.25rem;
            margin-bottom: 0.75rem;
        }
        .card a { color: var(--navy); font-weight: bold; text-decoration: none; }
        .card a:hover { text-decoration: underline; }
        .meta { color: var(--gray); font-size: 0.9rem; }
        .badge {
            display: inline-block;
            padding: 0.2rem 0.7rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: bold;
            color: #fff;
        }
        .badge.verde { background: var(--green); }
        .badge.rojo { background: var(--red); }
        .badge.amarillo { background: var(--gold); color: var(--navy); }
        .badge.gris { background: #9AAAC4; }
        footer {
            text-align: center;
            color: var(--gray);
            font-size: 0.85rem;
            padding: 2rem 0;
        }
        ul.comercios { list-style: none; padding: 0; }

        /* Estilos de los mensajes de error */
        .error {
            color: var(--red);
            font-size: 0.85rem;
            margin-left: 0.5rem;
        }
        .resumen-errores {
            background: #FBEAE9;
            border: 1px solid var(--red);
            color: var(--red);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin: 1rem 0;
        }
        .resumen-errores ul { margin: 0.5rem 0 0; padding-left: 1.2rem; }

        /* Botones de cada transacción (editar, mover estado, eliminar) */
        .acciones {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.6rem;
        }
        .acciones form { display: inline; margin: 0; }
        .acciones button.peligro { color: var(--red); }
    </style>
</head>
<body>
    <nav class="barra">
        <a href="{{ route('comercios.index') }}">Pasarela de Pagos · TaskBoard</a>

        {{-- Semana 12: la barra cambia según haya o no sesión --}}
        <div class="sesion">
            @auth
                <span>Hola, {{ Auth::user()->name }}</span>
                {{-- POST, no un enlace GET: una <img src="/logout"> en otra página no te puede sacar --}}
                <form method="POST" action="{{ route('logout') }}" style="display:inline">
                    @csrf
                    <button type="submit">Salir</button>
                </form>
            @endauth

            @guest
                <a href="{{ route('login') }}">Entrar</a>
                <a href="{{ route('registro') }}">Registrarse</a>
            @endguest
        </div>
    </nav>

    {{-- Mensaje de éxito. Dura un solo request: con F5 desaparece. --}}
    @if (session('mensaje'))
        <div class="alerta-exito" style="background:#EAF6EE;
                color:#1E7E52; padding:0.75rem 1rem; margin:1rem auto;
                max-width:900px; border-radius:8px;">
            {{ session('mensaje') }}
        </div>
    @endif

    <main>
        @yield('contenido')
    </main>

    <footer>
        &copy; {{ date('Y') }} UPED — Integración de Sistemas
    </footer>
</body>
</html>
