# Ficha de uso crítico de IA · Semana 16 (Guías 17 y 18)

> Borrador para pasar a las guías. Lo marcado **COMPLETAR** depende de lo que hagas tú (tu asistente de IA, tus
> capturas o tu opinión). Los resultados de pruebas son reales (corridos el 2026-11-13 en `taskboard-semana15`).

| Dato | Respuesta |
|---|---|
| Estudiante | Christian Monterrosa |
| Fecha | 2026-11-13 |
| Herramienta de IA usada | Claude (Anthropic) — **COMPLETAR si usaste otra para el Laboratorio 3** |

---

## Guía 17 (jueves)

### Laboratorio 1 · Reflexión sobre el caso analizado

1. **Leyendo el código se encuentran:** ① ruta sin protección (se ve que está fuera de `auth:sanctum`), ③ datos de
   todos los usuarios (no hay `$request->user()`), ④ inyección SQL (texto concatenado en la consulta) y ⑥ sin Resource
   (`response()->json(...)`).
2. **Solo al ejecutar o probar:** ② el orden de las rutas y ⑤ las fechas (que `hasta` excluya el día). El código
   "se ve bien"; el problema aparece con la petición real (404 en `/filtrar`, o la transacción de hoy que no sale).
3. **② no da error al escribirlo** porque las dos rutas son válidas por separado: Laravel toma la primera que
   coincide, y `transacciones/{transaccion}` también acepta la palabra "filtrar" como si fuera un id. Responde 404 en
   tiempo de ejecución, no al guardar el archivo.
4. **Semáforo:** ① rojo (autenticación) · ② amarillo (ruteo) · ③ rojo (datos de clientes) · ④ rojo (consulta a la
   base con dato del usuario) · ⑤ amarillo (validación) · ⑥ amarillo/verde (contrato de la respuesta).
   *Justificación de ③:* mostrar transacciones de otro usuario expone datos de clientes y montos; es una decisión de
   permisos que no se le delega a la IA.

### Laboratorio 2 · Nivel 1 · Fragmento "Resumen de cobros" (Caso A)

| # | Línea o parte | Problema | Paso que lo detecta | Riesgo | Corrección propuesta |
|---|---|---|---|---|---|
| 1 | `Comercio::find($id)` | No revisa que el comercio sea del usuario del token: Ana ve el resumen de Carlos | 3 · Seguridad / 5 · Probar (Ana pide un id de Carlos) | Rojo | `abort_unless($comercio->user_id === $request->user()->id, 404)` o `$request->user()->comercios()->findOrFail($id)` |
| 2 | `"... WHERE comercio_id = $id"` | SQL armado con el parámetro de la URL: inyección SQL | 3 · Seguridad | Rojo | Eloquent: `$comercio->transacciones()->sum('monto')` |
| 3 | `SUM(monto)` de todas | "Total cobrado" suma también Iniciadas y Fallidas; solo se cobró lo Completado | 1 · Leer (regla del negocio) / 5 · Probar | Rojo (dinero) | `->where('estado', 'Completada')->sum('monto')` |
| 4 | `Comercio::find($id)` → `$comercio->transacciones` | Si el id no existe, `find` da `null` y la línea del foreach revienta con 500 | 4 · Ejecutar / 5 · Probar (id 999) | Amarillo | `findOrFail` → 404 |
| 5 | `'comercio' => $comercio` | Devuelve el modelo crudo (incluye `user_id`, fechas internas): sin Resource, contrato inestable | 1 · Leer / 6 · Contrato | Amarillo | `new ComercioResource($comercio)` o solo los campos necesarios |
| 6 | `foreach ($comercio->transacciones ...)` | Carga TODAS las transacciones en memoria para contar; además `total` sale `null` si no hay ventas y como texto | 4 · Ejecutar / 5 · Probar | Amarillo | `->selectRaw('estado, count(*) as total')->groupBy('estado')->pluck(...)`; `(float) ($total ?? 0)` |

**El más difícil en una revisión rápida:** el #3 (regla del negocio). El código funciona y devuelve un número; solo
es incorrecto porque "cobrado" significa Completada. En Bruno/Insomnia lo pone en evidencia un comercio con una
transacción Fallida: el total no debería incluirla.

### Laboratorio 3 · Nivel 2 · Pedido corto vs completo — **COMPLETAR con tu asistente**

| Problema | ¿Con el pedido corto? | ¿Con el completo? |
|---|---|---|
| ① Fuera de auth:sanctum | COMPLETAR | COMPLETAR |
| ② Orden de las rutas | | |
| ③ Sin filtro por usuario | | |
| ④ SQL crudo | | |
| ⑤ Fechas sin validar / "hasta" excluye el día | | |
| ⑥ Sin Resource | | |
| Otro problema nuevo | Ej.: `after_or_equal:desde` SIN condición hace fallar `?hasta=hoy` cuando no viene `desde` | |

**Conclusión (sugerida):** el pedido completo reduce los errores, pero no los elimina; con cualquier pedido hay que
aplicar los seis pasos, porque la IA no conoce el proyecto real (nombres de columnas, orden de rutas, zona horaria).

### Laboratorio 4 · Nivel 3 · Filtro integrado (Caso B)

- Ruta en `routes/api.php`, dentro de `auth:sanctum` y **antes** de `apiResource`. `php artisan route:list
  --path=api/transacciones` muestra `api/transacciones/filtrar` antes de `{transaccion}`, con `Authenticate:sanctum`.
- Método `filtrar()` en `TransaccionApiController`: valida, usa Eloquent a partir de `$request->user()->transacciones()`,
  `whereDate` para las fechas y devuelve `TransaccionResource::collection(...)`.

| # | Identidad | Petición | Esperado | Obtenido |
|---|---|---|---|---|
| F-1 | Ninguna | GET /api/transacciones/filtrar | 401 | 401 ✅ |
| F-2 | Ana | ?estado=Completada | 200 solo de Ana | 200, 1 fila (comercio 1) ✅ |
| F-3 | Carlos | ?estado=Completada | 200 solo de Carlos | 200, 0 filas (Carlos no tiene Completadas; no ve las de Ana) ✅ |
| F-4 | Ana | ?estado=Pagada | 422 | 422 ✅ |
| F-5 | Ana | ?estado=x' OR '1'='1 | 422 | 422 ✅ |
| F-6 | Ana | ?desde=hola | 422 | 422 ✅ |
| F-7 | Ana | ?desde=2026-11-10&hasta=2026-11-01 | 422 | 422 ✅ |
| F-8 | Ana | ?hasta=(hoy) | 200 incluye las de hoy | 200, incluye la creada hoy ✅ |

**Regresión Semana 15:** colección de casos límite 24/24 en verde. PHPUnit: 11 pruebas pasan.

| Hallazgo (Caso B) | Riesgo | Cambio realizado | Justificación |
|---|---|---|---|
| `after_or_equal:desde` sin condición | Amarillo | `Rule::when($request->filled('desde'), ['after_or_equal:desde'])` | Sin `desde`, F-8 respondía 422 |
| Columnas ambiguas (`created_at` existe en comercios y transacciones por el join) | Amarillo | `transacciones.created_at`, `transacciones.estado` | La consulta pasa por comercios (hasManyThrough) |
| `whereBetween` con fecha sin hora | Amarillo | `whereDate(..., '<=', $hasta)` | Incluye todo el día de "hasta" |

**Documentación oficial consultada:** laravel.com/docs/11.x/validation (`Rule::in`, `Rule::when`, `date`,
`after_or_equal`, `gte`), /queries (`whereDate`, `when`), /eloquent-resources, /sanctum.

### Experimento · Inyección SQL en tinker (resultado real)

| Consulta | count() | ¿Por qué? |
|---|---|---|
| `whereRaw("estado = '" . $ataque . "'")` | **4** (todas) | El texto se pega dentro del SQL: queda `where estado = 'x' OR '1'='1'`, y `'1'='1'` es siempre verdadero |
| `where('estado', $ataque)` | **0** | `toSql()` muestra `where "estado" = ?`: el valor viaja aparte (parámetro enlazado) y se compara como texto literal |

---

## Guía 18 (viernes)

### Línea base
Antes de tocar código: colección Semana 15 → 24 pasan · 0 fallan.

### Laboratorio 1 · Clasificación

| Solicitud | Tipo | Semáforo | Justificación |
|---|---|---|---|
| Incluir el nombre del comercio en cada transacción | Evolutivo | Seguro | Campo nuevo en la respuesta; nadie lo usaba |
| Mover el servidor a PHP 8.4 | Adaptativo | — (no toca el contrato) | Cambio del entorno |
| Cambiar "cliente" por "nombre_cliente" | Evolutivo | Rompe | Quita un campo que la tienda lee |
| Monto negativo "-5" como texto se guarda | Correctivo | Riesgoso | Se valida más estricto: lo que antes entraba ahora da 422 |
| Expiración de tokens | Preventivo | Riesgoso | Los clientes deberán volver a autenticarse |
| Paginar GET /api/transacciones | Evolutivo | Riesgoso | Cambia la forma de la respuesta (ya no viene todo) |

**Renombrar "cliente" sin romper:** agregar `nombre_cliente` conservando `cliente` (los dos con el mismo valor),
anunciar la deprecación y retirar `cliente` solo después de avisar a la tienda y de que migre (o en una versión nueva
de la API).

### Laboratorio 2 · MT-013 (Caso C · correctivo)

- **Reproducido:** `GET /api/transacciones?estado=Pagada` con el token de Ana → 200 con `"data": []`.
- **Impacto:** Ruta no · Validación **sí** (`estado` con `Rule::in`) · Controlador **sí** (`index()`) · Modelo/BD no ·
  Resource no · Pruebas **sí** (carpeta 5. MT-013) · Clientes: los que manden estados mal escritos verán 422.
- **Semáforo:** riesgoso (validación más estricta: 200 → 422), por eso se avisa en CAMBIOS.md con ⚠.

| # | Petición (Ana) | Esperado | Obtenido |
|---|---|---|---|
| C-1 | ?estado=Pagada | 422 error en estado | 422 ✅ |
| C-2 | ?estado=Completada | 200 solo Completadas | 200 ✅ |
| C-3 | sin ?estado | 200 igual que antes | 200 ✅ |
| C-4 | ?estado=completada | **422** (decisión) | 422 ✅ |

**Decisión C-4:** 422. Los estados son un vocabulario fijo del contrato; aceptar variantes en minúsculas abre la puerta a
otras variantes ("COMPLETADA", "Completado") y obliga a todos los clientes a adivinar. Si se quisiera aceptar, sería una
solicitud evolutiva aparte.

| Justificación | Respuesta |
|---|---|
| ¿Qué cambié? | `TransaccionApiController@index`: `$request->validate(['estado' => ['nullable', Rule::in([...])]])` en lugar de `$request->query('estado')`; `use Illuminate\Validation\Rule;` |
| ¿Por qué? | MT-013: un estado inválido respondía 200 vacío y parecía que no había cobros |
| ¿Cómo lo verifiqué? | C-1 a C-4 en Insomnia (todos como se esperaba) + regresión Semana 15 24/24 + PHPUnit 11/11 |
| ¿Qué impacto tiene? | Solo afecta a quien envía estados inválidos (ahora 422). Semáforo: riesgoso, avisado en CAMBIOS.md |
| ¿Usé IA? ¿Para qué? | COMPLETAR (sí/no y para qué) |

### Laboratorio 3 · MT-014 (Caso D · evolutivo)

- **Impacto:** Validación sí · Controlador sí (`filtrar()`) · Ruta, modelo, BD y Resource no · Pruebas sí (6. MT-014).
  Semáforo: **seguro** (parámetros nuevos opcionales; sin ellos todo igual).
- **Pedido a la IA (copia exacta):** el del Paso 2 de la guía, con tu método `filtrar()` pegado. COMPLETAR si lo cambiaste.

| Línea o cambio propuesto | ¿Lo pide MT-014? | Decisión | Motivo |
|---|---|---|---|
| `'monto_min' => ['nullable','numeric','min:0']` | Sí | Aceptar | Opcional, número ≥ 0 |
| `'monto_max' => [..., Rule::when($request->filled('monto_min'), ['gte:monto_min'])]` | Sí | Aceptar | `gte` solo cuando vienen los dos (si no, M-2 daría 422) |
| `->when($datos['monto_min'] ?? null, ...)` | Sí | **Corregir** | Con `when($valor)`, un `monto_max=0` se toma como "no vino" y devuelve TODO. Se usó `when(isset(...))` |
| `where('monto', ...)` sin tabla | Sí | **Corregir** | La consulta pasa por comercios: se calificó `transacciones.monto` |
| Cualquier cambio a fechas, orden u otros filtros | No | Rechazar | No lo pide MT-014 ("una solicitud, un cambio") |

| # | Petición (Ana) | Esperado | Obtenido |
|---|---|---|---|
| M-1 | ?monto_min=20 | 200, montos ≥ 20 | 200, 2 filas ✅ |
| M-2 | ?monto_max=100 (sin mínimo) | 200, montos ≤ 100 | 200, 3 filas ✅ |
| M-3 | ?monto_min=50&monto_max=10 | 422 en monto_max | 422 ✅ |
| M-4 | ?monto_min=-5 | 422 en monto_min | 422 ✅ |
| M-5 | ?monto_min=abc | 422 en monto_min | 422 ✅ |
| M-6 | ?estado=Completada&monto_min=20 | 200, Completadas ≥ 20 | 200, 1 fila ✅ |
| M-7 (extra) | ?monto_max=0 | 200 vacío | 200, 0 filas ✅ |
| F-1 a F-8 | Carpeta 4. Filtro | Igual que ayer | Igual ✅ |
| Semana 15 | Casos límite | En verde | 24/24 ✅ |

| Justificación | Respuesta |
|---|---|
| ¿Qué cambié? | `TransaccionApiController@filtrar`: 2 reglas nuevas (`monto_min`, `monto_max`) y 2 `when` en la consulta |
| ¿Por qué? | MT-014: la tienda quiere revisar cobros grandes para prevenir fraude |
| ¿Cómo lo verifiqué? | M-1 a M-7 + F-1 a F-8 + Semana 15 (24/24) + PHPUnit (11/11) |
| ¿Qué impacto tiene? | Ninguno para los clientes actuales: parámetros opcionales. Semáforo: seguro |

### Experimento · Un renombre que rompe — **COMPLETAR al hacerlo** (petición "7. Experimento renombre")

| Versión del Resource | ¿Pasa la prueba de "cliente"? | ¿Qué le pasaría a la tienda? |
|---|---|---|
| Solo `nombre_cliente` | No (falla: data[0] no tiene "cliente") | Deja de mostrar el nombre del cliente (undefined / vacío) |
| `cliente` y `nombre_cliente` | Sí | Nada: sigue leyendo "cliente" |

**Retirar "cliente":** después de avisar a los clientes de la API (la tienda en línea), darles un plazo para migrar a
`nombre_cliente` y dejarlo anotado en CAMBIOS.md; idealmente en una versión nueva (`/api/v2`).

### Declaración
Declaro que revisé, entendí y probé todo el código generado con IA incluido en mi proyecto, que rechacé los cambios no
solicitados y que puedo explicar cada cambio realizado. Firma: __________________
