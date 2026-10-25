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

Auth::routes([ 'register' => false ]);

// Todo lo demás requiere sesión y usuario activo.
Route::middleware(['auth', 'active'])->group(function () {

    Route::get('/home', 'HomeController@index')->name('home');

    // ---------- Comandas y clientes: mesero, encargado y admin ----------
    Route::middleware('role:admin,encargado,mesero')->group(function () {
        Route::get('/comandas', 'Puntoventa\VentasController@index')->name('comandas');

        Route::resources([
            'ventas' => 'Puntoventa\VentasController',
            'clientes' => 'Puntoventa\ClientController',
        ]);
        Route::get('/create/{venta?}/{client?}', 'Puntoventa\VentasController@create')->name('create');
        Route::put('/update/{venta?}/{client?}', 'Puntoventa\VentasController@update')->name('update');
        Route::post('/ventastore', 'Puntoventa\VentasController@store')->name('ventastore');
        Route::post('/cerrarVenta', 'Puntoventa\VentasController@cerrarVenta')->name('cerrarVenta');

        Route::post('/addMoreProducts/{venta}/{client?}', 'Puntoventa\NavVentasController@addMoreProducts')->name('addMoreProducts');
        Route::get('/drinksTab/{venta}/{client?}', 'Puntoventa\NavVentasController@drinksTab')->name('drinksTab');
        Route::get('/foodsTab/{venta}/{client?}', 'Puntoventa\NavVentasController@foodsTab')->name('foodsTab');
        Route::get('/resumeTab/{venta}/{clientId?}/{enableFooter?}', 'Puntoventa\NavVentasController@resumeTab')->name('resumeTab');
        // Detalle desde la bandeja: solo consulta (sin Finalizar ni Regresar que descarte la ronda).
        Route::get('/detalleComanda/{venta}', 'Puntoventa\NavVentasController@detalle')->name('detalleComanda');
        Route::post('/addProductVenta', 'Puntoventa\VentasProductosController@store')->name('addProductVenta');
        Route::post('/cancelarVenta/{venta?}', 'Puntoventa\NavVentasController@cancelarVenta')->name('cancelarVenta');
        Route::post('/eliminarProducto', 'Puntoventa\VentasProductosController@destroy')->name('eliminarProducto');

        Route::get('/getQueryClient/{query?}', 'Puntoventa\ClientController@getQueryClient')->name('getQueryClient');
        Route::post('/addClient', 'Puntoventa\ClientController@addClient')->name('addClient');
        Route::get('/clientes', 'Puntoventa\ClientController@index')->name('clientes');
        Route::post('/updateClient', 'Puntoventa\ClientController@updateClient')->name('updateClient');
        Route::get('/getClient/{clientId?}', 'Puntoventa\ClientController@getClient')->name('getClient');
        Route::get('/getClientDetails/{clientId?}', 'Puntoventa\ClientController@getClientDetails')->name('getClientDetails');
        Route::get('/getByClientAndDate/{clientId?}', 'Puntoventa\ClientController@getByClientAndDate')->name('getByClientAndDate');

        Route::get('/venta/print/{venta?}', 'Puntoventa\VentasController@printSale')->name('ventaprint');
        // Imprime la orden de cocina/barra y deja la venta activa en la bandeja.
        Route::post('/printProductsOrder/{venta?}', 'Puntoventa\VentasController@printProductsOrder')->name('printProductsOrder');
    });

    // ---------- Corte, compras e inventario: encargado y admin ----------
    Route::middleware('role:admin,encargado')->group(function () {
        Route::resources([
            'compras' => 'ComprasController',
            'comprasproductos' => 'ComprasProductosController',
            'inventarioproductos' => 'InventarioProductosController',
            'cortecaja' => 'CorteCajaController',
            'cortecajamovimientos' => 'CorteCajaMovimientosController'
        ]);

        Route::get('/summary/{date?}', 'Puntoventa\SalesSummaryController@index')->name('summary.index');
        Route::post('/findSales', 'Puntoventa\SalesSummaryController@findSales')->name('findSales');

        Route::get('/cortecaja', 'CorteCajaController@index')->name('cortecaja');
        Route::get('/current/{date?}', 'CorteCajaController@current')->name('current');
        Route::post('/cerrarcortecaja', 'CorteCajaController@cerrarcortecaja')->name('cerrarcortecaja');
        Route::get('/cortecaja/detalle/{idCorte}', 'CorteCajaController@detalles')->name('cortecajadetalle');
        // El detalle de una venta del corte lo restringe a admin el propio controlador.
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
        Route::delete('/comprasproductos/delete/{idCompraProducto?}', 'ComprasProductosController@delete')->name('comprasproductos.delete');
    });

    // ---------- Productos y usuarios: solo admin ----------
    Route::middleware('role:admin')->group(function () {
        Route::resources([
            'products' => 'ProductController',
            'catalogos' => 'Puntoventa\CatalogController',
            'usuarios' => 'Users\UsersController',
        ]);

        Route::get('/usuarios', 'Users\UsersController@index')->name('usuarios');
        Route::delete('/usuarios/{id}', 'Users\UsersController@destroy')->name('usuarios.destroy');
        Route::get('/usuarios/{id}/edit', 'Users\UsersController@edit')->name('usuarios.edit');
        Route::put('/usuarios/update/{id}', 'Users\UsersController@update')->name('usuarios.update');

        // Ticket de prueba de impresora.
        Route::get('/ticket/print', 'PrinterController@print')->name('printticket');
    });
});
