<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Pasa a InnoDB las tablas que están en MyISAM (el motor por defecto del servidor de WAMP).
 *
 * Con MyISAM no hay transacciones ni bloqueo por fila: DB::transaction() y lockForUpdate()
 * no hacen nada, así que un doble clic en "Cobrar" podía cerrar la venta dos veces y un
 * error a la mitad del cierre dejaba la venta cerrada sin su movimiento en el corte.
 *
 * Toma unos segundos por tabla (ventasproductos es la más grande) y bloquea la tabla
 * mientras se convierte: correrla con el negocio cerrado y con respaldo previo.
 * No cambia datos ni columnas. Incluye las tablas del agente (mismo esquema).
 */
class ConvertTablesToInnodb extends Migration
{
    public function up()
    {
        $tablas = DB::select(
            "select table_name as nombre from information_schema.tables
             where table_schema = database() and table_type = 'BASE TABLE' and engine = 'MyISAM'"
        );

        foreach ($tablas as $tabla) {
            // DYNAMIC: el formato "compact" del servidor no admite índices sobre varchar(255) utf8mb4.
            DB::statement('ALTER TABLE `' . str_replace('`', '``', $tabla->nombre) . '` ENGINE=InnoDB ROW_FORMAT=DYNAMIC');
        }
    }

    public function down()
    {
        // Sin reversa: regresar a MyISAM volvería a quitar las transacciones.
    }
}
