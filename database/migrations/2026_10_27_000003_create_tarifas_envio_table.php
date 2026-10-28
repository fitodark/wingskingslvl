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
