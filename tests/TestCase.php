<?php

namespace Tests;

use App\Config;
use App\CorteCaja;
use App\InventarioProductos;
use App\Product;
use App\Role;
use App\User;
use App\Venta;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Usuario activo con un rol (admin, encargado, mesero) y, si se da, un PIN.
     */
    protected function usuario($rol = 'mesero', $pin = null)
    {
        $role = Role::where('name', $rol)->first();
        if ($role === null) {
            $role = new Role();
            $role->name = $rol;
            $role->description = ucfirst($rol);
            $role->save();
        }

        static $n = 0;
        $n++;
        $usuario = User::create([
            'name' => "Usuario $n",
            'email' => "usuario$n@prueba.test",
            'password' => Hash::make('secreto'),
            'status' => true,
            'pin' => $pin === null ? null : Hash::make($pin),
        ]);
        $usuario->roles()->attach($role->id);

        return $usuario;
    }

    protected function config($key, $value)
    {
        Config::where('key', $key)->delete();
        $config = new Config();
        $config->key = $key;
        $config->value = $value;
        $config->save();
    }

    protected function producto(array $datos = [])
    {
        return Product::create(array_merge([
            'name' => 'Agua mineral',
            'detail' => '600 ml',
            'price' => 25,
            'type' => Product::TIPO_BARRA,
            'active' => 1,
        ], $datos));
    }

    protected function alitas($piezas = 10, $precio = 150)
    {
        return $this->producto([
            'name' => 'Alitas',
            'detail' => "$piezas piezas",
            'price' => $precio,
            'type' => Product::TIPO_ALITAS,
            'piezas' => $piezas,
        ]);
    }

    protected function ventaAbierta(User $usuario, array $datos = [])
    {
        return Venta::create(array_merge([
            'IdUsuario' => $usuario->id,
            'montoTotal' => 0,
            'montoSubtotal' => 0,
            'montoIva' => 0,
            'cantidadRecibida' => 0,
            'cantidadProductos' => 0,
            'type' => Venta::TIPO_LOCAL,
            'estatus' => Venta::ESTATUS_ABIERTA,
            'activo' => 0,
            'order' => 1,
        ], $datos))->fresh();
    }

    protected function corteAbierto(User $usuario, $montoApertura = 500)
    {
        return CorteCaja::create([
            'idUsuarioApertura' => $usuario->id,
            'fechaApertura' => date('Y-m-d'),
            'montoApertura' => $montoApertura,
            'montoCierre' => $montoApertura,
            'corteStatus' => true,
        ]);
    }

    protected function inventario(Product $producto, $cantidad)
    {
        return InventarioProductos::create([
            'idProducto' => $producto->id,
            'cantidad' => $cantidad,
            'cantidadMinima' => 0,
            'cantidadMaxima' => 100,
        ]);
    }

    /**
     * Catálogo de cantidades y sabores (configs pieces / flavors) como en producción.
     */
    protected function catalogoSabores()
    {
        foreach (['5 Piezas', '10 Piezas', '15 Piezas', 'Mitad', 'Todas'] as $i => $valor) {
            Config::insert(['key' => 'pieces', 'value' => $valor, 'order' => $i + 1]);
        }
        foreach (['Habanero', 'Bufalo', 'Mango Habanero'] as $i => $valor) {
            Config::insert(['key' => 'flavors', 'value' => $valor, 'order' => $i + 1]);
        }
    }

    /**
     * JSON de sabores como lo arma el modal: encabezado + renglones.
     * Ej. sabores([['5 Piezas', 'Habanero'], ['5 Piezas', 'Bufalo']])
     */
    protected function sabores(array $renglones)
    {
        $datos = [[['key' => '', 'value' => 'Cantidad'], ['key' => '', 'value' => 'Sabor']]];
        foreach ($renglones as list($cantidad, $sabor)) {
            $datos[] = [['key' => '0', 'value' => $cantidad], ['key' => '0', 'value' => $sabor]];
        }
        return json_encode($datos);
    }
}
