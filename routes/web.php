<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
// Route::view('/', 'welcome');

Auth::routes([ 'register' => true ]);
// Auth::routes([ 'register' => true ]);

Route::get('/home', 'HomeController@index')->name('home');

// Route::get('/comandas', function(){
//   return view('puntoventa.comandas.index');
// });
Route::get('/comandas', 'Puntoventa\VentasController@index')->name('comandas');

Route::resources([
  'products' => 'ProductController',
  'catalogos' => 'Puntoventa\CatalogController',
  'usuarios' => 'Users\UsersController',
  'ventas' => 'Puntoventa\VentasController',
  'summary' => 'Puntoventa\SalesSummaryController'
]);
Route::get('/create/{venta?}', 'Puntoventa\VentasController@create')->name('create');
Route::get('/ventastore', 'Puntoventa\VentasController@store')->name('ventastore');
Route::post('/cerrarVenta', 'Puntoventa\VentasController@cerrarVenta')->name('cerrarVenta');

Route::get('/addMoreProducts/{venta}', 'Puntoventa\NavVentasController@addMoreProducts')->name('addMoreProducts');
Route::get('/drinksTab/{venta}', 'Puntoventa\NavVentasController@drinksTab')->name('drinksTab');
Route::get('/foodsTab/{venta}', 'Puntoventa\NavVentasController@foodsTab')->name('foodsTab');
Route::get('/resumeTab/{venta}', 'Puntoventa\NavVentasController@resumeTab')->name('resumeTab');
Route::post('/addProductVenta', 'Puntoventa\VentasProductosController@store')->name('addProductVenta');
Route::get('/addCliente/{venta}', 'Puntoventa\NavVentasController@addCliente')->name('addCliente');
Route::get('/finalizarVenta/{venta}', 'Puntoventa\NavVentasController@finalizarVenta')->name('finalizarVenta');
Route::get('/cancelarVenta/{venta?}', 'Puntoventa\NavVentasController@cancelarVenta')->name('cancelarVenta');

Route::delete('/eliminarProducto/{producto}', 'Puntoventa\VentasProductosController@destroy')->name('eliminarProducto');
Route::get('/ticket/print', 'PrinterController@print')->name('printticket');

Route::get('/getQueryClient/{query?}', 'Puntoventa\ClientController@getQueryClient')->name('getQueryClient');
Route::post('/addClient', 'Puntoventa\ClientController@addClient')->name('addClient');

Route::get('/venta/print/{venta?}', 'Puntoventa\VentasController@print')->name('ventaprint');
Route::get('/printProductsOrder/{venta?}', 'Puntoventa\VentasController@printProductsOrder')->name('printProductsOrder');
