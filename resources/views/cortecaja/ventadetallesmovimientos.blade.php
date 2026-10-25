@extends('layouts.app')

@section('content')
<div class="container example-container">
	
	<div class="wk-encabezado">
	    <p class="h3 text-black">Detalle de Movimientos de la Venta</p>
	    <div class="wk-encabezado-acciones">
	        <a class="btn btn-outline-secondary custom" href="{{ route('cortecajadetalle', [$movimiento->idCorte]) }}" role="button">
	        <i class="fa-solid fa-arrow-left"></i>
	        <span>Regresar</span>
	        </a>
	    </div>
	</div>

    <div class="row">
        <div class="form-group col-md-3">
            <label class="form-label">Folio Venta:</label>
            <input class="form-control" type="text" aria-label="Disabled input example" disabled
            value="{{ $venta->ventaId }}">
        </div>
        <div class="form-group col-md-3">
            <label >Ubicación</label>
            <input class="form-control" type="text" placeholder=".form-control-sm" disabled
            value="{{ $venta->ubicacion }}">
        </div>
        <div class="form-group col-md-6">
            @if (!$venta->esLocal() && !empty($venta->client))
                <label>Cliente</label>
                <input class="form-control" type="text" placeholder=".form-control-sm" disabled
                value="{{ $venta->client->name }} ( {{ $venta->client->phone }} ) - {{ $venta->client->address }} - {{ $venta->client->reference }}">
            @elseif ($venta->esParaLlevar())
                <label>Cuenta</label>
                <input class="form-control" type="text" placeholder=".form-control-sm" disabled
                value="{{ $venta->nombre_cuenta }}">
            @elseif ($venta->esLocal())
                <label>Mesa / Cuenta</label>
                <input class="form-control" type="text" placeholder=".form-control-sm" disabled
                value="{{ $venta->etiqueta_cuenta ?: 'Sin mesa' }}" readonly>
            @endif
        </div>
    </div>
    <div class="row">
		<div class="form-group col-md-3">
            <label>Total</label>
            <input class="form-control" type="text" placeholder=".form-control-sm" disabled
            value="@money($venta->montoTotal)">
        </div>
        <div class="form-group col-md-3">
            <label>Fecha Cierre</label>
            <input class="form-control" type="text" placeholder=".form-control-sm" disabled
            value="@datetimeformat($venta->updated_at)">
        </div>
        <div class="form-group col-md-3">
            <label>Usuario Apertura</label>
            <input class="form-control" type="text" placeholder=".form-control-sm" disabled
            value="{{ empty($venta->IdUsuario)? '':$venta->usuario->name }}">
        </div>
        <div class="form-group col-md-3">
            <label>Usuario Cierre</label>
            <input class="form-control" type="text" placeholder=".form-control-sm" disabled
            value="{{ empty($venta->id_user_close)? '':$venta->userClose->name }}">
        </div>
    </div>
    <div class="row">
		<div class="col-md-12 py-3">
        </div>
    </div>
    <div class="row">
		<div class="col-md-12">
            
            <div class="table-responsive">
            <table class="table table-striped table-sm">
                <tr>
                    <th width="5%">#</th>
                    <th width="30%">Descripcion</th>
                    <th width="10%">Monto</th>
                    <th width="10%">Estatus</th>
                    <th width="15%">Usuario<br>Alta</th>
                    <th width="15%">Usuario<br>Eliminación</th>
                    <th width="15%">Fecha</th>
                </tr>
                @foreach ($ventaproductos as $detalleventa)
                @if (!$detalleventa->estaActivo())
                    <tr class="table-danger">
                @else
                    <tr>
                @endif
                    <td>{{ $detalleventa->ventasProductosId }}</td>
                    <td>{{ $detalleventa->product->name }} - {{ $detalleventa->product->detail }} 
                        @if (!$detalleventa->product->llevaSabores() && is_null($detalleventa->descripcion) == false)
                            <br>[{{ $detalleventa->descripcion }}]
                        @endif
                        @if ($detalleventa->product->llevaSabores())
                            <br>
                            {{-- Sin el encabezado "Cantidad - Sabor"; un JSON mal formado no rompe la vista --}}
                            @foreach (\App\Support\SaboresOrden::renglones($detalleventa->descripcion) ?: [] as $renglon)
                                    {{ $renglon['cantidad'] }} - {{ $renglon['sabor'] }} <br>
                            @endforeach
                        @endif
                    </td>
                    <td>@money($detalleventa->montoVenta)</td>
                    <td>@ventaProductoEstatus($detalleventa->estatus)</td>
                    <td>{{ empty($detalleventa->id_user_create)? '':$detalleventa->userCreate->name }}</td>
                    <td>{{ empty($detalleventa->id_user_delete)? '':$detalleventa->userDelete->name }}</td>
                    <td>@datetimeformat($detalleventa->updated_at)</td>
                </tr>
                @endforeach
            </table>
            </div>
            
        </div>
    </div>
</div>
@endsection
