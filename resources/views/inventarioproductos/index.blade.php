@extends('layouts.app')

@section('content')
<div class="container example-container">
	
	<div class="row">
		<div class="col-md-10">
			<p class="h2 text-black">Inventario productos</p>
		</div>
		<div class="col-md-2">
            <a href="{{ route('inventarioproductos.create') }}" class="btn btn-outline-success btn-add-new" role="button">
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
                    <th width="30%">Producto</th>
                    <th width="15%">Existencia</th>
                    <th width="15%">Minimo</th>
                    <th width="15%">Maximo</th>
                    <th width="20%">Acción</th>
                </tr>
                @foreach ($inventarioproductos as $productos)
                <tr>
                    <td>{{ $productos->idInventarioProducto }}</td>
                    <td>{{ $productos->product->name }}</td>
                    <td>{{ $productos->cantidad }}</td>
                    <td>{{ $productos->cantidadMinima }}</td>
                    <td>{{ $productos->cantidadMaxima }}</td>
                    <td>
                        <form action="{{ route('inventarioproductos.destroy', $productos->idInventarioProducto) }}" method="POST">
                            <a href="{{ route('inventarioproductos.edit', $productos->idInventarioProducto) }}">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn">
                                <i class="ico-delete"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </table>
            {!! $inventarioproductos->links() !!}
            
        </div>
    </div>
</div>
@endsection
