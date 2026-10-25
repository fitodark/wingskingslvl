<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Agente de WhatsApp: imprime las órdenes de cocina y barra de una venta que él insertó.
// Sin sesión de usuario; se autoriza con el encabezado X-Agente-Token (AGENTE_TOKEN en .env).
Route::middleware('agente.token')->group(function () {
    Route::post('/agente/ventas/{folio}/imprimir-orden', 'Api\AgenteController@imprimirOrden')
        ->where('folio', '[0-9]+')
        ->name('agente.imprimirOrden');
});
