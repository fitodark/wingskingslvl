<?php

namespace App\Http\Controllers\Puntoventa;

use App\Venta;
use App\Product;
use App\VentasProductos;
use App\Exceptions\VentaException;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use App\Http\Controllers\Controller;

use App\Services\PinAuthService;
use App\Services\VentaService;

class VentasProductosController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Agrega un producto a la orden actual (modales de Bebidas y Alimentos).
     */
    public function store(Request $request, VentaService $ventas)
    {
        $venta = Venta::find($request->get('ventaId'));
        $producto = Product::findOrFail($request->get('idProduct'));
        $tab = $request->get('tab') == 'drinks' ? 'drinksTab' : 'foodsTab';

        try {
            $ventas->agregarProducto($venta, $producto, $request->get('cantidad'),
                $request->get('description'), auth()->id());
        } catch (VentaException $e) {
            if ($e->esVentaCerrada()) {
                return redirect()->route('comandas');
            }
            return redirect()->route($tab, [$venta, $venta->client_id])
                ->withErrors([$e->campo => $e->getMessage()]);
        }

        return redirect()->route($tab, [$venta, $venta->client_id]);
    }

    /**
     * Quita un producto de la venta. Si ya se comandó, pide el PIN de un usuario activo
     * (llega por AJAX y responde JSON); si no, se borra directo (form normal).
     */
    public function destroy(Request $request, PinAuthService $pinService, VentaService $ventas)
    {
        $ventaProducto = VentasProductos::find($request->get('productoid'));
        if ($ventaProducto === null) {
            return $this->respuestaEliminar($request, null, 'El producto ya no existe en la venta.');
        }
        $venta = $ventaProducto->venta;

        $usuarioAutoriza = null;
        if ($ventaProducto->estaComandado() && $ventaProducto->estaActivo() && $venta->estaAbierta()) {
            // Máximo 5 PIN incorrectos por minuto por usuario, para que no se pueda adivinar probando.
            $limiter = app(RateLimiter::class);
            $llave = 'pin-eliminar:' . auth()->id();
            if ($limiter->tooManyAttempts($llave, 5)) {
                return response()->json(['errors' => ['pin' => 'Demasiados intentos con PIN incorrecto. Espera '
                    . $limiter->availableIn($llave) . ' segundos.']]);
            }

            $usuarioAutoriza = $pinService->validarPin($request->pin);
            if (!$usuarioAutoriza) {
                $limiter->hit($llave, 60);
                return response()->json(['errors' => ['pin' => 'El PIN es incorrecto!']]);
            }
            $limiter->clear($llave);
        }

        try {
            $ventas->eliminarProducto($ventaProducto, $usuarioAutoriza);
        } catch (VentaException $e) {
            return $this->respuestaEliminar($request, $venta, $e->getMessage());
        }

        return $this->respuestaEliminar($request, $venta);
    }

    private function respuestaEliminar(Request $request, $venta, $error = null)
    {
        $tab = $request->get('tab') == 'drinks' ? 'drinksTab' : 'foodsTab';
        $abierta = $venta !== null && $venta->estaAbierta();
        $url = $abierta ? route($tab, [$venta, $venta->client_id]) : route('comandas');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($error !== null
                ? ['errors' => [$error]]
                : ['success' => true, 'url' => $url]);
        }

        $respuesta = redirect($url);
        return $error !== null ? $respuesta->withErrors(['producto' => $error]) : $respuesta;
    }
}
