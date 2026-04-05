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
            return "<?php
              switch ($amount) {
                  case 1:
                      echo 'Local';
                      break;
                  case 2:
                      echo 'Domicilio';
                      break;
                  case 3:
                      echo 'Domicilio';
                      break;
                  default:
                      echo 'No asignado';
                      break;
              } ?>";
        });

        Blade::directive('ventaEstatus', function ($amount) {
            return "<?php
                switch ($amount) {
                    case 1:
                        echo 'Abierto';
                        break;
                    case 2:
                        echo 'Finalizado';
                        break;
                    case 3:
                        echo 'Cancelado';
                        break;
                    default:
                        echo 'No asignado';
                        break;
                }
             ?>";
        });

        Blade::directive('categoriaType', function ($amount) {
            return "<?php
              switch ($amount) {
                  case 1:
                      echo 'Barra';
                      break;
                  case 2:
                      echo 'Cocina (Alitas)';
                      break;
                  case 3:
                      echo 'Cocina (General)';
                      break;
                  case 4:
                      echo 'Insumo';
                      break;
              } ?>";
        });

        Blade::directive('discount', function ($discountPercentage = 0) {
            return "<?php echo $discountPercentage. ' %'; ?>";
        });

        Blade::directive('discountApply', function ($discountApply) {
            return "<?php echo empty($discountApply)? '':
                (($discountApply==0)? '':
                    (($discountApply==1)? 'Aplica Descuento':'')
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
            return "<?php
                switch ($idTipo) {
                    case 1:
                        echo 'Venta';
                        break;
                    case 2:
                        echo 'Compra';
                        break;
                    case 3:
                        echo 'Compra libre';
                        break;
                    default:
                        echo 'No asignado';
                        break;
                }
             ?>";
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
            return "<?php
                switch ($corteStatus) {
                    case 1:
                        echo 'Efectivo';
                        break;
                    case 2:
                        echo 'Transferencia';
                        break;
                    default:
                        echo 'No asignado';
                        break;
                }
             ?>";
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
            return "<?php
                switch ($estatus) {
                    case 1:
                        echo 'Cobrado';
                        break;
                    case 0:
                        echo 'Eliminado';
                        break;
                    default:
                        echo 'No asignado';
                        break;
                }
             ?>";
        });

        Blade::if('role', function ($role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });
    }
}


// return "<?php echo
// ($amount == 1)? 'Abierto':(
//     ($amount == 2)? 'Finalizado':(
//         ($amount == 3)? 'Cancelado':'No asignado'
//     )
// );
