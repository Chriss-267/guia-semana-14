<!DOCTYPE html>
{{-- Formulario de práctica. No toca la base de datos. --}}
<html lang="es">
<head><meta charset="UTF-8"><title>Sandbox de formulario</title></head>
<body>

    <h1>Laboratorio de formularios</h1>

    {{-- url() arma la ruta completa; con "/practica/enviar" daba 404 en XAMPP --}}
    <form action="{{ url('/practica/enviar') }}" method="POST">
        @csrf

        <label for="cliente">Cliente</label>
        <input id="cliente" name="cliente_nombre" type="text"><br>

        <label for="monto">Monto</label>
        <input id="monto" name="monto" type="number" step="0.01"><br>

        <label for="estado">Estado</label>
        <select id="estado" name="estado">
            <option value="Iniciada">Iniciada</option>
            <option value="Completada">Completada</option>
        </select><br>

        {{-- Ejercicio 1.1: correo y checkbox --}}
        <label for="correo">Correo de contacto</label>
        <input id="correo" name="correo_contacto" type="email"><br>

        <input id="recurrente" name="es_recurrente" type="checkbox" value="1">
        <label for="recurrente">¿Es una transacción recurrente?</label><br>

        <button type="submit">Enviar (modo prueba)</button>
    </form>
</body>
</html>
