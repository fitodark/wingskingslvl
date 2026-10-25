@extends('layouts.app')

@section('content')
<form id="tableSelectForm" action="{{ route('printProductsOrder', ['venta' => $venta->ventaId, 'client' => !empty($client)? $client->id:''] ) }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <p class="h2 text-black">Resumen</p>
        </div>
    </div>
    <input type="hidden" name="apply" value="1">
    <input type="hidden" name="clientId" value="{{ !empty($client)? $client->id:'' }}">
    <div class="row">
        <div class="col-md-12 py-2">
            <div class="card">

                <div class="card-body">
                    @include('puntoventa.comandas.resumebody')
                </div>
            
                <div class="card-footer">
                    <div class="wk-wizard-nav">
                    @if ($enableFooter)
                        <a class="btn btn-outline-primary custom" href="{{ route('foodsTab', ['venta' => $venta['ventaId'], 'client' => optional($client)->id]) }}" role="button">Anterior</a>
                        <button type="submit" form="cancelarVentaForm" class="btn btn-outline-secondary custom">Regresar</button>
                        {{-- Manda la orden a cocina/barra --}}
                        <button type="submit" class="btn btn-primary custom" name="next">Finalizar</button>
                    @else
                        {{-- Consulta desde la bandeja: no imprime ni descarta nada --}}
                        <a class="btn btn-outline-secondary custom" href="{{ route('comandas') }}" role="button">Volver a la bandeja</a>
                        @if ($venta->estaAbierta() && $venta->tiene_ronda_pendiente)
                            {{-- Ronda capturada que no se mandó a cocina: se retoma en el wizard --}}
                            <a class="btn btn-primary custom" href="{{ route('drinksTab', ['venta' => $venta['ventaId'], 'client' => optional($client)->id]) }}" role="button">Continuar ronda</a>
                        @endif
                    @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
{{-- Fuera de otros forms; el botón Regresar lo envía con form="cancelarVentaForm" --}}
<form id="cancelarVentaForm" method="POST" action="{{ route('cancelarVenta', $venta['ventaId']) }}">
    @csrf
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/comandas.js') }}"></script>
@endpush

