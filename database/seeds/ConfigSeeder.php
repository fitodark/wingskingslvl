<?php

use App\Config;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Config::updateOrCreate([
          'key' => 'printKitchen',
          'value' => 'XP-80COCINA'
        ]);

        Config::updateOrCreate([
          'key' => 'printBar',
          'value' => 'XP-80BARRA'
        ]);

        Config::updateOrCreate([
          'key' => 'logoTicket',
          'value' => 'wingtwo.jpg'
        ]);

        Config::updateOrCreate([
          'key' => 'titleTicket',
          'value' => 'Wings Kings'
        ]);

        Config::updateOrCreate([
          'key' => 'addressTicket',
          'value' => 'Bravo #30 Col. Centro, Huajuapan de León'
        ]);

        Config::updateOrCreate([
          'key' => 'addressComTicket',
          'value' => 'Oaxaca, CP 69005, Pedidos al: 953 117 5127'
        ]);

        Config::updateOrCreate([
          'key' => 'fooderPropTicket',
          'value' => 'GRACIAS POR SU PROPINA'
        ]);

        Config::updateOrCreate([
          'key' => 'fooderTicket',
          'value' => '***** Muchas gracias por su compra *****'
        ]);

        Config::updateOrCreate([
          'key' => 'pieces',
          'value' => '5 Piezas',
          'order' => '1'
        ]);

        Config::updateOrCreate([
          'key' => 'pieces',
          'value' => '10 Piezas',
          'order' => '2'
        ]);

        Config::updateOrCreate([
          'key' => 'pieces',
          'value' => '15 Piezas',
          'order' => '3'
        ]);

        Config::updateOrCreate([
          'key' => 'pieces',
          'value' => '20 Piezas',
          'order' => '4'
        ]);

        Config::updateOrCreate([
          'key' => 'pieces',
          'value' => 'Mitad',
          'order' => '5'
        ]);

        Config::updateOrCreate([
          'key' => 'pieces',
          'value' => 'Todas',
          'order' => '6'
        ]);

        Config::updateOrCreate([
          'key' => 'flavors',
          'value' => 'A la Diabla',
          'order' => '1'
        ]);

        Config::updateOrCreate([
          'key' => 'flavors',
          'value' => 'Habanero',
          'order' => '2'
        ]);

        Config::updateOrCreate([
          'key' => 'flavors',
          'value' => 'Bufalo',
          'order' => '3'
        ]);

        Config::updateOrCreate([
          'key' => 'flavors',
          'value' => 'Chipotle',
          'order' => '4'
        ]);

        Config::updateOrCreate([
          'key' => 'flavors',
          'value' => 'Mango Habanero',
          'order' => '5'
        ]);

        Config::updateOrCreate([
          'key' => 'flavors',
          'value' => 'Tamarindo',
          'order' => '6'
        ]);

        Config::updateOrCreate([
          'key' => 'flavors',
          'value' => 'Barbecue Hot',
          'order' => '7'
        ]);

        Config::updateOrCreate([
          'key' => 'flavors',
          'value' => 'Barbecue',
          'order' => '8'
        ]);

        Config::updateOrCreate([
          'key' => 'flavors',
          'value' => 'Parmesano',
          'order' => '9'
        ]);

        Config::updateOrCreate([
          'key' => 'flavors',
          'value' => 'Limon',
          'order' => '10'
        ]);

        Config::updateOrCreate([
          'key' => 'flavors',
          'value' => 'Valentina',
          'order' => '11'
        ]);

        Config::updateOrCreate([
          'key' => 'flavors',
          'value' => 'Infierno',
          'order' => '12'
        ]);

        Config::updateOrCreate([
          'key' => 'flavors',
          'value' => 'TNT',
          'order' => '13'
        ]);

        Config::updateOrCreate([
          'key' => 'flavors',
          'value' => 'Maracuya',
          'order' => '14'
        ]);

        Config::updateOrCreate([
          'key' => 'flavors',
          'value' => 'Natural',
          'order' => '15'
        ]);

        Config::updateOrCreate([
          'key' => 'printPrincipal',
          'value' => 'XP-80C1'
        ]);
	
        Config::updateOrCreate([
          'key' => 'printStatus',
          'value' => 'true'
        ]);

        Config::updateOrCreate([
          'key' => 'discountPercentage',
          'value' => '0'
        ]);

        Config::updateOrCreate([
          'key' => 'salesNumber',
          'value' => '1000000'
        ]);

        Config::updateOrCreate([
          'key' => 'products_type',
          'value' => 'Barra',
          'order' => '1'
        ]);

        Config::updateOrCreate([
          'key' => 'products_type',
          'value' => 'Cocina (Alitas)',
          'order' => '2'
        ]);

        Config::updateOrCreate([
          'key' => 'products_type',
          'value' => 'Cocina (General)',
          'order' => '3'
        ]);

        Config::updateOrCreate([
          'key' => 'products_type',
          'value' => 'Insumo',
          'order' => '4'
        ]);

        Config::updateOrCreate([
          'key' => 'products_promotion_type',
          'value' => 'General',
          'order' => '1'
        ]);

        Config::updateOrCreate([
          'key' => 'products_promotion_type',
          'value' => 'Domicilio',
          'order' => '2'
        ]);

    }
}
