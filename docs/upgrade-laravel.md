# Plan de actualización de Laravel

Estado al 2026-10-24: **Laravel 5.8.38** (sin soporte desde 2020) sobre **PHP 7.4** (WAMP) y **MariaDB 10.6**.

## Qué limita hasta dónde se puede llegar

| Laravel | PHP mínimo | Soporte de seguridad | ¿Llega con PHP 7.4? |
|---|---|---|---|
| 6 (LTS) | 7.2 | terminó 2022 | Sí |
| 7 | 7.2.5 | terminó 2021 | Sí |
| 8 | 7.3 | terminó 2023 | Sí (máximo con PHP 7.4) |
| 9 | 8.0 | terminó 2024 | No |
| 10 | 8.1 | terminó 2025 | No |
| 11 / 12 | 8.2 | vigentes | No |

Laravel 8 sigue sin soporte; solo con PHP 8.2 o superior se llega a una versión que todavía recibe parches. **Lo primero es confirmar la versión de PHP del servidor de producción** (`php -v` en ese equipo) y si se puede instalar PHP 8.2 ahí. WAMP permite tener varias versiones de PHP y cambiar entre ellas.

## Antes de empezar (requisitos)

1. **Tablas en InnoDB**: correr la migración `2026_10_24_000004_convert_tables_to_innodb` (ver abajo por qué).
2. **Tests verdes**: `vendor/bin/phpunit` (usa la base `wingskings_test`, nunca la real). Cada salto de versión debe terminar con los tests en verde y una prueba manual del flujo: abrir corte → venta con alitas → comandar → eliminar con PIN → cobrar → cerrar corte.
3. Una rama por salto (`upgrade/laravel-6`, `upgrade/laravel-7`, …) y respaldo de la base antes de desplegar cada uno.
4. Mientras dure el upgrade, no mezclar otros cambios funcionales en esas ramas.

## Salto 1: 5.8 → 6 (LTS)

Cambios en `composer.json`: `laravel/framework ^6.20`, `laravel/tinker ^2.0`, `nunomaduro/collision ^3.0`, `phpunit/phpunit ^8.5`; quitar `beyondcode/laravel-dump-server` (ya no se usa así).

Lo que afecta a este código:
- Helpers `str_*` / `array_*` eliminados. Hay uno en uso: `str_random(25)` en `app/Traits/UploadTrait.php` → `Str::random(25)`. (Los de `CatalogController` están comentados.)
- Laravel 6 ya no trae `Input`; aquí no se usa.
- `Carbon` 2 ya está instalado (2.57); sin cambios.
- PHPUnit 8: los `setUp()` de los tests ya declaran `: void`.

Riesgo: **bajo**.

## Salto 2: 6 → 7

`laravel/framework ^7.30`, `nunomaduro/collision ^4.1`, `phpunit/phpunit ^8.5|^9.0`.

- `app/Exceptions/Handler.php`: `report(Exception $e)` y `render($request, Exception $e)` pasan a `Throwable`.
- Symfony 5: revisar que `mike42/escpos-php` siga instalando (no depende de Symfony; esperado sin cambios).
- Fechas en JSON se serializan en ISO-8601 con zona. Revisar respuestas JSON del agente/AJAX que muestren fechas (hoy no se mandan fechas en esas respuestas).

Riesgo: **bajo**.

## Salto 3: 7 → 8 (máximo con PHP 7.4)

`laravel/framework ^8.83`, `nunomaduro/collision ^5.0`, `fakerphp/faker` en lugar de `fzaninotto/faker` (abandonado), `phpunit/phpunit ^9.3`.

- **Paginación**: Laravel 8 usa Tailwind por defecto. Agregar `Paginator::useBootstrap();` en `AppServiceProvider::boot()`; hay 11 vistas con `->links()` que se verían rotas sin esto.
- Seeders: `database/seeds` → `database/seeders` con namespace `Database\Seeders` (ajustar `composer.json` autoload).
- Factories basadas en clases: el único es `UserFactory`, y los tests no lo usan (crean datos con los ayudantes de `tests/TestCase.php`). Se puede borrar o reescribir.
- Las rutas usan `'Controlador@metodo'` con el prefijo `$namespace` de `RouteServiceProvider`; **conservar** esa propiedad (los proyectos actualizados la mantienen).

Riesgo: **medio** (paginación y seeders son los puntos visibles).

## Salto 4 en adelante: requiere PHP 8

Si producción puede pasar a PHP 8.2:

- **9** (PHP 8.0): quitar `fideloper/proxy` (Laravel ya trae `TrustProxies`), `doctrine/dbal ^3`, Flysystem 3 (`UploadTrait::storeAs` sigue funcionando; revisar el disco configurado).
- **10** (PHP 8.1): `DB::raw()` ya no se puede usar como texto. Hay 5 lugares con `DB::select(DB::raw("..."))` en `app/Traits/ComandasDataLibrary.php` → cambiar a `DB::select("...", $bindings)`.
- **11** (PHP 8.2): la estructura nueva (sin `Kernel`) es opcional; no es necesario migrarla.

Cambios de PHP 8 que tocan reglas de negocio:
- Las comparaciones entre número y texto cambian (`0 == "abc"` ahora es falso). Las reglas de venta ya usan constantes y métodos (`estaAbierta()`, `estaComandado()`…), lo que reduce el riesgo, pero hay que correr los tests en PHP 8 antes de desplegar.
- `mike42/escpos-php` v3 funciona en PHP 8; probar impresión real (ticket, cocina y barra) en la máquina de la caja.

## Por qué InnoDB va primero

Todas las tablas estaban en MyISAM (motor por defecto del servidor de WAMP). MyISAM no tiene transacciones ni bloqueo por fila, así que `DB::transaction()` y `lockForUpdate()` no hacen nada:

- Prueba con tres cobros simultáneos de la misma venta (copia de la base): con MyISAM la venta se cerró 2 o 3 veces, con movimientos duplicados en el corte e inventario descontado de más. Con InnoDB, 1 cierre y 2 rechazos.
- En los datos reales hay 10 ventas (feb–mar 2026) con el movimiento de corte duplicado, a menos de un segundo de diferencia: $3,962.50 contados de más en esos cortes.

La conversión en una copia de la base real tardó ~7 s, sin cambios en conteos ni sumas. Bloquea cada tabla mientras se convierte: correrla con el negocio cerrado y con respaldo.
