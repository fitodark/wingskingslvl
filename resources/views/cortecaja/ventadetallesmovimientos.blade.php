@extends('layouts.app')

@section('content')
<div class="container example-container">
	
	<div class="row">
		<div class="col-md-10">
			<p class="h3 text-black">Detalle de Movimientos de la Venta</p>
		</div>
		<div class="col-md-2">
            <a class="btn btn-outline-secondary custom" href="{{ route('cortecajadetalle', [$movimiento->idCorte]) }}" role="button">
				<i class="fa-sharp fa-solid fa-plus"></i>
				<span>Regresar</span>
			</a>
		</div>
	</div>

    <div class="row">
        <div class="form-group col-md-3">
            <label class="form-label">Folio Venta:</label>
            <input class="form-control" type="text" aria-label="Disabled input example" disabled
            value="{{$venta->ventaId}}">
        </div>
        <div class="form-group col-md-3">
            <label >Ubicación</label>
            <input class="form-control" type="text" placeholder=".form-control-sm" disabled
            value="{{ ($venta->type == 1)? $venta->dinerstable->name:(($venta->type == 2)? 'Domicilio':'Para Llevar (Pasan a recoger)')}}">
        </div>
        <div class="form-group col-md-6">
            @if ($venta->type == 2 || $venta->type == 3)
                <label>Cliente</label>
                <input class="form-control" type="text" placeholder=".form-control-sm" disabled
                value="{{ $venta->client->name }} ( {{ $venta->client->phone }} ) - {{ $venta->client->address }} - {{ $venta->client->reference }}">
            @elseif ($venta->type == 1)
                <label>Mesa</label>
                <input class="form-control" type="text" placeholder=".form-control-sm" disabled
                value="{{ $venta->dinerstable->name }}" readonly>
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
                @if ($detalleventa->estatus == 0)
                    <tr class="table-danger">
                @else
                    <tr>
                @endif
                    <td>{{ $detalleventa->ventasProductosId }}</td>
                    <td>{{ $detalleventa->product->name }} - {{ $detalleventa->product->detail }} 
                        @if ($detalleventa->product->type != 2 && is_null($detalleventa->descripcion) == false)
                            <br>[{{ $detalleventa->descripcion }}]
                        @endif
                        @if ($detalleventa->product->type == 2)
                            <br>
                            @foreach (json_decode($detalleventa->descripcion, TRUE) as $key => $value)
                                    {{ $value[0]['value'] }} - {{ $value[1]['value']}} <br>
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
@endsection
