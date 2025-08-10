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

    }
}


// return "<?php echo
// ($amount == 1)? 'Abierto':(
//     ($amount == 2)? 'Finalizado':(
//         ($amount == 3)? 'Cancelado':'No asignado'
//     )
// );
