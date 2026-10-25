<?php

namespace App\Services;

use App\Config;
use App\CorteCajaMovimientos;
use App\Exceptions\VentaException;
use App\Product;
use App\PromotionsClients;
use App\Support\SaboresOrden;
use App\Traits\ComandasDataLibrary;
use App\Traits\CorteCajaDataLibrary;
use App\Traits\PrintSales;
use App\Venta;
use App\VentasProductos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Reglas del flujo de una venta: agregar y quitar productos, mandar la orden
 * a cocina/barra y cobrar. Los controladores (wizard y agente) solo traducen
 * la petición y la respuesta; las reglas viven aquí.
 *
 * Los errores de negocio se lanzan como VentaException con un mensaje para el usuario.
 */
class VentaService
{
    use ComandasDataLibrary;
    use CorteCajaDataLibrary;
    use PrintSales;

    /**
     * Agrega un producto a la orden actual. El precio sale del catálogo.
     * Para alitas, los sabores se validan y normalizan contra las piezas del producto.
     */
    public function agregarProducto(Venta $venta, Product $producto, $cantidad, $descripcion, $idUsuario)
    {
        $this->asegurarAbierta($venta);

        $cantidad = filter_var($cantidad, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999]]);
        if ($cantidad === false) {
            throw new VentaException('La cantidad debe ser un número entero mayor a cero.', 'cantidad');
        }

        if ($producto->llevaSabores()) {
            list($descripcion, $error) = SaboresOrden::normalizar($descripcion, $producto->piezas);
            if ($error !== null) {
                throw new VentaException($producto->name . ' - ' . $producto->detail . ': ' . $error, 'sabores');
            }
        }

        return DB::transaction(function () use ($venta, $producto, $cantidad, $descripcion, $idUsuario) {
            $ventaProducto = $venta->ventasProductos()->create([
                'IdProducto' => $producto->id,
                'cantidad' => $cantidad,
                'montoVenta' => $cantidad * floatval($producto->price),
                'descripcion' => $descripcion,
                'estatus' => VentasProductos::ESTATUS_ACTIVO,
                // Aún no se manda a cocina: se puede quitar sin PIN.
                'delete_flag' => true,
                'order' => $venta->order,
                'id_user_create' => $idUsuario,
            ]);
            $this->recalcularTotales($venta);

            return $ventaProducto;
        });
    }

    /**
     * Quita un producto de la venta.
     * - Sin comandar: se borra.
     * - Comandado: baja lógica (estatus 0) con el usuario que autorizó con su PIN,
     *   para que el admin la vea en el detalle de la venta del corte.
     */
    public function eliminarProducto(VentasProductos $ventaProducto, $usuarioAutoriza = null)
    {
        $venta = $ventaProducto->venta;
        $this->asegurarAbierta($venta);

        if (!$ventaProducto->estaActivo()) {
            throw new VentaException('Este producto ya fue eliminado.', 'producto');
        }
        if ($ventaProducto->estaComandado() && $usuarioAutoriza === null) {
            throw new VentaException('Este producto ya se mandó a cocina; eliminarlo requiere PIN.', 'pin');
        }

        DB::transaction(function () use ($venta, $ventaProducto, $usuarioAutoriza) {
            if ($ventaProducto->estaComandado()) {
                $ventaProducto->order = 0;
                $ventaProducto->delete_flag = false;
                $ventaProducto->estatus = VentasProductos::ESTATUS_ELIMINADO;
                $ventaProducto->id_user_delete = $usuarioAutoriza->id;
                $ventaProducto->save();
            } else {
                $ventaProducto->delete();
            }
            $this->recalcularTotales($venta);
        });
    }

    /**
     * "Regresar" en el wizard: descarta lo que se agregó en la orden actual y aún no se comandó.
     */
    public function cancelarOrdenActual(Venta $venta)
    {
        DB::transaction(function () use ($venta) {
            VentasProductos::where('IdVenta', $venta->ventaId)
                ->where('delete_flag', true)
                ->activos()
                ->update(['estatus' => VentasProductos::ESTATUS_ELIMINADO]);
            $this->recalcularTotales($venta);
        });
    }

    /**
     * "Agregar" desde la bandeja: abre una orden nueva en la misma venta.
     */
    public function nuevaOrden(Venta $venta)
    {
        $this->asegurarAbierta($venta);
        $venta->order += 1;
        $venta->save();
    }

    /**
     * Productos de la ronda actual que todavía no se mandan a cocina/barra (delete_flag = 1).
     * Lo ya comandado no se vuelve a imprimir aunque se dé Finalizar otra vez.
     *
     * @return array [$arrayBebidas, $arrayComidas]
     */
    public function productosPorComandar(Venta $venta)
    {
        list($arrayBebidas, $arrayComidas) = $this->getProductosOrden($venta);
        $pendiente = function ($vp) {
            return $vp->estaActivo() && !$vp->estaComandado();
        };

        return [$arrayBebidas->filter($pendiente)->values(), $arrayComidas->filter($pendiente)->values()];
    }

    /**
     * Manda a cocina y barra (si la impresión está activa) lo pendiente de la ronda actual y deja
     * la venta en la bandeja con los productos marcados como comandados.
     */
    public function comandar(Venta $venta, $clientId, $aplicarDescuento)
    {
        $this->asegurarAbierta($venta);

        list($arrayBebidas, $arrayComidas) = $this->productosPorComandar($venta);
        $hayPendientes = !$arrayBebidas->isEmpty() || !$arrayComidas->isEmpty();

        if ($hayPendientes && $this->impresionActiva()) {
            try {
                $this->printProducts($venta, $arrayBebidas, $arrayComidas);
            } catch (\Exception $e) {
                Log::info('Exception to print: ' . $e->getMessage());
            }
        }

        $this->activarVenta($venta, $clientId, $aplicarDescuento);
    }

    /**
     * Cobra y cierra la venta: registra el pago, la promoción si aplica, el movimiento
     * en el corte abierto y descuenta inventario. Todo en una transacción y con la fila
     * bloqueada, para que un doble clic no cierre la venta dos veces.
     * El ticket se imprime después de confirmar: si la impresora falla, la venta ya quedó cerrada.
     */
    public function cobrar(Venta $venta, $cantidadRecibida, $tipoPago, $idUsuario)
    {
        if (!array_key_exists((int) $tipoPago, Venta::PAGOS)) {
            throw new VentaException('Selecciona el método de pago.', 'paymenttype');
        }

        $cerrada = DB::transaction(function () use ($venta, $cantidadRecibida, $tipoPago, $idUsuario) {
            $venta = Venta::where('ventaId', $venta->ventaId)->lockForUpdate()->first();
            $this->asegurarAbierta($venta);

            if (!is_numeric($cantidadRecibida) || $cantidadRecibida < $venta->monto_a_cobrar) {
                throw new VentaException($venta->apply_discount == 1
                    ? 'La cantidad ingresada es menor al Monto Total con descuento'
                    : 'La cantidad ingresada es menor al Monto Total', 'quantity');
            }

            $ahora = date('Y-m-d H:i:s');
            $venta->estatus = Venta::ESTATUS_CERRADA;
            $venta->cantidadRecibida = $cantidadRecibida;
            $venta->payment_type = (int) $tipoPago;
            $venta->id_user_close = $idUsuario;
            $venta->save();

            $this->registrarPromocion($venta, $ahora);
            $this->registrarEnCorte($venta, $ahora);
            $this->getUpdateMontosInventarioFromVentas($venta->ventaId);

            return $venta;
        });

        $this->printVenta($cerrada, ['total' => true]);

        return $cerrada;
    }

    /**
     * Monto total y número de productos a partir de los renglones activos.
     */
    public function recalcularTotales(Venta $venta)
    {
        $totales = VentasProductos::where('IdVenta', $venta->ventaId)
            ->activos()
            ->selectRaw('coalesce(sum(montoVenta), 0) as monto, coalesce(sum(cantidad), 0) as cantidad')
            ->first();

        $venta->montoTotal = $totales->monto;
        $venta->cantidadProductos = $totales->cantidad;
        $venta->save();
    }

    public function impresionActiva()
    {
        $printStatus = Config::where('key', '=', 'printStatus')->first();
        return $printStatus !== null && $printStatus->value == 'true';
    }

    private function asegurarAbierta($venta)
    {
        if ($venta === null || !$venta->estaAbierta()) {
            throw VentaException::ventaCerrada();
        }
    }

    private function registrarPromocion(Venta $venta, $ahora)
    {
        if ($venta->apply_discount != 1 || !$this->getPromotionsClients($venta->ventaId, date('Y-m-d'))->isEmpty()) {
            return;
        }
        $arrayDiscountPercentage = $this->getDiscountPercentage($venta->client_id);
        if (empty($arrayDiscountPercentage)) {
            return;
        }
        $descuento = $arrayDiscountPercentage[0];
        PromotionsClients::create([
            'IdVenta' => $venta->ventaId,
            'client_id' => $venta->client_id,
            'porcentaje' => $descuento->discountPercentage,
            'cantidadVentas' => $descuento->total,
            'montoDescuento' => $venta->montoTotalDescuento,
            'estatus' => 1,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);
    }

    private function registrarEnCorte(Venta $venta, $ahora)
    {
        $corteCajaCurrent = $this->getCurrentCorteCaja(null);
        if ($corteCajaCurrent->isEmpty() || CorteCajaMovimientos::where('idVenta', $venta->ventaId)->exists()) {
            return;
        }
        $corteCaja = $corteCajaCurrent[0];
        CorteCajaMovimientos::create([
            'idCorte' => $corteCaja->idCorte,
            'idVenta' => $venta->ventaId,
            'idCompra' => null,
            'descripcion' => null,
            'monto' => $venta->monto_a_cobrar,
            'idTipo' => CorteCajaMovimientos::TIPO_VENTA,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);
        $this->getUpdateMontoTotalCorte($corteCaja->idCorte);
    }
}
