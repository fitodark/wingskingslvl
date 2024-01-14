<?php

use App\Config;
use Illuminate\Database\Seeder;

class DinersTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Config::Create([
            'name' => 'Mesa 1',
            'detail' => 'Mesa 1'
        ]);

        Config::Create([
            'name' => 'Mesa 2',
            'detail' => 'Mesa 2'
        ]);

        Config::Create([
            'name' => 'Mesa 3',
            'detail' => 'Mesa 3'
        ]);

        Config::Create([
            'name' => 'Mesa 4',
            'detail' => 'Mesa 4'
        ]);

        Config::Create([
            'name' => 'Mesa 5',
            'detail' => 'Mesa 5'
        ]);

        Config::Create([
            'name' => 'Mesa 6',
            'detail' => 'Mesa 6'
        ]);

        Config::Create([
            'name' => 'Mesa 7',
            'detail' => 'Mesa 7'
        ]);

        Config::Create([
            'name' => 'Mesa 8',
            'detail' => 'Mesa 8'
        ]);

        Config::Create([
            'name' => 'Mesa 9',
            'detail' => 'Mesa 9'
        ]);

        Config::Create([
            'name' => 'Mesa 10',
            'detail' => 'Mesa 10'
        ]);

        Config::Create([
            'name' => 'Mesa 11',
            'detail' => 'Mesa 11'
        ]);

        Config::Create([
            'name' => 'Mesa 12',
            'detail' => 'Mesa 12'
        ]);

        Config::Create([
            'name' => 'Mesa 13',
            'detail' => 'Mesa 13'
        ]);

        Config::Create([
            'name' => 'Mesa 14',
            'detail' => 'Mesa 14'
        ]);

        Config::Create([
            'name' => 'Mesa 15',
            'detail' => 'Mesa 15'
        ]);

        Config::Create([
            'name' => 'Barra',
            'detail' => 'Barra'
        ]);
    }
}
