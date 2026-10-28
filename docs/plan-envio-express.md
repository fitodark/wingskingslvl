# Plan Laravel — Envío express por colonia + migraciones pendientes del agente

> Proyecto: `wingskingslvl` (Laravel 5.8, PHP 7.1, MariaDB).
> Contraparte: el agente de WhatsApp (`whatsapp-agentkit`) lee y escribe en esta misma base.
> Fecha: 2026-10-27. El plan del agente vive en `whatsapp-agentkit/docs/plan-envio-express-agente.md`.

## Contexto

Además del envío gratuito del establecimiento, ahora hay un **servicio externo de envío express**
con tarifa por colonia o agencia. Reglas de negocio acordadas:

- Todas las colonias y agencias cercanas se cargan en un catálogo. Cada una indica si tiene
  **envío gratuito** o si, por distancia, **solo tiene express**. Todas tienen tarifa express.
- Envío gratuito: el tiempo sale de `configs.delivery_time_windows` (ya existe).
- Envío express: el cliente paga el monto de la tarifa de su colonia. El tiempo sale de una clave
  nueva `configs.express_delivery_time` (por ahora **30** minutos).
- El monto express es **solo informativo**: se guarda en la venta, **no se suma a `montoTotal`
  y no se imprime en ningún ticket**.
- El catálogo completo de tarifas nunca se expone al cliente (lo controla el agente).

## Orden de despliegue

1. Correr estas migraciones (y `ConfigSeeder` para la clave nueva) en producción.
2. Después desplegar el agente. El agente nuevo escribe columnas que crean estas migraciones;
   si se despliega antes, el INSERT de `ventas` falla.

---

## 1. Migraciones de sesiones pasadas (ya existen en la BD, faltan en el repo)

Estos objetos los creó el agente directo en la base. Las migraciones deben ser **idempotentes**
(con `hasTable` / `hasColumn`) para que en producción no fallen por "ya existe" y en una base nueva
sí los creen. Definición tomada de `SHOW CREATE TABLE` de producción.

### 1.1 `2026_10_27_000001_create_contador_indecision_table.php`

Misma forma que `contador_sin_intencion` (ver `2026_08_09_000002`).

```php
class CreateContadorIndecisionTable extends Migration
{
    // Intercambios seguidos en los que el cliente esquiva la pregunta de cierre.
    // Lo usa el agente (marcar_indecision); a 4 deja la plática en stand-by.
    public function up()
    {
        if (Schema::hasTable('contador_indecision')) {
            return;
        }
        Schema::create('contador_indecision', function (Blueprint $table) {
            $table->string('telefono', 50)->primary();      // chat_id de WhatsApp
            $table->unsignedInteger('contador')->default(0);
            $table->timestamp('actualizado_en')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contador_indecision');
    }
}
```

### 1.2 `2026_10_27_000002_add_location_to_clients_table.php`

```php
class AddLocationToClientsTable extends Migration
{
    // Última ubicación (pin) que el cliente compartió por WhatsApp. Referencia para el
    // repartidor; nunca sustituye la dirección escrita.
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'location_lat')) {
                $table->decimal('location_lat', 10, 7)->nullable()->after('reference');
            }
            if (!Schema::hasColumn('clients', 'location_lng')) {
                $table->decimal('location_lng', 10, 7)->nullable()->after('location_lat');
            }
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['location_lat', 'location_lng']);
        });
    }
}
```

> Nota: `numeros_bloqueados` y `contador_sin_intencion` ya tienen migración (`2026_08_09_*`).
> Las tablas `mensajes` y `webhook_procesado` viven en la SQLite local del agente, no aquí.

---

## 2. Migraciones nuevas — envío express

### 2.1 `2026_10_27_000003_create_tarifas_envio_table.php` (tabla + carga inicial)

Catálogo único de colonias y agencias. Una fila por localidad; la bandera dice si aplica gratuito.
La **carga inicial va dentro de esta misma migración** (fuente: `whatsapp-agentkit/knowledge/catalogo_colonias.md`,
128 localidades: 106 con envío gratuito y 22 solo express).

| Columna | Tipo | Nota |
|---|---|---|
| `id` | bigIncrements | |
| `colonia` | string(150), unique | Nombre tal como viene en el catálogo del negocio |
| `tipo` | string(20), default `colonia` | `colonia` o `agencia` (agencia si el nombre empieza con "Agencia") |
| `envio_gratuito` | boolean, default 1 | 1 = con envío gratuito. 0 = sin alcance, solo express |
| `monto_express` | decimal(8,2), **nullable** | Tarifa del proveedor externo. NULL = sin tarifa express cargada |
| `activo` | boolean, default 1 | 0 = se oculta al agente sin borrar historial |
| `created_at` / `updated_at` | timestamps | |

> `monto_express` es nullable porque el catálogo inicial trae costo 0 en todas las colonias con envío
> gratuito (no hay tarifa express para ellas). Se cargan como NULL, no como 0, para no ofrecer un
> express "gratis". Cuando el negocio tenga esas tarifas se capturan desde la pantalla de admin.

```php
<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTarifasEnvioTable extends Migration
{
    /**
     * Catálogo de colonias/agencias para el envío a domicilio del agente de WhatsApp.
     * envio_gratuito = 1: se ofrece envío gratuito. 0: por distancia solo hay envío express.
     * monto_express: tarifa del servicio externo (informativa, no entra al ticket).
     */
    public function up()
    {
        Schema::create('tarifas_envio', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('colonia', 150)->unique();
            $table->string('tipo', 20)->default('colonia');
            $table->boolean('envio_gratuito')->default(true);
            $table->decimal('monto_express', 8, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Carga inicial: [colonia, tipo, envio_gratuito, monto_express]
        $catalogo = [
            ['Acatlima', 'colonia', false, 45.00],
            ['Agencia Municipal Rancho Solano', 'agencia', true, null],
            ['Agua Dulce', 'colonia', false, 50.00],
            ['Ahuehuetitlan de González', 'colonia', true, null],
            ['Altavista Juárez', 'colonia', true, null],
            ['Antonio de León', 'colonia', true, null],
            ['Antonio de León Fovissste', 'colonia', true, null],
            ['Antonio de León Fovissste 2da. Sección', 'colonia', true, null],
            ['Antonio de León Fovissste 3ra. Sección', 'colonia', true, null],
            ['Aviación 1a Secc', 'colonia', true, null],
            ['Aviación 2a. Secc', 'colonia', true, null],
            ['Aviación Poniente', 'colonia', true, null],
            ['Barrio Guadalupe Victoria', 'colonia', true, null],
            ['Bella Vista', 'colonia', true, null],
            ['Buenavista', 'colonia', true, null],
            ['Chapultepec', 'colonia', true, null],
            ['Colonia Juquilita', 'colonia', true, null],
            ['Cuauhtémoc', 'colonia', true, null],
            ['Del Carmen', 'colonia', true, null],
            ['Del Maestro 1a Sección', 'colonia', true, null],
            ['Del Maestro 2a Sección', 'colonia', true, null],
            ['Del Valle', 'colonia', true, null],
            ['Dolores', 'colonia', true, null],
            ['El Calvario', 'colonia', true, null],
            ['El Carmen II', 'colonia', true, null],
            ['El Castillo', 'colonia', true, null],
            ['El Girón', 'colonia', true, null],
            ['El Mirador', 'colonia', false, 30.00],
            ['El Molino', 'colonia', true, null],
            ['El Palmar', 'colonia', true, null],
            ['El Rincón', 'colonia', true, null],
            ['El Rosario', 'colonia', false, 40.00],
            ['El Rosario 2da. Sección', 'colonia', false, 40.00],
            ['El Tanque', 'colonia', true, null],
            ['El Tatoto (Palo de Flor)', 'colonia', true, null],
            ['El Zapote (Rancho el Zapote Juquilita)', 'colonia', true, null],
            ['Esquipulas', 'colonia', true, null],
            ['Fidepal', 'colonia', true, null],
            ['FIOB', 'colonia', true, null],
            ['Fraccionamiento la Estancia Tercera Sección', 'colonia', true, null],
            ['Hacienda el Rosario', 'colonia', true, null],
            ['Héroes de La Independencia', 'colonia', true, null],
            ['Heroica Ciudad de Huajuapan de León Centro', 'colonia', true, null],
            ['Insurgentes', 'colonia', true, null],
            ['Jardines Del Sur', 'colonia', true, null],
            ['José López Alavez', 'colonia', true, null],
            ['La Antorcha', 'colonia', true, null],
            ['La Ciénega', 'colonia', true, null],
            ['La Ermita', 'colonia', true, null],
            ['La Esperanza', 'colonia', true, null],
            ['La Estancia', 'colonia', false, 45.00],
            ['La Joya', 'colonia', true, null],
            ['La Junta', 'colonia', false, 45.00],
            ['La Merced', 'colonia', true, null],
            ['La Providencia', 'colonia', true, null],
            ['Las Animas', 'colonia', true, null],
            ['La SARH', 'colonia', true, null],
            ['Las Campanas', 'colonia', true, null],
            ['Las Flores', 'colonia', true, null],
            ['Las Huertas', 'colonia', true, null],
            ['Las Lomas', 'colonia', true, null],
            ['La Soledad', 'colonia', true, null],
            ['Las Palmas', 'colonia', false, 50.00],
            ['Las Peñitas', 'colonia', true, null],
            ['Lázaro Cárdenas', 'colonia', false, 45.00],
            ['Lázaro Cárdenas 1ra. Sección', 'colonia', false, 45.00],
            ['Lázaro Cárdenas 2da Sección', 'colonia', false, 45.00],
            ['Lindavista', 'colonia', true, null],
            ['Llano Grande', 'colonia', true, null],
            ['Los Álamos 1a. Sección', 'colonia', true, null],
            ['Los Álamos 2da. Sección', 'colonia', true, null],
            ['Los Naranjos', 'colonia', false, 50.00],
            ['Los Pinos 2a. Sección', 'colonia', false, 40.00],
            ['Los Presidentes', 'colonia', true, null],
            ['Los Presidentes 2da Sección', 'colonia', true, null],
            ['Magdalena Tetaltepec', 'colonia', true, null],
            ['Militar', 'colonia', true, null],
            ['Mixteca de Cárdenas Infonavit', 'colonia', true, null],
            ['Monte Alban', 'colonia', true, null],
            ['Morelos', 'colonia', false, 30.00],
            ['Niños Héroes', 'colonia', true, null],
            ['Núcleo Rural Universitario', 'colonia', true, null],
            ['Ojo de Agua', 'colonia', true, null],
            ['Pedregal de la Mixteca', 'colonia', true, null],
            ['Puerta Del Sol', 'colonia', true, null],
            ['Rancho Burgoa', 'colonia', true, null],
            ['Rancho Jesús', 'colonia', true, null],
            ['Rancho Ramírez', 'colonia', true, null],
            ['Rancho Reyes', 'colonia', true, null],
            ['Reforma 1a. Sección', 'colonia', true, null],
            ['Reforma 2a. Sección', 'colonia', true, null],
            ['Reforma Agraria', 'colonia', true, null],
            ['Río Balsas', 'colonia', true, null],
            ['Salto del Agua 1a. Sección', 'colonia', true, null],
            ['Salto del Agua 2a. Sección', 'colonia', true, null],
            ['San Antonio', 'colonia', true, null],
            ['San Diego', 'colonia', true, null],
            ['San Isidro Oriente', 'colonia', true, null],
            ['San Isidro Poniente', 'colonia', true, null],
            ['San Mateo', 'colonia', true, null],
            ['San Miguel', 'colonia', true, null],
            ['San Miguel Papalutla', 'colonia', true, null],
            ['San Nicolás Tabernillas', 'colonia', true, null],
            ['San Pedro de los Pinos', 'colonia', true, null],
            ['San Pedro Yodoyuxi', 'colonia', true, null],
            ['San Rafael 1 Sección', 'colonia', false, 30.00],
            ['San Rafael 2 Sección', 'colonia', false, 30.00],
            ['Santa Cruz', 'colonia', true, null],
            ['Santa Fé', 'colonia', true, null],
            ['Santa Isabel', 'colonia', true, null],
            ['Santa Maria Ayu', 'colonia', true, null],
            ['Santa María Xochixtlapilco', 'colonia', false, 45.00],
            ['Santa Rosa 1a. Sección', 'colonia', true, null],
            ['Santa Rosa 2a. Sección', 'colonia', true, null],
            ['Santa Teresa', 'colonia', true, null],
            ['Santiago Chilixtlahuaca', 'colonia', true, null],
            ['Señor de los Corazones', 'colonia', true, null],
            ['Sinahi 1a. Sección', 'colonia', false, 45.00],
            ['Sinahi 2a. Sección', 'colonia', false, 45.00],
            ['Tabaco', 'colonia', true, null],
            ['Tepeyac', 'colonia', true, null],
            ['Valerio Trujano', 'colonia', true, null],
            ['Villa de Los Ángeles', 'colonia', true, null],
            ['Villa Universitaria', 'colonia', true, null],
            ['Vista Hermosa', 'colonia', false, 40.00],
            ['Yuxichi', 'colonia', true, null],
            ['San Angel', 'colonia', false, 40.00],
            ['Volcanes', 'colonia', false, 40.00],
        ];

        $ahora = now();
        $filas = [];
        foreach ($catalogo as list($colonia, $tipo, $gratuito, $monto)) {
            $filas[] = [
                'colonia' => $colonia,
                'tipo' => $tipo,
                'envio_gratuito' => $gratuito,
                'monto_express' => $monto,
                'activo' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ];
        }
        DB::table('tarifas_envio')->insert($filas);
    }

    public function down()
    {
        Schema::dropIfExists('tarifas_envio');
    }
}
```

### 2.2 `2026_10_27_000004_add_envio_to_ventas_table.php`

Todas nullable: ventas del POS y pedidos para recoger las dejan en NULL.

```php
Schema::table('ventas', function (Blueprint $table) {
    // 1 = envío gratuito, 2 = envío express. NULL = no es a domicilio o venta del POS.
    $table->unsignedTinyInteger('tipo_envio')->nullable()->after('direccion_envio');
    // Monto express aceptado por el cliente. Informativo: NO entra a montoTotal ni al ticket.
    $table->decimal('monto_envio_express', 8, 2)->nullable()->after('tipo_envio');
    // Colonia contra la que se consultó la tarifa (texto, por si la tarifa cambia después).
    $table->string('colonia_envio', 150)->nullable()->after('monto_envio_express');
});
```

`down()`: `dropColumn(['tipo_envio', 'monto_envio_express', 'colonia_envio'])`.

### 2.3 `2026_10_27_000005_add_colonia_to_clients_table.php`

```php
Schema::table('clients', function (Blueprint $table) {
    $table->string('colonia', 150)->nullable()->after('address');
});
```

Texto libre (no FK) para no romper clientes existentes. El agente lo llena al dar de alta;
para clientes antiguos intenta detectarlo dentro de `address`.

---

## 3. Configuración y seeders

### 3.1 Clave nueva en `ConfigSeeder`

```php
// Tiempo estimado del envío express (minutos). Se ajusta después desde BD.
Config::updateOrCreate(
    ['key' => 'express_delivery_time'],
    ['value' => '30', 'order' => '1']
);
```

Usar `updateOrCreate` con solo `key` en el primer arreglo (es clave de un solo valor);
así cambiar el valor no duplica la fila.

### 3.2 Catálogo de colonias

Ya no hace falta seeder: la carga inicial vive en la migración 2.1. Cambios posteriores
(colonias nuevas, tarifas) se hacen desde la pantalla de admin (sección 5).

---

## 4. Modelos

- `app/TarifaEnvio.php`: `$table = 'tarifas_envio'`, `$fillable` con todas las columnas,
  `$casts = ['envio_gratuito' => 'boolean', 'activo' => 'boolean', 'monto_express' => 'float']`.
  Scope `activas()`.
- `app/Venta.php`: agregar `tipo_envio`, `monto_envio_express`, `colonia_envio` a `$fillable`.
  Helper `esEnvioExpress()` (`tipo_envio == 2`).
- `app/Client.php`: agregar `colonia` (y `location_lat`, `location_lng` si se usan) a `$fillable`.

---

## 5. Pantalla de administración "Tarifas de envío" (solo admin)

Para mantener el catálogo sin tocar la base.

- Ruta en el grupo `role:admin` de `routes/web.php`: `'tarifasenvio' => 'Puntoventa\TarifaEnvioController'`.
- `TarifaEnvioController`: `index` (tabla con buscador por colonia), `create`/`store`, `edit`/`update`,
  y en lugar de `destroy` un cambio de `activo`.
- Validación: `colonia` requerida y única; `tipo` en `colonia,agencia`; `envio_gratuito` booleano;
  `monto_express` numérico > 0, **obligatorio si `envio_gratuito = 0`** y opcional (vacío = NULL) si es gratuito.
- Enlace en el menú junto a "Catálogos".

## 6. Bandeja de comandas (pantalla, no ticket)

El equipo tiene que saber que un pedido va por express para pedir el servicio externo.

- `resources/views/puntoventa/comandas/ventas.blade.php`: en las tarjetas con `venta_agente`,
  junto a la dirección, mostrar una etiqueta **"Envío express · $monto · colonia"** cuando
  `tipo_envio == 2`, y "Envío gratuito" cuando `tipo_envio == 1`.
- `clientSelection.blade.php`: mismo dato junto a "Dirección de envío capturada por WhatsApp".

## 7. Lo que NO cambia

- `app/Traits/PrintSales.php` (tickets de venta, cocina y barra): **no** imprimir `monto_envio_express`
  ni `tipo_envio`. Verificar que nada de lo nuevo entre a totales impresos.
- `montoTotal`, `cantidadRecibida` y cortes de caja no incluyen el monto express.

## 8. Formulario de clientes (opcional, recomendado)

En alta/edición de cliente (`ClientController@addClient` / `updateClient` y su vista), agregar el campo
`colonia` con `<datalist>` de `tarifas_envio` activas. Así los clientes capturados en el POS también
tienen colonia y el agente no tiene que preguntarla.

## Checklist de entrega

- [ ] 5 migraciones (2 de sesiones pasadas idempotentes, 3 nuevas) corren en una BD limpia y en una copia de producción sin error.
- [ ] `express_delivery_time = 30` en `configs`.
- [ ] `tarifas_envio` con 128 filas después de migrar (106 gratuito con monto NULL, 22 express con monto).
- [ ] CRUD de tarifas accesible solo para admin.
- [ ] Bandeja muestra el tipo de envío; tickets sin cambios.
