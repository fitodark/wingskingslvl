# Propuesta v1 — Cola de prioridad para cocina (pantalla en tiempo real)

> Proyecto: `wingskingslvl`. Fecha: 2026-10-27. Estado: **propuesta**, faltan las decisiones del final.

## Qué pide el cliente

1. Calcular la prioridad de cada comanda, sea en el local o a domicilio.
2. Que el orden se ajuste solo. Cocina solo atiende lo que está **arriba**.
3. Una pantalla para cocina que se actualice sola (websockets).
4. Una fórmula que hoy funcione con los datos que ya tenemos y que después acepte distancia, cantidad, etc.

## Cómo funciona hoy (lo que condiciona el diseño)

- Cocina **no tiene pantalla**. Recibe tickets impresos cada vez que se *comanda* una ronda (`VentaService::comandar`).
- Una venta puede tener **varias rondas** (`ventasproductos.order`). Por ejemplo, una mesa pide alitas y luego pide más. Lo que cocina prepara es la **ronda**, no la venta completa.
- Nadie registra cuándo cocina terminó algo. Sin ese dato **la cola nunca avanza**.
- El agente de WhatsApp inserta directo en MySQL y luego llama a `POST /api/agente/ventas/{folio}/imprimir-orden`.
- Stack: Laravel 5.8, PHP 7.4, WAMP, `QUEUE_CONNECTION=sync`, `BROADCAST_DRIVER=log`. No hay nada de websockets.

## 1. Unidad de la cola: "orden de cocina"

Tabla nueva `ordenes_cocina`. Se crea un renglón por cada ronda que tenga productos de cocina (alitas o cocina, `Product::TIPOS_COCINA`).

| Campo | Uso |
|---|---|
| `venta_id`, `ronda` | Qué venta y qué ronda (único por par) |
| `estatus` | `pendiente` → `preparando` → `lista` (o `cancelada`) |
| `comandada_at` | Cuándo se mandó a cocina (arranca el reloj) |
| `iniciada_at`, `lista_at` | Cuándo cocina la tomó y cuándo la terminó |
| `ajuste_min` | Ajuste manual del encargado (+/− minutos), por defecto 0 |

Se crea en dos lugares:
- `VentaService::comandar`, **aunque la impresión esté apagada**.
- `AgenteController::imprimirOrden`.

Barrido de respaldo: cada vez que se consulta la cola, se agregan las rondas ya comandadas (`delete_flag = 0`) de ventas abiertas que no tengan orden. Así no se pierden las ventas del agente si nunca llamó a imprimir.

Si la venta se cancela o se eliminan todos los productos de cocina de la ronda, la orden pasa a `cancelada`.

**Bonus:** con `comandada_at` y `lista_at` medimos los tiempos reales de preparación. Esos datos sirven para calibrar la fórmula en la v2.

## 2. Fórmula: "minutos de holgura" (primero la que vence antes)

En lugar de sumar puntos abstractos, cada orden tiene una **hora límite para estar lista**. La cola se ordena por **holgura**: cuántos minutos quedan antes de tener que *empezar* a prepararla (hora límite − ahora − preparación). La de menor holgura va primero; las que ya están "Preparando" se quedan arriba.

```
hora_entrega   = comandada_at + promesa(tipo, envío)        ← lo que se le prometió al cliente
lista_a_mas_tardar = hora_entrega − traslado(envío) + ajuste_min
holgura        = lista_a_mas_tardar − ahora − preparación(ronda)
orden de la cola: holgura ASC, empate → comandada_at ASC (FIFO)
```

Por qué así:
- Se explica fácil en pantalla: *"Empezar en 6 min · lista a más tardar 12:48"*.
- Página para QA con prototipo de pantalla y hardware: https://claude.ai/artifact/GpMRsQFcuwHE4rA1MBfPGP
- Todo está en **minutos**. Cada parámetro nuevo se suma sin rehacer la fórmula:
  - Distancia → `traslado`
  - Cantidad → `preparación`
- Una comanda de mesa que lleva mucho esperando sube sola, porque su holgura baja minuto a minuto. Así no se queda abajo para siempre.
- Una comanda a domicilio sube porque necesita salir antes para recorrer el trayecto.

### Parámetros v1 (en `configs`, editables sin tocar código)

| Clave | Valor inicial sugerido | Nota |
|---|---|---|
| `cocina_promesa_local` | 20 min | Mesa |
| `cocina_promesa_llevar` | 20 min | Pasan a recoger |
| `cocina_promesa_domicilio` | 45 min | Lo que se le dice al cliente |
| `cocina_traslado_gratuito` | 15 min | Repartidor propio |
| `cocina_traslado_express` | 10 min | Servicio externo: solo esperar a que llegue. Ya existe `express_delivery_time` (30) para la entrega total |
| `cocina_prep_base` | 10 min | Preparación mínima de una ronda |
| `cocina_prep_por_10_piezas` | 0 min | **Apagado en v1.** Se enciende cuando midamos tiempos reales |
| `cocina_rojo` / `cocina_amarillo` | 0 / 10 min | Colores del semáforo según la holgura |

### Ejemplo a las 12:00

| Folio | Tipo | Comandada | Hora límite (lista) | Prep | Holgura | Posición |
|---|---|---|---|---|---|---|
| 812 | Domicilio gratuito | 11:50 | 11:50+45−15 = **12:20** | 10 | 10 | **1** |
| 810 | Mesa 3 | 11:45 | 11:45+20 = **12:05** | 10 | −5 (rojo) | … |
| 813 | Para llevar | 11:58 | 11:58+20 = **12:18** | 10 | 8 | … |
| 814 | Domicilio express | 11:59 | 11:59+45−10 = **12:34** | 10 | 24 | … |

Orden resultante: **810 (−5) → 813 (8) → 812 (10) → 814 (24)**. La mesa 810 va primero porque ya está atrasada. El domicilio 812 va antes que el 814 aunque el 814 sea express.

### Cómo crecerá en la v2 (sin cambiar la fórmula)
- **Distancia:** `traslado` por colonia, con una columna `minutos_traslado` en `tarifas_envio`. Después se puede calcular con `clients.location_lat/lng` (el pin que ya guarda el agente).
- **Cantidad:** `preparación = base + piezas/10 × factor`. Ya tenemos `products.piezas` y `cantidad`, y calibramos el factor con los tiempos medidos.
- **Carga de cocina:** si hay muchas órdenes abiertas, sube la promesa al cliente. El agente también puede leer este dato para dar un tiempo realista.

## 3. Pantalla de cocina `/cocina`

- Pantalla completa para tablet o monitor, con letra grande y sin menú.
- Una **tarjeta por orden**, ya ordenadas. La primera se ve más grande.
- Cada tarjeta muestra:
  - Posición y folio.
  - Tipo (Mesa 3 / Para llevar / Domicilio) con su etiqueta de envío.
  - Productos de cocina **de esa ronda**, con sabores.
  - "Lleva 14 min · lista a las 12:20 · faltan 6 min", con semáforo verde/amarillo/rojo.
- Botones grandes: **Preparando** (opcional) y **Lista**. "Lista" saca la orden de la cola.
- El encargado puede usar **Subir / Bajar**, que cambia `ajuste_min` en ±5 min. No se mueven posiciones a mano: el ajuste entra a la fórmula y queda registrado.
- La bandeja `/comandas` muestra una etiqueta "Cocina #3" en cada venta con orden pendiente.
- Rol nuevo `cocina` que solo ve `/cocina`. Admin y encargado también pueden entrar.
- Los tickets impresos **se siguen imprimiendo** en v1. Así, si falla la pantalla, cocina sigue trabajando.

## 4. Tiempo real (websockets)

- **Servidor:** `beyondcode/laravel-websockets` 1.4. Es compatible con Laravel 5.8 y PHP 7.4 (ya lo verifiqué en composer). Corre en PHP dentro del mismo servidor (`php artisan websockets:serve`), no depende de internet y no cuesta. En Windows se deja como servicio con NSSM.
  - Habla el protocolo de Pusher. Cuando subamos Laravel (ver `docs/upgrade-laravel.md`), pasar a Pusher en la nube o a Laravel Reverb es solo un cambio de configuración.
- **Evento:** `ColaCocinaCambio` (`ShouldBroadcastNow`, porque no hay worker de colas). Va en un canal público `cocina` **sin datos**, solo el aviso. La pantalla recibe el aviso y pide `GET /cocina/cola` con su sesión normal. Así los datos de los clientes nunca viajan por el socket y no hace falta autenticar canales.
- **Se dispara al:** comandar, al imprimir desde el agente, al marcar preparando/lista, al subir/bajar, al cancelar la venta y al eliminar productos ya comandados.
- **Si el socket falla:** el aviso va en `try/catch`. Una falla del websocket **nunca** debe impedir comandar o cobrar.
- **Respaldo en la pantalla:**
  - Refresca la cola cada 30 s aunque no lleguen avisos.
  - Recalcula los minutos en el navegador cada 30 s, porque la holgura cambia con el tiempo aunque nadie toque nada.
  - Muestra un indicador "En vivo / Reconectando".
- **Cliente:** `pusher-js` y `laravel-echo` desde CDN, solo en la vista de cocina. No hace falta tocar el build de mix.

## 5. Pruebas

- Unitarias de la fórmula, con el ejemplo de la tabla de arriba y con la hora fijada (`Carbon::setTestNow`).
- Feature:
  - Comandar crea la orden aunque la impresión esté apagada.
  - El agente crea la orden.
  - El barrido recoge ventas sin orden.
  - "Lista" saca la orden de la cola.
  - Cancelar la venta cancela la orden.
  - Una ronda con solo bebidas no crea orden.
  - Si el websocket falla, la venta se comanda igual.

## Estimado v1

| Bloque | Días |
|---|---|
| Tabla, servicio de cola, fórmula, barrido y pruebas | 1.5 |
| Pantalla `/cocina`, botones y etiqueta en la bandeja | 1.5 |
| Websockets: paquete, evento, servicio en Windows y respaldo | 1 |
| QA en el local con tablet real y ajuste de parámetros | 1 |
| **Total** | **~5** |

## Decisiones que necesitamos del cliente

1. **¿Quién marca "Lista" en cocina y con qué equipo** (tablet, monitor con touch, PC)? Sin esto la cola no avanza. Es el punto más importante.
2. **Tiempos prometidos** por tipo (mesa, para llevar, domicilio) y el traslado promedio del repartidor.
3. **¿La barra también tendrá cola?** La propuesta es solo cocina en v1. La barra se puede agregar después con el mismo mecanismo.
4. **¿El servidor está en el local o en la nube?** De eso depende dónde corre el websocket. La recomendación es en el mismo servidor.
5. **¿Quién puede subir o bajar una orden a mano?** La propuesta es el encargado.
6. **¿Los tickets impresos se quedan?** La propuesta es mantenerlos en v1 y decidir después.
