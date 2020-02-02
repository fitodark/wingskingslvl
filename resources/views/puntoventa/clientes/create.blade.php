@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-16">
            <div class="card">
                <div class="card-header">Agregar Cliente Nuevo
                  <div class="float-right">
                      <a class="btn btn-primary" href="{{ route('clientes') }}">Regresar</a>
                  </div>
                </div>

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

                  <div class="card-body">
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

                              <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                                      <button type="submit" class="btn btn-primary">Guardar</button>
                              </div>
                          </div>
                      </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
