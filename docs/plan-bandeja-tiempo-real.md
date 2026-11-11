# Bandeja de comandas en tiempo real

**Problema.** El agente de WhatsApp inserta las ventas directo en MySQL (`agent/tools.py`) y después
llama a `POST /api/agente/ventas/{folio}/imprimir-orden` (encabezado `X-Agente-Token`). La orden se
imprime en cocina y barra, pero la bandeja (`/comandas`) se pinta en el servidor y no cambia hasta
refrescar la página. Lo mismo pasa entre tablets: si una abre o cobra una venta, las otras no se enteran.

## 1. Implementado (2026-11-10): consulta periódica

| Pieza | Dónde |
|---|---|
| Firma de las ventas abiertas (conteo, folio máximo, última modificación y totales de `ventas` y `ventasproductos`) | `App\Services\BandejaService::firma()` |
| `GET /comandas/bandeja?firma=&desde=&page=` (rol mesero, encargado o admin). Firma igual → `204`; cambió → `{firma, maxFolio, html, nuevas}` | `VentasController@bandeja`, ruta `bandeja` |
| `nuevas`: ventas abiertas del agente con folio mayor a `desde` (folio, cliente, tipo, express) | `BandejaService::nuevasDelAgente()` |
| Consulta cada `BANDEJA_INTERVALO` s (5 por defecto), cambia solo la tabla y respeta la página | `public/js/bandeja.js` |
| Aviso visual (esquina superior derecha, encima de los modales, se cierra al tocarlo) y sonido "ding-dong" solo para pedidos de WhatsApp | `bandeja.js`, CSS `.wk-aviso*` en `mystyle.css` |
| Filas nuevas resaltadas unos segundos (cualquier origen) | `.wk-fila-nueva` |
| Índice `ventas(estatus, activo)`: la firma bajó de ~72 ms a ~2 ms con 68 mil ventas | migración `2026_11_10_000001` |

Reglas:
- Con un modal abierto (cobrar, etc.) no se reemplaza la tabla; el aviso y el sonido sí salen. Al
  cerrar el modal se consulta de inmediato.
- Con la pestaña oculta no se consulta; al volver se consulta al momento.
- `401`/`419` (sesión terminada) detiene la consulta y avisa que hay que recargar. Errores de red o
  `500` se reintentan en la siguiente vuelta.
- **Sonido:** el navegador no deja sonar audio hasta que alguien toca la pantalla. Mientras no esté
  habilitado se muestra el botón **Activar sonido** junto a "Nueva venta". Basta un toque por carga
  de página.
- La consulta periódica mantiene viva la sesión mientras la pantalla de comandas esté abierta.

Variables de `.env` (todas opcionales; `config/services.php` → `bandeja`):

```
BANDEJA_INTERVALO=5             # segundos entre consultas (mínimo 2)
BANDEJA_WEBSOCKETS=false        # ver sección 2
BANDEJA_INTERVALO_RESPALDO=60   # segundos entre consultas cuando el websocket está conectado
```

Costo: 1 petición cada 5 s por tablet; casi todas responden `204` sin cuerpo con dos consultas
indexadas.

## 2. Websockets después: bandera, no reemplazo

**Recomendación: bandera en `.env` (`BANDEJA_WEBSOCKETS`) y la consulta periódica se queda como
respaldo.** No conviene un reemplazo directo porque:

1. El agente escribe en MySQL sin pasar por Laravel. Laravel solo se entera si el agente llama al
   endpoint de impresión; si esa llamada falla (red, impresión apagada con `503`, sabores con `422`),
   no habría evento y la venta no aparecería.
2. Si el proceso `websockets:serve` se cae o la tablet pierde la conexión, la pantalla se quedaría
   congelada sin que nadie lo note.
3. Con la bandera se puede encender y apagar en producción sin desplegar código.

Cómo queda preparado:
- `bandeja.js` expone `WK.bandeja.refrescar()` y `WK.bandeja.socketConectado(true|false)`.
- Con `BANDEJA_WEBSOCKETS=true` la pantalla baja a `BANDEJA_INTERVALO_RESPALDO` **solo mientras el
  socket esté conectado**; si no conecta o se cae, vuelve sola a `BANDEJA_INTERVALO`. Encender la
  bandera sin el servidor de websockets no rompe nada.

Pasos cuando se instale (junto con la pantalla de prioridad en cocina, `docs/plan-prioridad-cocina.md`):
1. `beyondcode/laravel-websockets` 1.4 + `pusher/pusher-php-server` ~4.0; `BROADCAST_DRIVER=pusher`
   apuntando a `127.0.0.1:6001`; `php artisan websockets:serve` como servicio permanente.
2. Evento `BandejaCambio` (`ShouldBroadcastNow`, canal público sin datos) disparado desde
   `Api\AgenteController::imprimirOrden` (al inicio, antes de validar impresión),
   `VentasController@store/update/cerrarVenta` y `VentaService` (agregar, eliminar, comandar, cobrar),
   dentro de `try/catch` para que nunca bloquee una venta.
3. `pusher-js` en `public/vendor` (sin compilación). En `bandeja.js`, si `cfg.websockets`: conectar,
   en `connected` llamar `socketConectado(true)`, en `disconnected/unavailable` `socketConectado(false)`,
   y en el evento llamar `refrescar()`. La firma sigue decidiendo si hay que repintar.
4. Opcional: que el agente avise por un endpoint propio justo después del INSERT, para no depender
   de la impresión.

## 3. Despliegue

1. `php artisan migrate` (índice nuevo; no cambia columnas, el agente no se ve afectado).
2. Tablets: Ctrl+F5 en comandas (archivos nuevos `bandeja.js` y CSS).
3. Probar: con la bandeja abierta, crear un pedido por WhatsApp → en ≤5 s aparece la fila resaltada,
   el aviso y el sonido (después de tocar la pantalla una vez).

## 4. Pruebas

- `tests/Feature/BandejaTiempoRealTest` (6): `204` sin cambios; venta del agente por SQL directo
  cambia la firma, sale en la tabla y en `nuevas` una sola vez; venta del POS refresca sin avisar;
  eliminar producto o cobrar cambia la firma; paginación apunta a `/comandas`; requiere sesión.
- JS simulado con jsdom (16 verificaciones): parámetros, reemplazo de tabla, resaltado, avisos sin
  repetir, modal abierto, reintento tras `500`, alto con `401`. No probado en tablet real (sonido).
