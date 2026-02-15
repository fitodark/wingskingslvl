@extends('layouts.app')

@section('content')
<div class="container example-container">
	
	<div class="row">
		<div class="col-md-10">
			<p class="h2 text-black">Cortes de Caja</p>
		</div>
		<div class="col-md-2">
            <a href="{{ route('cortecaja.create') }}" class="btn btn-outline-success btn-add-new" role="button">
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
                    <th width="13%">Usuario Apertura</th>
                    <th width="12%">Usuario Cierre</th>
                    <th width="10%">Fecha Apertura</th>
                    <th width="10%">Fecha Cierre</th>
                    <th width="10%">Monto Apertura</th>
                    <th width="10%">Monto Cierre</th>
                    <th width="10%">Estatus</th>
                    <th width="20%">Acción</th>
                </tr>
                @foreach ($cortecaja as $corte)
                <tr>
                    <td>{{ $corte->idCorte }}</td>
                    <td>{{ $corte->usuarioApertura->name }}</td>
                    <td>{{ empty($corte->usuarioCierre)? '':$corte->usuarioCierre->name }}</td>
                    <td>{{ $corte->fechaApertura }}</td>
                    <td>{{ $corte->fechaCierre }}</td>
                    <td>@money($corte->montoApertura)</td>
                    <td>@money($corte->montoCierre)</td>
                    <td>@corteCajaEstatus($corte->corteStatus)</td>
                    <td>
                        <form method="POST">
                        <a class="ico-details" href="{{ route('cortecajadetalle', [$corte->idCorte]) }}"></a>
                        @if ($corte->corteStatus == true)
                            <button type="button" class="btn" data-toggle="modal"
                                data-target="#cerrarcortecajamodal"
                                data-idcorte="{{ $corte->idCorte }}"
                                data-fechaapertura="{{ $corte->fechaApertura }}">
                                <i class="ico-delete"></i>
                            </button>

                        @endif
                        </form>
                    </td>
                </tr>
                @endforeach
            </table>
            {!! $cortecaja->links() !!}
            
        </div>
    </div>
</div>
@include('cortecaja.cerrarcortecaja')
@endsection
