@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <p class="h2 text-black">Seleccionar Alimentos</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 py-2">
            <div class="card">

                <div class="card-body">
                    @include('puntoventa.comandas.resumebody')
                </div>
            
                <div class="card-footer text-center">
                    @if ($enableFooter)
                        <div class="row justify-content-center">
                            <div class="col-3"></div>
                            <div class="col-2 justify-content-center">
                                <a class="btn btn-primary custom" href="{{ route('foodsTab', $venta['ventaId']) }}" role="button">Anterior</a>
                            </div>
                            <div class="col-2 justify-content-center">
                                <a class="btn btn-outline-secondary custom" href="{{ route('cancelarVenta', $venta['ventaId']) }}" role="button">Regresar</a>
                            </div>
                            <div class="col-2 justify-content-center">
                                <a class="btn btn-primary custom" href="{{ route('printProductsOrder', $venta['ventaId']) }}" role="button">Finalizar</a>
                            </div>
                            <div class="col-3"></div>
                        </div>
                    @else
                        <div class="row justify-content-center">
                            <div class="col-5"></div>
                            <div class="col-2 justify-content-center">
                                <a class="btn btn-outline-secondary custom" href="{{ route('cancelarVenta', $venta['ventaId']) }}" role="button">Regresar</a>
                            </div>
                            <div class="col-5"></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
