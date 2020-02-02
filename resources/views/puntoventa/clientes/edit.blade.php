@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-16">
            <div class="card">
                <div class="card-header">Editar Cliente Nuevo
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
                      <form action="{{ route('clientes.update', $cliente->id) }}" method="POST">
                          @csrf
                          @method('PUT')

                          <div class="row">
                              <div class="form-group col-md-6">
                                  <strong>Nombre:</strong>
                                  <input type="text" name="name" class="form-control" placeholder="Nombre" value="{{ $cliente->name }}">
                              </div>
                              <div class="form-group col-md-6">
                                  <strong>Dirección:</strong>
                                  <input type="text" name="address" class="form-control" placeholder="Dirección" value="{{ $cliente->address }}">
                              </div>
                          </div>
                          <div class="row">
                              <div class="form-group col-md-6">
                                  <strong>Teléfono:</strong>
                                  <input type="text" name="phone" class="form-control" placeholder="Teléfono" value="{{ $cliente->phone }}">
                              </div>

                              <div class="form-group col-md-6">
                                  <strong>Referencia:</strong>
                                  <input type="text" name="reference" class="form-control" placeholder="Referencia" value="{{ $cliente->reference }}">
                              </div>

                              <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                                      <button type="submit" class="btn btn-primary">Actualizar</button>
                              </div>
                          </div>
                      </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
