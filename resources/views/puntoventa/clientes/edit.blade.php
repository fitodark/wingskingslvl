@extends('layouts.app')

@section('content')
<div class="container example-container">
    <div class="wk-encabezado">
        <p class="h2 text-black">Editar Cliente</p>
        <div class="wk-encabezado-acciones">
            <a class="btn btn-outline-secondary custom" href="{{ route('clientes') }}" role="button">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Regresar</span>
            </a>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">

                  @if ($errors->any())
                      <div class="alert alert-danger">
                          <strong>Revisa los datos capturados:</strong>
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

                        @include('puntoventa.clientes.clientBodyForm')

                        <div class="row">
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
