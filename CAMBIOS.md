# Registro de cambios · TaskBoard API

## 2026-11-13

### Corregido
- MT-013: GET /api/transacciones responde 422 si ?estado no es
  Iniciada, Completada o Fallida (antes: 200 con lista vacía).
  ⚠ Clientes que enviaban estados mal escritos verán 422.

### Agregado
- Filtro (Guía 17): GET /api/transacciones/filtrar con los parámetros opcionales
  estado, desde y hasta. Solo devuelve las transacciones del usuario del token,
  dentro de auth:sanctum y por TransaccionResource. Endpoint nuevo: no cambia nada existente.
- MT-014: GET /api/transacciones/filtrar acepta monto_min y monto_max, ambos
  OPCIONALES y numéricos >= 0; monto_max debe ser >= monto_min solo cuando se
  envían los dos (si no, 422). Sin estos parámetros el filtro responde igual que
  antes: estado, desde, hasta, el orden y la forma de la respuesta no cambian.
