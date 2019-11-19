@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Resumen</div>

                <div class="card-body">
                    @include('puntoventa.comandas.resumebody')
                </div>

                <div class="card-footer">
                    @if ($enableFooter)
                    <div class="mx-auto" style="width: 200px;">
                        <nav aria-label="Page navigation example">
                          <ul class="pagination">
                            <li class="page-item">
                                <a class="btn btn-primary" href="{{ route('foodsTab', $venta['ventaId']) }}" role="button">Anterior</a>
                            </li>
                            <li class="page-item">
                                <a class="btn btn-warning" href="{{ route('cancelarVenta', $venta['ventaId']) }}" role="button">Cancelar</a>
                            </li>
                            <li class="page-item">
                                <a class="btn btn-primary" href="{{ route('printProductsOrder', $venta['ventaId']) }}" role="button">Finalizar</a>
                            </li>
                          </ul>
                        </nav>
                    </div>
                    @else
                    <div class="mx-auto" style="width: 200px;">
                        <nav aria-label="Page navigation example">
                          <ul class="pagination">
                            <li class="page-item">
                                <a class="btn btn-warning" href="{{ route('cancelarVenta', $venta['ventaId']) }}" role="button">Regresar</a>
                            </li>
                          </ul>
                        </nav>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
