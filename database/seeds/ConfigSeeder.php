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
        Config::Create([
          'key' => 'printKitchen',
          'value' => 'XP-80COCINA'
        ]);

        Config::Create([
          'key' => 'printBar',
          'value' => 'XP-80BARRA'
        ]);

        Config::Create([
          'key' => 'logoTicket',
          'value' => 'wingtwo.jpg'
        ]);

        Config::Create([
          'key' => 'titleTicket',
          'value' => 'Wings Kings'
        ]);

        Config::Create([
          'key' => 'addressTicket',
          'value' => 'Bravo #30 Col. Centro, Huajuapan de León'
        ]);

        Config::Create([
          'key' => 'addressComTicket',
          'value' => 'Oaxaca, CP 69005, Pedidos al: 953 117 5127'
        ]);

        Config::Create([
          'key' => 'fooderPropTicket',
          'value' => 'GRACIAS POR SU PROPINA'
        ]);

        Config::Create([
          'key' => 'fooderTicket',
          'value' => '***** Muchas gracias por su compra *****'
        ]);

        Config::Create([
          'key' => 'pieces',
          'value' => '5 Piezas',
          'order' => '1'
        ]);

        Config::Create([
          'key' => 'pieces',
          'value' => '10 Piezas',
          'order' => '2'
        ]);

        Config::Create([
          'key' => 'pieces',
          'value' => '15 Piezas',
          'order' => '3'
        ]);

        Config::Create([
          'key' => 'pieces',
          'value' => '20 Piezas',
          'order' => '4'
        ]);

        Config::Create([
          'key' => 'pieces',
          'value' => 'Mitad',
          'order' => '5'
        ]);

        Config::Create([
          'key' => 'pieces',
          'value' => 'Todas',
          'order' => '6'
        ]);

        Config::Create([
          'key' => 'flavors',
          'value' => 'A la Diabla',
          'order' => '1'
        ]);

        Config::Create([
          'key' => 'flavors',
          'value' => 'Habanero',
          'order' => '2'
        ]);

        Config::Create([
          'key' => 'flavors',
          'value' => 'Bufalo',
          'order' => '3'
        ]);

        Config::Create([
          'key' => 'flavors',
          'value' => 'Chipotle',
          'order' => '4'
        ]);

        Config::Create([
          'key' => 'flavors',
          'value' => 'Mango Habanero',
          'order' => '5'
        ]);

        Config::Create([
          'key' => 'flavors',
          'value' => 'Tamarindo',
          'order' => '6'
        ]);

        Config::Create([
          'key' => 'flavors',
          'value' => 'Barbecue Hot',
          'order' => '7'
        ]);

        Config::Create([
          'key' => 'flavors',
          'value' => 'Barbecue',
          'order' => '8'
        ]);

        Config::Create([
          'key' => 'flavors',
          'value' => 'Parmesano',
          'order' => '9'
        ]);

        Config::Create([
          'key' => 'flavors',
          'value' => 'Limon',
          'order' => '10'
        ]);

        Config::Create([
          'key' => 'flavors',
          'value' => 'Valentina',
          'order' => '11'
        ]);

        Config::Create([
          'key' => 'flavors',
          'value' => 'Infierno',
          'order' => '12'
        ]);

        Config::Create([
          'key' => 'flavors',
          'value' => 'TNT',
          'order' => '13'
        ]);

        Config::Create([
          'key' => 'flavors',
          'value' => 'Maracuya',
          'order' => '14'
        ]);

        Config::Create([
          'key' => 'flavors',
          'value' => 'Natural',
          'order' => '15'
        ]);

        Config::Create([
          'key' => 'printPrincipal',
          'value' => 'XP-80C1'
        ]);
	
        Config::Create([
          'key' => 'printStatus',
          'value' => 'true'
        ]);

        Config::Create([
          'key' => 'discountPercentage',
          'value' => '5'
        ]);

        Config::Create([
          'key' => 'salesNumber',
          'value' => '3'
        ]);
    }
}
