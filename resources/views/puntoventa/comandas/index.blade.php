@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">Comandas</div>

          <ul class="list-group list-group-flush">
              <li class="list-group-item">
                <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="Toolbar with button groups">
                  <div class="btn-group" role="group" aria-label="First group">
                    <a href="{{ route('ventastore') }}" class="btn btn-primary btn-lg active" role="button" aria-pressed="true">Agregar</a>
                    <!-- <a href="{{ route('ventaprint') }}" class="btn btn-primary btn-lg active" role="button" aria-pressed="true">Imprimir</a> -->
                  </div>
                </div>
              </li>
              <li class="list-group-item">
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

                @include('puntoventa.comandas.ventas')
              </li>
          </ul>
    </div>
</div>
@include('puntoventa.comandas.ventaDetails')
@include('puntoventa.comandas.finalizarVenta')
@endsection
