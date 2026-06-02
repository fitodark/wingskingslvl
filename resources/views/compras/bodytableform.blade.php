<div class="row">
    <div class="col-md-10">
        <p class="h2 text-black">Detalles</p>
    </div>
    <div class="col-md-2">
        @if ($compra->status == false)
            <a href="{{ route('compras.addProducts', [$compra->idCompra]) }}" class="btn btn-outline-success btn-add-new" role="button">
                <i class="fa-sharp fa-solid fa-plus"></i>
                <span>Agregar</span>
            </a>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <table class="table table-striped table-sm">
            <tr>
                <th width="10%">#</th>
                <th width="30%">Producto</th>
                <th width="20%">Cantidad</th>
                <th width="20%">Monto</th>
                <th width="20%">Acción</th>
            </tr>
            @foreach ($compra->comprasproductos as $producto)
            <tr>
                <td>{{ $producto->idCompraProducto }}</td>
                <td>{{ $producto->product->name }}</td>
                <td>{{ $producto->cantidad }}</td>
                <td>@money($producto->monto)</td>
                <td>
                    @if ($compra->status == false)                    
                        <a class="ico-delete" href="{{ route('comprasproductos.delete', $producto->idCompraProducto) }}"></a>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
        
    </div>
</div>
<div class="row">
    <div class="col-md-12 py-3">
    </div>
</div>
