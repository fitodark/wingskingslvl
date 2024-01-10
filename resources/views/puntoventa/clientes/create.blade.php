@extends('layouts.app')

@section('content')
<div class="container example-container">
	
	<div class="row">
		<div class="col-md-10">
			<p class="h2 text-black">Agregar Cliente</p>
        </div>
		<div class="col-md-2 ">
            <a class="btn btn-outline-secondary custom" href="{{ route('clientes') }}" role="button">
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
        </div>
    </div>

    <div class="row">
		<div class="col-md-12">
            <form action="{{ route('clientes.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="form-group col-md-6">
                        <strong>Nombre:</strong>
                        <input type="text" name="name" class="form-control" placeholder="Nombre" value="{{ old('name') }}">
                    </div>
                    <div class="form-group col-md-6">
                        <strong>Dirección:</strong>
                        <input type="text" name="address" class="form-control" placeholder="Dirección" value="{{ old('address') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <strong>Teléfono:</strong>
                        <input type="text" name="phone" class="form-control" placeholder="Teléfono" value="{{ old('phone') }}">
                    </div>

                    <div class="form-group col-md-6">
                        <strong>Referencia:</strong>
                        <input type="text" name="reference" class="form-control" placeholder="Referencia" value="{{ old('reference') }}">
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
