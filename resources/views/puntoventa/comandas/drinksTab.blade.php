@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <p class="h2 text-black">Seleccionar Bebidas</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 py-2">
            <div class="card">

                <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="col-6">
                            <table class="table table-striped table-sm">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Precio</th>
                                    <th>Acción</th>
                                </tr>
                                @foreach ($drinkProducts as $drink)
                                <tr>
                                    <td>{{ $drink->name }} - {{ $drink->detail }}</td>
                                    <td>@money($drink->price)</td>
                                    <td>
                                        <button type="button" class="btn btn-outline-success btn-sm" data-toggle="modal"
                                            data-tab="drinks"
                                            data-productname="{{ $drink->name }} - {{ $drink->detail }}"
                                            data-ventaid="{{ $venta['ventaId'] }}"
                                            data-productid="{{ $drink->id }}"
                                            data-price="{{ $drink->price }}"
                                            data-target="#dialogDetails">
                                            Agregar
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                            {!! $drinkProducts->links() !!}

                        </div>
                        
                        <div class="col-6">
                            <table class="table table-striped table-sm">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Cantidad</th>
                                    <th>Total</th>
                                    <th>Acción</th>
                                </tr>
                                @forelse ( $arrayBebidas as $record)
                                @if ($venta->order == $record->order)
                                    <tr class="table-success">
                                @else
                                    <tr>
                                @endif
                                    <td>{{ $record['product']->name }} - {{ $record['product']->detail }}
                                        @if ($record->product->type != 2 && is_null($record['descripcion']) == false)
                                            <br>[{{ $record['descripcion'] }}]
                                        @endif
                                    </td>
                                    <td>{{ $record['cantidad'] }}</td>
                                    <td>@money($record['montoVenta'])</td>
                                    <td>
                                        @if ($record->delete_flag == true)
                                            <form action="{{ route('eliminarProducto', $record['ventasProductosId']) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <input type="text" hidden name="tab" id="tab" value="drinks">

                                                <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    {{-- <td>@arrayPrint($arrayBebidas)</td> --}}
                                </tr>
                                @endforelse
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-center">
                    <div class="row justify-content-center">
                        <div class="col-3"></div>
                        <div class="col-2 justify-content-center">
                            <a class="btn btn-primary custom" href="{{ route('create', ['venta' => $venta['ventaId'], 'client' => $client['id']]) }}" role="button">Anterior</a>
                        </div>
                        <div class="col-2 justify-content-center">
                            <a class="btn btn-outline-secondary custom" href="{{ route('cancelarVenta', $venta['ventaId']) }}" role="button">Regresar</a>
                        </div>
                        <div class="col-2 justify-content-center">
                            <a class="btn btn-primary custom" href="{{ route('foodsTab', ['venta' => $venta['ventaId'], 'client' => $client['id']]) }}" role="button">Siguiente</a>
                        </div>
                        <div class="col-3"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@include('puntoventa.comandas.dialogDetails')
@endsection
