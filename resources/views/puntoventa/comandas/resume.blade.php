@extends('layouts.app')

@section('content')
<form id="tableSelectForm" action="{{ route('printProductsOrder', ['venta' => $venta->ventaId, 'client' => !empty($client)? $client->id:''] ) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('GET')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <p class="h2 text-black">Resumen</p>
        </div>
    </div>
    <input type="text" hidden name="apply" id="type" value="1">
    <input type="text" hidden name="clientId" id="type" value="{{ !empty($client)? $client->id:'' }}">
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
                                <a class="btn btn-primary custom" href="{{ route('foodsTab', ['venta' => $venta['ventaId'], 'client' => $client['id']]) }}" role="button">Anterior</a>
                            </div>
                            <div class="col-2 justify-content-center">
                                <a class="btn btn-outline-secondary custom" href="{{ route('cancelarVenta', $venta['ventaId']) }}" role="button">Regresar</a>
                            </div>
                            <div class="col-2 justify-content-center">
                                <!-- <a class="btn btn-primary custom" href="{{ route('printProductsOrder', ['venta' => $venta['ventaId'], 'client' => $client['id']]) }}" role="button">Finalizar</a> -->
                                <button type="submit" class="btn btn-primary custom" name="next">Finalizar</button>
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
</form>
@endsection
