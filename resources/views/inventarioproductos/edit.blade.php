@extends('layouts.app')

@section('content')
<div class="container example-container">
    <div class="wk-encabezado">
        <p class="h2 text-black">Editar Producto del Inventario</p>
        <div class="wk-encabezado-acciones">
            <a class="btn btn-outline-secondary custom" href="{{ route('inventarioproductos') }}" role="button">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Regresar</span>
            </a>
        </div>
    </div>

    <div class="row">
		<div class="col-md-12">
            <form action="{{ route('inventarioproductos.update', $inventarioproductos->idInventarioProducto) }}" method="POST">
                @csrf
                @method('PUT')
                @include('inventarioproductos.bodyform')
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
