<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AssignMeseroRoleToAgenteUser extends Migration
{
    /**
     * El usuario 6 lo usa el agente de WhatsApp para registrar ventas (ventas.IdUsuario).
     * No tenía rol; se le asigna mesero para que no quede fuera al aplicar los roles.
     *
     * @return void
     */
    public function up()
    {
        $rolMesero = DB::table('roles')->where('name', 'mesero')->value('id');
        $existeUsuario = DB::table('users')->where('id', 6)->exists();
        if (!$rolMesero || !$existeUsuario) {
            return;
        }

        $yaTieneRol = DB::table('role_user')->where('user_id', 6)->exists();
        if (!$yaTieneRol) {
            DB::table('role_user')->insert([
                'role_id' => $rolMesero,
                'user_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $rolMesero = DB::table('roles')->where('name', 'mesero')->value('id');
        DB::table('role_user')->where('user_id', 6)->where('role_id', $rolMesero)->delete();
    }
}
