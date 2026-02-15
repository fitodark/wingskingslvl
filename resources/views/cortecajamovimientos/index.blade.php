@extends('layouts.app')

@section('content')
<div class="container example-container">
	
	<div class="row">
		<div class="col-md-10">
			<p class="h3 text-black">Movimientos del Corte de Caja al dia: @dateformat($corteCajaCurrent->fechaApertura)</p>
		</div>
		<div class="col-md-2">
            <a href="{{ route('cortecajamovimientos.create') }}" class="btn btn-outline-success btn-add-new" role="button">
                <i class="fa-sharp fa-solid fa-plus"></i>
				<span>Agregar</span>
            </a>
		</div>
	</div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
		<div class="col-md-12 py-3">
            @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    <p>{{ $message }}</p>
                </div>
            @endif
        </div>
    </div>
    <div class="row">
		<div class="col-md-12">
            
            <table class="table table-striped table-sm">
                <tr>
                    <th width="5%">#</th>
                    <th width="13%">Corte</th>
                    <th width="13%">Venta</th>
                    <th width="13%">Compra</th>
                    <th width="13%">Descripcion</th>
                    <th width="13%">Monto</th>
                    <th width="20%">Estatus</th>
                    <th width="10%">Acción</th>
                </tr>
                @foreach ($cortecajamovimientos as $movimiento)
                <tr>
                    <td>{{ $movimiento->idCorteMovimiento }}</td>
                    <td>{{ $movimiento->idCorte }}</td>
                    <td>{{ $movimiento->idVenta }}</td>
                    <td>{{ $movimiento->idCompra }}</td>
                    <td>{{ $movimiento->descripcion }}</td>
                    <td>@money($movimiento->monto)</td>
                    @if ($movimiento->idTipo == 1)
                        <td>@tipoMovimiento($movimiento->idTipo) - @paymenttype($movimiento->venta->payment_type)</td>
                    @else
                        <td>@tipoMovimiento($movimiento->idTipo)</td>
                    @endif
                    @if ($movimiento->idTipo == 3)
                        <td>
                            <form action="{{ route('cerrarcortecaja', $movimiento->idCorteMovimiento) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn">
                                    <i class="ico-delete"></i>
                                </button>
                            </form>
                        </td>
                    @else
                        <td></td>
                    @endif
                </tr>
                @endforeach
            </table>
            {!! $cortecajamovimientos->links() !!}
        </div>
    </div>
</div>
@endsection
