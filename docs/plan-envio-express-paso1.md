# Plan Laravel — Envío express en el paso 1 de la comanda (captura manual)

> Proyecto: `wingskingslvl`. Fecha: 2026-10-28. Estado: **aprobado**, decisiones confirmadas al final.
> Parte de: `docs/plan-envio-express.md` (ya implementado, cambios aún sin commit).
> Alcance: comandas capturadas a mano (pedido por teléfono). El agente de WhatsApp no cambia.

## Problema

Cuando un cliente pide por teléfono, el usuario captura la comanda en el **paso 1 (Ubicación)**.
Hoy ese paso no consulta `tarifas_envio`:

1. El usuario no se entera de que la colonia del cliente **solo tiene envío express** y no puede
   avisarle del costo extra antes de tomar el pedido.
2. Las comandas manuales nunca guardan `ventas.tipo_envio`, `monto_envio_express` ni
   `colonia_envio`. La bandeja (que ya muestra "Envío express · $ · colonia") no las marca, y nadie
   sabe que hay que pedir el servicio externo.
3. `clients.colonia` es texto libre con sugerencias (`datalist`): puede quedar vacía (los ~7,500
   clientes antiguos) o con un nombre que no está en el catálogo.
4. Las comandas del agente también se abren en el paso 1. Si el usuario guarda ese paso, no debe
   borrar ni cambiar el envío que el cliente ya aceptó por WhatsApp.

## Comportamiento esperado (paso 1, tipo Domicilio)

Al elegir o cambiar el cliente, debajo de la ficha aparece un **panel de envío** según la colonia
de ese registro (cada registro de cliente es una dirección):

| Caso | Panel | ¿Siguiente habilitado? |
|---|---|---|
| Colonia con envío gratuito y **sin** tarifa express | "Envío gratuito" (verde), sin opciones | Sí |
| Colonia con envío gratuito **y** con tarifa express | Opciones: "Envío gratuito" / "Envío express · $X" | Sí, con gratuito marcado por defecto |
| Colonia **solo express** | Aviso amarillo: "Por la distancia, [colonia] solo tiene envío express: **$X adicional que el cliente le paga directo al repartidor**." Botones **"Cliente acepta"** / **"Cliente declina"** | Solo después de "Cliente acepta" |
| Declina | El aviso cambia a: "El cliente no aceptó el envío express." Botones **"Cambiar a Para llevar"** (marca el tipo 3 y conserva el cliente) y **"Cancelar comanda"** (usa el form `cancelarVentaForm` que ya existe) | No (en Domicilio) |
| Colonia vacía o fuera del catálogo | Aviso: "Esta dirección no tiene una colonia del catálogo." Campo **Colonia** con `datalist` y botón **"Guardar colonia"** que actualiza el registro del cliente y vuelve a consultar | No, hasta guardar una colonia válida |

- El resumen de la barra inferior agrega el envío: "Domicilio · Juan Pérez · Express $45".
- Mesa y Para llevar no muestran panel y guardan los tres campos en NULL.
- El monto **no** entra a `montoTotal`, a la cuenta ni a ningún ticket (regla D del plan original).

## 1. Backend

### 1.1 Servicio de tarifa (una sola fuente de verdad)
`app/Services/EnvioService.php`:
- `buscarTarifa(?string $colonia): ?TarifaEnvio` — `TarifaEnvio::activas()->where('colonia', trim($colonia))->first()`.
  La collation `utf8mb4_unicode_ci` ya ignora mayúsculas y acentos ("acatlima" = "Acatlima").
- `opciones(TarifaEnvio $t): array` — `['colonia', 'envio_gratuito', 'express_disponible' => monto !== null, 'monto_express']`.

### 1.2 Endpoint para el paso 1
- `GET /tarifaEnvio/{clientId}` → `ClientController@tarifaEnvio` (grupo `role:admin,encargado,mesero`).
- Respuesta: `{ "encontrada": true, "colonia": "Acatlima", "envio_gratuito": false, "express_disponible": true, "monto_express": 45.0 }`
  o `{ "encontrada": false, "colonia": "<lo que tenga el cliente o null>" }`.
- Registrar la ruta en `WK.rutas` (`layouts/app.blade.php`, bloque `rutas: {...}`).

### 1.3 Guardar la colonia desde el paso 1
- `POST /clienteColonia` → `ClientController@actualizarColonia` con `clientId` y `colonia`.
- Valida que la colonia exista en `tarifas_envio` activas; guarda el nombre **del catálogo**
  (no el texto escrito) en `clients.colonia` y devuelve la misma respuesta que 1.2.

### 1.4 `VentasController@update` (guardar el paso 1)
Campos nuevos del form: `envio` (`gratuito` | `express`) y `acepta_express` (`1` cuando el usuario pulsó "Cliente acepta").

Dentro de la validación `after()`, solo si `type == TIPO_DOMICILIO`:
1. Si la venta es del agente (`venta_agente = 1`), ya tiene `tipo_envio` **y** el `clientId` no cambió:
   **no tocar** los tres campos (se respeta lo que el cliente aceptó por WhatsApp; su colonia
   viene de `colonia_envio`, no de `clients.colonia`).
2. Si no, se busca la tarifa con `EnvioService` usando la colonia del cliente elegido:
   - Sin tarifa → error "La dirección del cliente no tiene una colonia del catálogo de envío."
   - Solo express y `acepta_express != 1` → error "El cliente debe aceptar el envío express para continuar."
   - `envio = express` y sin `monto_express` → error "Esta colonia no tiene tarifa express."

Al guardar:
- Domicilio: `tipo_envio` = 1 o 2, `colonia_envio` = nombre del catálogo, `monto_envio_express`
  = **monto leído de la tabla en el servidor** (nunca del formulario) si es express; NULL si es gratuito.
- Mesa o Para llevar: los tres campos en NULL.

### 1.5 Formulario de cliente (alta/edición)
- Validar `colonia` con `exists:tarifas_envio,colonia` (además de `nullable`), para que lo que se
  capture siempre se pueda tarificar. Mensaje: "Elige una colonia de la lista."
- Mantener `nullable`: no se fuerza a los clientes antiguos hasta que pidan a domicilio (ahí lo pide el paso 1).

## 2. Frontend (`clientSelection.blade.php` + `public/js/comandas.js`)

### 2.1 Vista
Dentro de `#panelCliente`, después de `#fichaCliente`, un contenedor `#panelEnvio` (oculto por defecto) con:
- `#envioMensaje` (texto del caso), radios `name="envio"` (`gratuito`/`express`), botones
  `#aceptaExpress` / `#declinaExpress`, botones `#cambiarALlevar` / cancelar
  (`<button type="submit" form="cancelarVentaForm">`), y el bloque de colonia (`input list` + `#guardarColonia`).
- `<input type="hidden" name="acepta_express" id="aceptaExpressInput">`.
- Si la venta ya trae `tipo_envio` (agente o comanda reabierta), pasar el estado inicial en
  `data-envio="{{ json_encode([...]) }}"` para pintarlo sin pedir nada al servidor.

### 2.2 Lógica (`paso1`)
- Estado nuevo: `paso1.envio = { tarifa: null, elegido: null, acepta: null, cargando: false }`.
- `consultarEnvio()`: al `elegirCliente`, al volver de "Editar" y al cargar la página con cliente,
  si el tipo es Domicilio → `$.get(WK.rutas.tarifaEnvio + '/' + cliente.id)`. Ignorar respuestas viejas
  (mismo patrón que `paso1.busqueda`).
- `pintarPaso1()`:
  - `listo` en Domicilio pasa a ser `!!cliente && envioListo()`, donde `envioListo()` exige tarifa
    encontrada y, si es solo express, `acepta === true`.
  - Resumen: agrega "· Gratuito" o "· Express $X".
  - Ayuda: "Confirma con el cliente el envío express" mientras falte la respuesta.
- "Cliente declina" → `acepta = false`, muestra las dos salidas. "Cambiar a Para llevar" →
  marca `#tipo3`, conserva el cliente y llama `pintarPaso1()`.
- "Cambiar" cliente o cambiar de tipo → reinicia `paso1.envio`.

## 3. Lo que NO cambia

- Tickets (`PrintSales.php`): sin monto express. La bandeja ya muestra la etiqueta con lo que se guarde aquí.
- Pasos 2–4 y el cierre de venta: sin cambios.
- Agente de WhatsApp: sin cambios. Sigue escribiendo los mismos campos.

## 4. Pruebas

Unitarias / feature (`tests/`):
1. `EnvioService::buscarTarifa` ignora mayúsculas y acentos.
2. `update` Domicilio con colonia solo express y sin `acepta_express` → error; con `acepta_express=1` → `tipo_envio=2` y monto de la tabla aunque el form mande otro.
3. `update` con colonia gratuita sin tarifa express y `envio=express` → error.
4. `update` Para llevar → los tres campos en NULL.
5. `update` de una venta del agente con `tipo_envio` y mismo cliente → campos intactos.
6. `actualizarColonia` rechaza una colonia fuera del catálogo y guarda el nombre del catálogo.

Manual (tablet):
- Cliente en Acatlima → aviso amarillo, Siguiente bloqueado hasta "Cliente acepta"; bandeja muestra "Envío express · $45.00 · Acatlima"; ticket sin el monto.
- "Cliente declina" → "Cambiar a Para llevar" deja la comanda como Para llevar con el mismo cliente.
- Cliente antiguo sin colonia → elegir colonia en el paso 1 la guarda en el cliente.
- Comanda del agente express reabierta en el paso 1 → conserva su etiqueta al dar Siguiente.

## Decisiones confirmadas (2026-10-28)

1. **Colonia vacía o fuera del catálogo bloquea Siguiente** en Domicilio hasta elegir una del catálogo,
   que se guarda en el cliente desde el mismo paso (sección 1.3 y caso "Colonia vacía" de la tabla).
   Contexto: 7,456 de 7,457 clientes activos no tienen colonia, así que al inicio casi todo pedido a
   domicilio de un cliente existente la pedirá una vez; después queda guardada.
2. **Gratuito con tarifa express**: gratuito marcado por defecto; el usuario cambia a express si el cliente lo pide.
3. **Sin tiempo de entrega en el paso 1** en esta entrega. Queda como mejora futura (requiere portar a
   PHP el cálculo de `delivery_time_windows` y `express_delivery_time` que hoy vive en el agente).
