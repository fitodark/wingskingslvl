<?php

use App\Role;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $role = new Role();
        $role->name = 'admin';
        $role->description = 'Administrator';
        $role->save();

        $role = new Role();
        $role->name = 'encargado';
        $role->description = 'Encargado Sucursal';
        $role->save();

        $role = new Role();
        $role->name = 'mesero';
        $role->description = 'Mesero';
        $role->save();
    }
}
