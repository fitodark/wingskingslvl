@extends('layouts.app')

@section('content')
<div class="container example-container">
	
	<div class="row">
		<div class="col-md-10">
			<p class="h2 text-black">Agregar Producto</p>
        </div>
		<div class="col-md-2 ">
            <a class="btn btn-outline-secondary custom" href="{{ route('catalogos.index') }}" role="button">
				<i class="fa-sharp fa-solid fa-plus"></i>
				<span>Regresar</span>
			</a>
		</div>
	</div>

    <div class="row">
		<div class="col-md-12 py-3">
                  @if ($errors->any())
                      <div class="alert alert-danger">
                          <strong>Whoops!</strong> There were some problems with your input.<br><br>
                          <ul>
                              @foreach ($errors->all() as $error)
                                  <li>{{ $error }}</li>
                              @endforeach
                          </ul>
                      </div>
                  @endif

    <div class="row">
		<div class="col-md-12">
            <form action="{{ route('catalogos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="form-group col-md-6">
                        <strong>Nombre:</strong>
                        <input type="text" name="name" class="form-control" placeholder="Nombre" value="{{ old('name') }}">
                    </div>
                
                    <div class="form-group col-md-6">
                        <strong>Descripción:</strong>
                        <input type="text" name="detail" class="form-control" placeholder="Descripcion"value="{{ old('detail') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <strong>Precio:</strong>
                        <input type="number" name="price" class="form-control" placeholder="Precio"
                        pattern="[0-9]+([\.,][0-9]+)?" step="0.01" title="This should be a number with up to 2 decimal places."
                        value="{{ old('price') }}">
                    </div>

                    <div class="form-group col-md-6">
                        <strong>Categoria:</strong>
                        <select class="custom-select" name="type"
                            value="{{ old('type') }}">
                            @foreach ($categorias as $key => $value)
                            <option value="{{ $key }}" {{ ($key == 0) ? 'selected' : '' }}>{{$value}}</option>
                            @endforeach
                        </select>
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
