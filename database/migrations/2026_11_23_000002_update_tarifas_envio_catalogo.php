<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Catálogo de tarifas de envío actualizado por el negocio (2026-11-23), copiado de
 * whatsapp-agentkit/knowledge/catalogo_colonias.md (130 colonias). Cobertura según "Envio" y "Costo":
 * - Express + costo       → solo express  (envio_gratuito = 0, monto_express = costo)
 * - Gratuito + costo > 0  → gratuito y express (envio_gratuito = 1, monto_express = costo)
 * - Gratuito + 0          → solo gratuito (envio_gratuito = 1, monto_express = NULL)
 *
 * - "Heroica Ciudad de Huajuapan de León Centro" se renombra a "Centro" (como viene en el catálogo)
 *   y se actualiza clients.colonia para que esos clientes no tengan que volver a elegir colonia.
 *   ventas.colonia_envio no se toca (es histórico).
 * - Colonias del catálogo que ya existen: se actualizan cobertura y tarifa; `activo` y `tipo` se respetan.
 *   Colonias nuevas: se agregan activas.
 * - Colonias que estén en la base y no en el catálogo (p. ej. agregadas a mano) no se tocan.
 */
class UpdateTarifasEnvioCatalogo extends Migration
{
    const CENTRO_ANTES = 'Heroica Ciudad de Huajuapan de León Centro';
    const CENTRO_AHORA = 'Centro';

    // [colonia, tipo (solo para las nuevas), envio_gratuito, monto_express]
    const CATALOGO = [
            ['Acatlima', 'colonia', false, 50.00],
            ['Agencia Municipal Rancho Solano', 'agencia', true, null],
            ['Agua Dulce', 'colonia', false, 50.00],
            ['Ahuehuetitlan de González', 'colonia', true, null],
            ['Altavista Juárez', 'colonia', true, 30.00],
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
            ['Del Carmen', 'colonia', true, 45.00],
            ['Del Maestro 1a Sección', 'colonia', true, 30.00],
            ['Del Maestro 2a Sección', 'colonia', true, 30.00],
            ['Del Valle', 'colonia', true, 35.00],
            ['Dolores', 'colonia', true, null],
            ['El Calvario', 'colonia', true, null],
            ['El Carmen II', 'colonia', true, 45.00],
            ['El Castillo', 'colonia', true, null],
            ['El Girón', 'colonia', true, null],
            ['El Mirador', 'colonia', false, 35.00],
            ['El Molino', 'colonia', true, null],
            ['El Palmar', 'colonia', true, null],
            ['El Rincón', 'colonia', true, null],
            ['El Rosario', 'colonia', false, 40.00],
            ['El Rosario 2da. Sección', 'colonia', false, 40.00],
            ['El Tanque', 'colonia', true, null],
            ['El Tatoto (Palo de Flor)', 'colonia', true, null],
            ['El Zapote (Rancho el Zapote Juquilita)', 'colonia', true, null],
            ['Esquipulas', 'colonia', true, null],
            ['Fidepal', 'colonia', true, 40.00],
            ['FIOB', 'colonia', true, null],
            ['Fraccionamiento la Estancia Tercera Sección', 'colonia', true, 40.00],
            ['Hacienda el Rosario', 'colonia', true, null],
            ['Héroes de La Independencia', 'colonia', true, null],
            ['Centro', 'colonia', true, 30.00],
            ['Insurgentes', 'colonia', true, null],
            ['Jardines Del Sur', 'colonia', true, 30.00],
            ['José López Alavez', 'colonia', true, null],
            ['La Antorcha', 'colonia', true, null],
            ['La Ciénega', 'colonia', true, null],
            ['La Ermita', 'colonia', true, null],
            ['La Esperanza', 'colonia', true, null],
            ['La Estancia', 'colonia', false, 40.00],
            ['La Joya', 'colonia', true, null],
            ['La Junta', 'colonia', false, 45.00],
            ['La Merced', 'colonia', true, null],
            ['La Providencia', 'colonia', true, 30.00],
            ['Las Animas', 'colonia', true, null],
            ['La SARH', 'colonia', true, null],
            ['Las Campanas', 'colonia', true, null],
            ['Las Flores', 'colonia', true, null],
            ['Las Huertas', 'colonia', true, null],
            ['Las Lomas', 'colonia', true, null],
            ['La Soledad', 'colonia', true, null],
            ['Las Palmas', 'colonia', false, 50.00],
            ['Las Peñitas', 'colonia', true, 30.00],
            ['Lázaro Cárdenas', 'colonia', false, 45.00],
            ['Lázaro Cárdenas 1ra. Sección', 'colonia', false, 45.00],
            ['Lázaro Cárdenas 2da Sección', 'colonia', false, 45.00],
            ['Lindavista', 'colonia', true, null],
            ['Llano Grande', 'colonia', true, null],
            ['Los Álamos 1a. Sección', 'colonia', true, 40.00],
            ['Los Álamos 2da. Sección', 'colonia', true, 40.00],
            ['Los Naranjos', 'colonia', false, 45.00],
            ['Los Pinos 2a. Sección', 'colonia', false, 50.00],
            ['Los Presidentes', 'colonia', true, 30.00],
            ['Los Presidentes 2da Sección', 'colonia', true, 30.00],
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
            ['Santa Isabel', 'colonia', true, 35.00],
            ['Santa Maria Ayu', 'colonia', true, null],
            ['Santa María Xochixtlapilco', 'colonia', false, 45.00],
            ['Santa Rosa 1a. Sección', 'colonia', true, 30.00],
            ['Santa Rosa 2a. Sección', 'colonia', true, 35.00],
            ['Santa Teresa', 'colonia', true, 30.00],
            ['Santiago Chilixtlahuaca', 'colonia', true, null],
            ['Señor de los Corazones', 'colonia', true, null],
            ['Sinahi 1a. Sección', 'colonia', false, 50.00],
            ['Sinahi 2a. Sección', 'colonia', false, 50.00],
            ['Tabaco', 'colonia', true, null],
            ['Tepeyac', 'colonia', true, 30.00],
            ['Valerio Trujano', 'colonia', true, 30.00],
            ['Villa de Los Ángeles', 'colonia', true, null],
            ['Villa Universitaria', 'colonia', true, null],
            ['Vista Hermosa', 'colonia', false, 45.00],
            ['Yuxichi', 'colonia', true, null],
            ['San Angel', 'colonia', false, 40.00],
            ['Volcanes', 'colonia', false, 50.00],
            ['Guardia Nacional', 'colonia', true, 50.00],
            ['Huajolotitlan', 'colonia', false, 75.00],
    ];

    // Valores de la carga inicial (2026_10_27_000003) para el rollback: [colonia, envio_gratuito, monto_express]
    const ORIGINAL = [
            ['Acatlima', false, 45.00],
            ['Agencia Municipal Rancho Solano', true, null],
            ['Agua Dulce', false, 50.00],
            ['Ahuehuetitlan de González', true, null],
            ['Altavista Juárez', true, null],
            ['Antonio de León', true, null],
            ['Antonio de León Fovissste', true, null],
            ['Antonio de León Fovissste 2da. Sección', true, null],
            ['Antonio de León Fovissste 3ra. Sección', true, null],
            ['Aviación 1a Secc', true, null],
            ['Aviación 2a. Secc', true, null],
            ['Aviación Poniente', true, null],
            ['Barrio Guadalupe Victoria', true, null],
            ['Bella Vista', true, null],
            ['Buenavista', true, null],
            ['Chapultepec', true, null],
            ['Colonia Juquilita', true, null],
            ['Cuauhtémoc', true, null],
            ['Del Carmen', true, null],
            ['Del Maestro 1a Sección', true, null],
            ['Del Maestro 2a Sección', true, null],
            ['Del Valle', true, null],
            ['Dolores', true, null],
            ['El Calvario', true, null],
            ['El Carmen II', true, null],
            ['El Castillo', true, null],
            ['El Girón', true, null],
            ['El Mirador', false, 30.00],
            ['El Molino', true, null],
            ['El Palmar', true, null],
            ['El Rincón', true, null],
            ['El Rosario', false, 40.00],
            ['El Rosario 2da. Sección', false, 40.00],
            ['El Tanque', true, null],
            ['El Tatoto (Palo de Flor)', true, null],
            ['El Zapote (Rancho el Zapote Juquilita)', true, null],
            ['Esquipulas', true, null],
            ['Fidepal', true, null],
            ['FIOB', true, null],
            ['Fraccionamiento la Estancia Tercera Sección', true, null],
            ['Hacienda el Rosario', true, null],
            ['Héroes de La Independencia', true, null],
            ['Heroica Ciudad de Huajuapan de León Centro', true, null],
            ['Insurgentes', true, null],
            ['Jardines Del Sur', true, null],
            ['José López Alavez', true, null],
            ['La Antorcha', true, null],
            ['La Ciénega', true, null],
            ['La Ermita', true, null],
            ['La Esperanza', true, null],
            ['La Estancia', false, 45.00],
            ['La Joya', true, null],
            ['La Junta', false, 45.00],
            ['La Merced', true, null],
            ['La Providencia', true, null],
            ['Las Animas', true, null],
            ['La SARH', true, null],
            ['Las Campanas', true, null],
            ['Las Flores', true, null],
            ['Las Huertas', true, null],
            ['Las Lomas', true, null],
            ['La Soledad', true, null],
            ['Las Palmas', false, 50.00],
            ['Las Peñitas', true, null],
            ['Lázaro Cárdenas', false, 45.00],
            ['Lázaro Cárdenas 1ra. Sección', false, 45.00],
            ['Lázaro Cárdenas 2da Sección', false, 45.00],
            ['Lindavista', true, null],
            ['Llano Grande', true, null],
            ['Los Álamos 1a. Sección', true, null],
            ['Los Álamos 2da. Sección', true, null],
            ['Los Naranjos', false, 50.00],
            ['Los Pinos 2a. Sección', false, 40.00],
            ['Los Presidentes', true, null],
            ['Los Presidentes 2da Sección', true, null],
            ['Magdalena Tetaltepec', true, null],
            ['Militar', true, null],
            ['Mixteca de Cárdenas Infonavit', true, null],
            ['Monte Alban', true, null],
            ['Morelos', false, 30.00],
            ['Niños Héroes', true, null],
            ['Núcleo Rural Universitario', true, null],
            ['Ojo de Agua', true, null],
            ['Pedregal de la Mixteca', true, null],
            ['Puerta Del Sol', true, null],
            ['Rancho Burgoa', true, null],
            ['Rancho Jesús', true, null],
            ['Rancho Ramírez', true, null],
            ['Rancho Reyes', true, null],
            ['Reforma 1a. Sección', true, null],
            ['Reforma 2a. Sección', true, null],
            ['Reforma Agraria', true, null],
            ['Río Balsas', true, null],
            ['Salto del Agua 1a. Sección', true, null],
            ['Salto del Agua 2a. Sección', true, null],
            ['San Antonio', true, null],
            ['San Diego', true, null],
            ['San Isidro Oriente', true, null],
            ['San Isidro Poniente', true, null],
            ['San Mateo', true, null],
            ['San Miguel', true, null],
            ['San Miguel Papalutla', true, null],
            ['San Nicolás Tabernillas', true, null],
            ['San Pedro de los Pinos', true, null],
            ['San Pedro Yodoyuxi', true, null],
            ['San Rafael 1 Sección', false, 30.00],
            ['San Rafael 2 Sección', false, 30.00],
            ['Santa Cruz', true, null],
            ['Santa Fé', true, null],
            ['Santa Isabel', true, null],
            ['Santa Maria Ayu', true, null],
            ['Santa María Xochixtlapilco', false, 45.00],
            ['Santa Rosa 1a. Sección', true, null],
            ['Santa Rosa 2a. Sección', true, null],
            ['Santa Teresa', true, null],
            ['Santiago Chilixtlahuaca', true, null],
            ['Señor de los Corazones', true, null],
            ['Sinahi 1a. Sección', false, 45.00],
            ['Sinahi 2a. Sección', false, 45.00],
            ['Tabaco', true, null],
            ['Tepeyac', true, null],
            ['Valerio Trujano', true, null],
            ['Villa de Los Ángeles', true, null],
            ['Villa Universitaria', true, null],
            ['Vista Hermosa', false, 40.00],
            ['Yuxichi', true, null],
            ['San Angel', false, 40.00],
            ['Volcanes', false, 40.00],
    ];

    public function up()
    {
        DB::transaction(function () {
            $this->renombrarCentro(self::CENTRO_ANTES, self::CENTRO_AHORA);

            foreach (self::CATALOGO as list($colonia, $tipo, $gratuito, $monto)) {
                $datos = ['envio_gratuito' => $gratuito, 'monto_express' => $monto, 'updated_at' => now()];
                $existe = DB::table('tarifas_envio')->where('colonia', $colonia)->exists();
                if ($existe) {
                    DB::table('tarifas_envio')->where('colonia', $colonia)->update($datos);
                } else {
                    DB::table('tarifas_envio')->insert($datos + [
                        'colonia' => $colonia, 'tipo' => $tipo, 'activo' => true, 'created_at' => now(),
                    ]);
                }
            }
        });
    }

    /**
     * Regresa a los valores de la carga inicial, quita las colonias nuevas y devuelve el nombre del Centro.
     * Cambios hechos a mano antes de esta migración no se recuperan.
     */
    public function down()
    {
        DB::transaction(function () {
            $originales = array_column(self::ORIGINAL, 0);
            $nuevas = array_diff(array_column(self::CATALOGO, 0), $originales, [self::CENTRO_AHORA]);
            DB::table('tarifas_envio')->whereIn('colonia', $nuevas)->delete();

            $this->renombrarCentro(self::CENTRO_AHORA, self::CENTRO_ANTES);
            foreach (self::ORIGINAL as list($colonia, $gratuito, $monto)) {
                DB::table('tarifas_envio')->where('colonia', $colonia)
                    ->update(['envio_gratuito' => $gratuito, 'monto_express' => $monto, 'updated_at' => now()]);
            }
        });
    }

    private function renombrarCentro($de, $a)
    {
        if (DB::table('tarifas_envio')->where('colonia', $a)->exists()) {
            return; // ya existe con el nombre nuevo: no se duplica
        }
        DB::table('tarifas_envio')->where('colonia', $de)->update(['colonia' => $a, 'updated_at' => now()]);
        DB::table('clients')->where('colonia', $de)->update(['colonia' => $a]);
    }
}
