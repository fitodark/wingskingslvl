<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        Blade::directive('money', function ($amount) {
            return "<?php echo '$ ' . number_format($amount, 2); ?>";
        });

        Blade::directive('arrayPrint', function ($amount) {
            return "<?php print_r($amount); ?>";
        });

        Blade::directive('ventaType', function ($amount) {
            return "<?php echo e(\App\Venta::TIPOS[$amount] ?? 'No asignado'); ?>";
        });

        Blade::directive('ventaEstatus', function ($amount) {
            return "<?php echo e(\App\Venta::ESTATUS[$amount] ?? 'No asignado'); ?>";
        });

        Blade::directive('categoriaType', function ($amount) {
            return "<?php echo e(\App\Product::TIPOS[$amount] ?? ''); ?>";
        });

        Blade::directive('discount', function ($discountPercentage = 0) {
            return "<?php echo $discountPercentage. ' %'; ?>";
        });

        Blade::directive('discountApply', function ($discountApply) {
            return "<?php echo empty($discountApply)? '':
                (($discountApply==0)? '':
                    (($discountApply==1)? 'Aplica Descuento<br>':'')
                ); ?>";
        });

        Blade::directive('corteCajaEstatus', function ($corteStatus) {
            return "<?php
                switch ($corteStatus) {
                    case true:
                        echo 'Abierto';
                        break;
                    case false:
                        echo 'Finalizado';
                        break;
                    default:
                        echo 'No asignado';
                        break;
                }
             ?>";
        });

        Blade::directive('dateformat', function (string $date) {
            return "<?php echo date('M j, y', strtotime($date)); ?>";
        });

        Blade::directive('datetimeformat', function (string $date) {
            return "<?php echo date('M j - H:i', strtotime($date)); ?>";
        });

        Blade::directive('tipoMovimiento', function ($idTipo) {
            return "<?php echo e(\App\CorteCajaMovimientos::TIPOS[$idTipo] ?? 'No asignado'); ?>";
        });

        Blade::directive('compraEstatus', function ($corteStatus) {
            return "<?php
                switch ($corteStatus) {
                    case false:
                        echo 'Abierto';
                        break;
                    case true:
                        echo 'Finalizado';
                        break;
                    default:
                        echo 'No asignado';
                        break;
                }
             ?>";
        });

        Blade::directive('paymenttype', function ($corteStatus) {
            return "<?php echo e(\App\Venta::PAGOS[$corteStatus] ?? ''); ?>";
        });

        Blade::directive('activo', function ($status) {
            return "<?php
              switch ($status) {
                  case true:
                      echo 'Activo';
                      break;
                  case false:
                      echo 'Inactivo';
                      break;
                  default:
                      echo 'No asignado';
                      break;
              } ?>";
        });

        Blade::directive('ventaProductoEstatus', function ($estatus) {
            return "<?php echo e(\App\VentasProductos::ESTATUS[$estatus] ?? 'No asignado'); ?>";
        });

        // @anyrole('admin', 'encargado') ... @endanyrole
        Blade::if('anyrole', function (...$roles) {
            return auth()->check() && auth()->user()->hasAnyRole($roles);
        });

        Blade::if('role', function ($role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        // Formulario de cliente: lista de colonias del catálogo de envío para el campo colonia.
        // El paso 1 incluye el formulario dos veces (alta y edición), por eso cada lista lleva su
        // propio id y la consulta se hace una sola vez por petición.
        $colonias = null;
        $n = 0;
        View::composer('puntoventa.clientes.clientBodyForm', function ($view) use (&$colonias, &$n) {
            if ($colonias === null) {
                $colonias = \App\TarifaEnvio::activas()->orderBy('colonia')->pluck('colonia');
            }
            $n++;
            $view->with('coloniasEnvio', $colonias)->with('listaColoniasId', 'coloniasEnvio' . $n);
        });
    }
}

