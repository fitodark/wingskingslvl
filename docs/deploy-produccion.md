# Pase a producción — rama `wingskings-2.5`

Lista para subir a producción los cambios de las ramas 2.4 y 2.5: envío express, teléfono de quien recoge,
leyenda SERVICIO EXPRESS, bandeja en tiempo real, selector de colonia y "Para llevar" limpia el cliente.
Seguir los pasos **en orden**. El paso 3 (migraciones) va **antes** de actualizar el agente de WhatsApp,
porque el agente escribe columnas nuevas de `ventas` y `clients`: si se actualiza primero, su INSERT falla.

No hay cambios en `composer.json` ni en `composer.lock`, así que **no** hace falta `composer install`.
No hay compilación de JS/CSS (los archivos de `public/` se sirven tal cual).

---

## 0. Antes de empezar

- [ ] Elegir un horario sin servicio (ninguna comanda abierta). Si hay comandas abiertas, se conservan, pero
      conviene que nadie esté capturando mientras se actualiza.
- [ ] Respaldar la base de datos:
      ```
      mysqldump -u USUARIO -p --single-transaction --routines wingskings > wingskings_AAAA-MM-DD.sql
      ```
- [ ] Respaldar el `.env` de producción (`copy .env .env.respaldo`).
- [ ] Anotar el commit actual en producción (`git log -1 --oneline`) para poder volver a él.

## 1. Bajar el código

```
git fetch origin
git checkout wingskings-2.5        (o master, si ya se hizo el merge del PR)
git pull
git log -1 --oneline               → debe mostrar 1183b2a o posterior
```

- [ ] Verificar que exista `public/wingtwo.jpg` (logo del ticket; se restauró en ecb60ef).

## 2. Ajustar el `.env` de producción

Comparar contra `.env.example` (las líneas marcadas `[PROD]`). Claves nuevas o que cambian:

| Clave | Valor | Para qué |
|---|---|---|
| `APP_TIMEZONE` | `America/Regina` | Corrige la hora +1 en tickets (PHP 7.4 trae una tzdb vieja con horario de verano). |
| `AGENTE_TOKEN` | el mismo que tiene el agente | Sin él, el agente no puede imprimir (`/api/agente/...` responde 401). Si ya existe, no cambiarlo. |
| `BANDEJA_INTERVALO` | `5` | Segundos entre refrescos de la bandeja. Opcional (5 por defecto). |
| `BANDEJA_WEBSOCKETS` | `false` | Dejar en `false`: los websockets todavía no se instalan. |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` | Si no lo estaban ya. |

## 3. Migraciones

```
php artisan migrate:status          → revisar cuáles salen "No"
php artisan migrate --force
```

Migraciones de estas ramas (las que ya estén aplicadas se saltan solas):

| Migración | Qué hace | Nota |
|---|---|---|
| `2026_10_20_000001` | `ventas.nombre_cuenta` | |
| `2026_10_24_000001` | `payment_type` nulo por defecto | |
| `2026_10_24_000002` | Rol mesero al usuario 6 (agente) | |
| `2026_10_24_000003` | `products.piezas` | |
| `2026_10_24_000004` | Tablas a InnoDB | Ya se corrió en prod (23 tablas); si sale "No", tarda unos segundos por tabla. |
| `2026_10_27_000001` / `000002` | `contador_indecision`, `clients.location_lat/lng` | Idempotentes: no fallan si ya existen (el agente ya las había creado). |
| `2026_10_27_000003` | Tabla `tarifas_envio` con 128 colonias | |
| `2026_10_27_000004` | `ventas.tipo_envio/monto_envio_express/colonia_envio` | Necesaria para el agente. |
| `2026_10_27_000005` | `clients.colonia` | Necesaria para el agente. |
| `2026_10_27_000006` | `configs.express_delivery_time = 30` si no existe | **No** correr `ConfigSeeder` en prod: duplica claves. |
| `2026_10_30_000001` | `ventas.telefono_recoge` | |
| `2026_11_10_000001` | Índice `ventas(estatus, activo)` | Para el refresco de la bandeja. |

- [ ] `php artisan migrate:status` ya no muestra ninguna en "No".

## 4. Limpiar caché

```
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

(Si en producción se usa `config:cache` / `route:cache`, volver a generarlos después.)

## 5. Actualizar el agente de WhatsApp

Solo después del paso 3. Seguir `whatsapp-agentkit/docs/plan-envio-express-agente.md`.

- [ ] El agente usa el mismo `AGENTE_TOKEN` que el `.env`.

## 6. Tablets y equipos

- [ ] **Ctrl+F5** (o borrar caché del navegador) en cada tablet y en la caja: cambiaron `comandas.js`,
      `wk-common.js`, `bandeja.js` y `mystyle.css`, que no tienen versión en la URL.

## 7. Verificación (5–10 minutos)

| # | Prueba | Resultado esperado |
|---|---|---|
| 1 | Entrar con un mesero y abrir Comandas | Carga sin errores; arriba aparece el botón "Activar sonido". |
| 2 | Crear comanda de Mesa y comandar una bebida | Imprime en barra; la hora del ticket coincide con el reloj. |
| 3 | Para llevar sin cliente: nombre + teléfono de 10 dígitos | Deja avanzar; ticket con "Tel. recoge". Sin teléfono no deja avanzar. |
| 4 | Para llevar con cliente registrado, sin teléfono | Deja avanzar (el teléfono es opcional con cliente). |
| 5 | Domicilio con cliente sin colonia | Pide elegir colonia; en la tablet se despliega la lista al tocar el campo. |
| 6 | Domicilio en colonia solo express (p. ej. Acatlima) | Aviso "Cliente acepta / declina"; bandeja muestra "Envío express · $45.00 · Acatlima". |
| 7 | Comandar esa venta express | Ticket de cocina/barra con "SERVICIO EXPRESS" centrado y en negritas, sin montos. |
| 8 | Dos tablets con Comandas abiertas; crear una venta en una | En la otra aparece sola en ≤ 5 s. |
| 9 | Pedido de prueba por WhatsApp | Aparece en la bandeja en ≤ 5 s con aviso y sonido (si se tocó "Activar sonido"); se imprime. |
| 10 | Admin → Tarifas de envío | Lista las 128 colonias. |

## 8. Si algo sale mal

- **Error 500 en todas las pantallas:** revisar `storage/logs/laravel.log`; normalmente es el `.env` o la caché
  (repetir el paso 4).
- **El agente no puede crear ventas:** falta alguna migración del paso 3 → `php artisan migrate:status`.
- **El agente no imprime (401):** `AGENTE_TOKEN` distinto entre el `.env` y el agente.
- **Volver a la versión anterior:** `git checkout <commit anotado en el paso 0>` + paso 4.
  No hace falta deshacer las migraciones: solo agregan columnas, una tabla y un índice, y la versión
  anterior las ignora. Restaurar el respaldo de la base solo si se dañaron datos.

## Pendientes conocidos después del pase

- Prueba de la bandeja en tiempo real con QA en producción (pendiente).
- Prioridad de cocina: en pausa hasta definir los parámetros de prioridad (`docs/plan-prioridad-cocina.md`).
