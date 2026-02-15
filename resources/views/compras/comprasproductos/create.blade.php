@extends('layouts.app')

@section('content')
<div class="container example-container">
	
	<div class="row">
		<div class="col-md-10">
			<p class="h2 text-black">Agregar Producto</p>
        </div>
		<div class="col-md-2 ">
            <a class="btn btn-outline-secondary custom" href="{{ route('compras.create') }}" role="button">
				<i class="fa-sharp fa-solid fa-plus"></i>
				<span>Regresar</span>
			</a>
		</div>
	</div>

    <div class="row">
		<div class="col-md-12">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('comprasproductos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="text" hidden name="idCompra" id="idCompra" value="{{ $idCompra }}">
                <div class="row">
                    <div class="form-group col-md-6">
                        <strong>Producto:</strong>
                        <select name="idProducto" id="idProducto" class="form-control">
                        @foreach($items as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                        </select>
                        <div class="invalid-feedback">El producto es obligatorio.</div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <strong>Cantidad:</strong>
                        <input id="cantidad" type="text" name="cantidad" class="form-control" 
                            placeholder="Cantidad" value="{{ old('cantidad') }}" required>
                        <div class="invalid-feedback">La cantidad es obligatoria.</div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <strong>Monto:</strong>
                        <input id="monto" type="text" name="monto" class="form-control" 
                            placeholder="Monto de la nota" value="{{ old('monto') }}" required>
                        <div class="invalid-feedback">El monto es obligatorio.</div>
                    </div>
                </div>
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
