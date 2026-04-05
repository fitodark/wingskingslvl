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

Auth::routes([ 'register' => false ]);
//Auth::routes([ 'register' => true ]);

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
  'clientes' => 'Puntoventa\ClientController',
  'compras' => 'ComprasController',
  'comprasproductos' => 'ComprasProductosController',
  'inventarioproductos' => 'InventarioProductosController',
  'cortecaja' => 'CorteCajaController',
  'cortecajamovimientos' => 'CorteCajaMovimientosController'
]);
Route::get('/create/{venta?}/{client?}', 'Puntoventa\VentasController@create')->name('create');
Route::put('/update/{venta?}/{client?}', 'Puntoventa\VentasController@update')->name('update');
Route::get('/ventastore', 'Puntoventa\VentasController@store')->name('ventastore');
Route::post('/cerrarVenta', 'Puntoventa\VentasController@cerrarVenta')->name('cerrarVenta');

Route::get('/addMoreProducts/{venta}/{client?}', 'Puntoventa\NavVentasController@addMoreProducts')->name('addMoreProducts');
Route::get('/drinksTab/{venta}/{client?}', 'Puntoventa\NavVentasController@drinksTab')->name('drinksTab');
Route::get('/foodsTab/{venta}/{client?}', 'Puntoventa\NavVentasController@foodsTab')->name('foodsTab');
Route::get('/resumeTab/{venta}/{clientId?}/{enableFooter?}', 'Puntoventa\NavVentasController@resumeTab')->name('resumeTab');
Route::post('/addProductVenta', 'Puntoventa\VentasProductosController@store')->name('addProductVenta');
//Route::get('/addCliente/{venta}', 'Puntoventa\NavVentasController@addCliente')->name('addCliente');
Route::get('/finalizarVenta/{venta}', 'Puntoventa\NavVentasController@finalizarVenta')->name('finalizarVenta');
Route::get('/cancelarVenta/{venta?}', 'Puntoventa\NavVentasController@cancelarVenta')->name('cancelarVenta');

Route::post('/eliminarProducto', 'Puntoventa\VentasProductosController@destroy')->name('eliminarProducto');
Route::get('/ticket/print', 'PrinterController@print')->name('printticket');

Route::get('/getQueryClient/{query?}', 'Puntoventa\ClientController@getQueryClient')->name('getQueryClient');
Route::post('/addClient', 'Puntoventa\ClientController@addClient')->name('addClient');
Route::get('/clientes', 'Puntoventa\ClientController@index')->name('clientes');
Route::post('/updateClient', 'Puntoventa\ClientController@updateClient')->name('updateClient');
Route::get('/getClient/{clientId?}', 'Puntoventa\ClientController@getClient')->name('getClient');
Route::get('/getClientDetails/{clientId?}', 'Puntoventa\ClientController@getClientDetails')->name('getClientDetails');
Route::get('/getByClientAndDate/{clientId?}', 'Puntoventa\ClientController@getByClientAndDate')->name('getByClientAndDate');

Route::get('/venta/print/{venta?}', 'Puntoventa\VentasController@printSale')->name('ventaprint');
Route::get('/printProductsOrder/{venta?}', 'Puntoventa\VentasController@printProductsOrder')->name('printProductsOrder');
Route::get('/summary/{date?}', 'Puntoventa\SalesSummaryController@index')->name('summary.index');
Route::post('/findSales', 'Puntoventa\SalesSummaryController@findSales')->name('findSales');

Route::get('/cortecaja', 'CorteCajaController@index')->name('cortecaja');
Route::get('/current/{date?}', 'CorteCajaController@current')->name('current');
Route::post('/cerrarcortecaja', 'CorteCajaController@cerrarcortecaja')->name('cerrarcortecaja');
Route::get('/cortecaja/detalle/{idCorte}', 'CorteCajaController@detalles')->name('cortecajadetalle');
Route::get('/cortecaja/detalleventa/{idCorteMovimiento}', 'CorteCajaController@salesdetail')->name('salesdetail');

Route::get('/cortecajamovimientos', 'CorteCajaMovimientosController@index')->name('cortecajamovimientos');

Route::get('/inventarioproductos', 'InventarioProductosController@index')->name('inventarioproductos');
Route::put('/inventarioproductos/{idInventarioProducto?}', 'InventarioProductosController@update')->name('inventarioproductos.update');
Route::get('/inventarioproductos/{idInventarioProducto?}/edit', 'InventarioProductosController@edit')->name('inventarioproductos.edit');

Route::get('/compras', 'ComprasController@index')->name('compras');
Route::get('/compras/create/{idCompra?}', 'ComprasController@create')->name('compras.create');
Route::get('/compras/addProducts/{idCompra}', 'ComprasController@addProducts')->name('compras.addProducts');
Route::post('/compras/addCompraProductos', 'ComprasController@addCompraProductos')->name('addCompraProductos');
Route::put('/compras/update/{idCompra}', 'ComprasController@update')->name('compras.update');
Route::get('/comprasproductos/delete/{idCompraProducto?}', 'ComprasProductosController@delete')->name('comprasproductos.delete');
Route::get('/usuarios', 'Users\UsersController@index')->name('usuarios');
Route::delete('/usuarios/{id}', 'Users\UsersController@destroy')->name('usuarios.destroy');
Route::get('/usuarios/{id}/edit', 'Users\UsersController@edit')->name('usuarios.edit');
Route::put('/usuarios/update/{id}', 'Users\UsersController@update')->name('usuarios.update');

Route::get('/timezone', function () {
    return [config('app.timezone'), date('Y-m-d H:i:s')];
});