@extends('layouts.app')

@section('content')
<div class="container example-container">
	
	<div class="row">
		<div class="col-md-10">
			<p class="h3 text-black">Movimientos del Corte de Caja</p>
		</div>
		<div class="col-md-2">
            <a class="btn btn-outline-secondary custom" href="{{ route('cortecaja') }}" role="button">
				<i class="fa-sharp fa-solid fa-plus"></i>
				<span>Regresar</span>
			</a>
		</div>
	</div>

    <!-- <div class="row">
		<div class="col-md-12 py-3">
            <p class="h3 text-black">Folio {{$corteCajaCurrent->idCorte}}, Fecha: @dateformat($corteCajaCurrent->fechaApertura)</p>
        </div>
    </div> -->
    <div class="row">
        <div class="form-group col-md-6">
            <label for="idCorte" class="form-label">Folio Corte:</label>
            <input name="idCorte" id="idCorte" class="form-control" type="text" aria-label="Disabled input example" disabled
            value="{{$corteCajaCurrent->idCorte}}">
        </div>
        <div class="form-group col-md-6">
            <label for="fechaApertura" class="form-label">Fecha Apertura:</label>
            <input name="fechaApertura" id="fechaApertura" class="form-control" type="text" aria-label="Disabled input example" disabled
            value="@dateformat($corteCajaCurrent->fechaApertura)">
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-3">
            <label for="clientAddress" class="form-label">Ventas Efectivo:</label>
            <input name="clientAddress" id="clientAddress" class="form-control" type="text" aria-label="Disabled input example" disabled
            value="@money($totalVentasEfectivoArray->montoTotalVentas)">
        </div>
        <div class="form-group col-md-3">
            <label for="clientAddress" class="form-label">Ventas Transferencias:</label>
            <input name="clientAddress" id="clientAddress" class="form-control" type="text" aria-label="Disabled input example" disabled
            value="@money($totalVentasTransferArray->montoTotalVentas)">
        </div>

        <div class="form-group col-md-6">
            <label for="clientReference" class="form-label">Total Compras:</label>
            <input name="clientReference" id="clientReference" class="form-control" type="text" aria-label="Disabled input example" disabled
            value="@money($totalComprasArray->montoTotalCompras)">
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
                    <th width="32%">Descripcion</th>
                    <th width="16%">Fecha</th>
                    <th width="15%">Monto</th>
                    <th width="15%">Tipo</th>
                </tr>
                @foreach ($cortecajamovimientos as $movimiento)
                <tr>
                    <td>{{ $movimiento->idCorteMovimiento }}</td>
                    <td>
                    @if (!empty($movimiento->idVenta))
                        {{ $movimiento->idVenta }}
                    @endif
                    @if (!empty($movimiento->idCompra))
                        {{ empty($movimiento->idCompra)? '':'Folio:'. $movimiento->compra->idCompra }} <br>
                        {{ empty($movimiento->idCompra)? '':'Proveedor:'. $movimiento->compra->proveedor }}
                    @endif
                    @if (empty($movimiento->idVenta) && empty($movimiento->idCompra))
                        Compra: {{ $movimiento->descripcion }}
                    @endif
                    </td>
                    <td>@datetimeformat($movimiento->updated_at) </td>
                    <td>@money($movimiento->monto)</td>
                    @if ($movimiento->idTipo == 1)
                        <td>@tipoMovimiento($movimiento->idTipo) - @paymenttype($movimiento->venta->payment_type)</td>
                    @else
                        <td>@tipoMovimiento($movimiento->idTipo)</td>
                    @endif
                </tr>
                @endforeach
            </table>
            
        </div>
    </div>
</div>
@endsection
