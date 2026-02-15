@extends('layouts.app')

@section('content')
<div class="container example-container">
	
	<div class="row">
		<div class="col-md-10">
			<p class="h2 text-black">Listado de Compras</p>
		</div>
		<div class="col-md-2">
            <a href="{{ route('compras.create') }}" class="btn btn-outline-success btn-add-new" role="button">
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
                    <th width="20%">Proveedor</th>
                    <th width="20%">Observaciones</th>
                    <th width="10%">Monto</th>
                    <th width="15%">Fecha Creacion</th>
                    <th width="10%">Estatus</th>
                    <th width="20%">Acción</th>
                </tr>
                @foreach ($compras as $compra)
                <tr>
                    <td>{{ $compra->idCompra }}</td>
                    <td>{{ $compra->proveedor }}</td>
                    <td>{{ $compra->observaciones }}</td>
                    <td>@money($compra->monto)</td>
                    <td>{{ $compra->created_at }}</td>
                    <td>@compraEstatus($compra->status)</td>
                    <td>
                        <form action="{{ route('compras.destroy', $compra->idCompra) }}" method="POST">
                            <a href="{{ route('compras.create', $compra->idCompra) }}">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            @csrf
                            @if ($compra->status == false)
                                @method('DELETE')
                                <button type="submit" class="btn">
                                    <i class="ico-delete"></i>
                                </button>
                            @endif
                        </form>
                    </td>
                </tr>
                @endforeach
            </table>
            {!! $compras->links() !!}
            
        </div>
    </div>
</div>
@endsection
