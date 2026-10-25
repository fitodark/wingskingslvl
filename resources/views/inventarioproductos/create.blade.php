@extends('layouts.app')

@section('content')
<div class="container example-container">
	
	<div class="wk-encabezado">
	    <p class="h2 text-black">Agregar Producto al Inventario</p>
	    <div class="wk-encabezado-acciones">
	        <a class="btn btn-outline-secondary custom" href="{{ route('inventarioproductos') }}" role="button">
	        <i class="fa-solid fa-arrow-left"></i>
	        <span>Regresar</span>
	        </a>
	    </div>
	</div>

    <div class="row">
		<div class="col-md-12">
            <form action="{{ route('inventarioproductos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('inventarioproductos.bodyform')
                <div class="row">
                    <div class="form-group col-md-4"></div>
                    <div class="form-group col-md-4">
                        <button type="submit" class="btn btn-primary custom">Guardar</button>
                    </div>
                    <div class="form-group col-md-4"></div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
