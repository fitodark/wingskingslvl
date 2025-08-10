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
                    <div class="row justify-content-center">
                        <div class="col-6">
                            <table class="table table-striped table-sm">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Precio</th>
                                    <th>Acción</th>
                                </tr>
                                @foreach ($foodProducts as $food)
                                <tr>
                                    <td>{{ $food->name }} - {{ $food->detail }}</td>
                                    <td width="80px;">@money($food->price)</td>
                                    @if ($food->type === 2)
                                        <td>
                                            <button type="button" class="btn btn-outline-success btn-sm" data-toggle="modal"
                                                data-tab="food"
                                                data-foodname="{{ $food->name }} - {{ $food->detail }}"
                                                data-ventaid="{{ $venta['ventaId'] }}"
                                                data-productid="{{ $food->id }}"
                                                data-price="{{ $food->price }}"
                                                data-target="#dialogFoodDetails">
                                                Agregar
                                            </button>
                                        </td>
                                    @else
                                        <td>
                                            <button type="button" class="btn btn-outline-success btn-sm" data-toggle="modal"
                                                data-tab="food"
                                                data-productname="{{ $food->name }} - {{ $food->detail }}"
                                                data-ventaid="{{ $venta['ventaId'] }}"
                                                data-productid="{{ $food->id }}"
                                                data-price="{{ $food->price }}"
                                                data-target="#dialogDetails">
                                                Agregar
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                                @endforeach
                            </table>
                            {!! $foodProducts->links() !!}

                        </div>

                        <div class="col-6">
                            <table class="table table-striped table-sm">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Cantidad</th>
                                    <th>Total</th>
                                    <th>Acción</th>
                                </tr>
                                @forelse ( $arrayComidas as $record)
                                @if ($venta->order == $record->order)
                                    <tr class="table-success">
                                @else
                                    <tr>
                                @endif
                                    <td>{{ $record['product']->name }} - {{ $record['product']->detail }} 
                                        @if ($record->product->type != 2 && is_null($record['descripcion']) == false)
                                            <br>[{{ $record['descripcion'] }}]
                                        @endif
                                        @if ($record->product->type == 2)
                                            <br>
                                            @foreach (json_decode($record['descripcion'], TRUE) as $key => $value)
                                                    {{ $value[0]['value'] }} - {{ $value[1]['value']}} <br>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>{{ $record['cantidad'] }}</td>
                                    <td>@money($record['montoVenta'])</td>
                                    <td>
                                        <form action="{{ route('eliminarProducto', $record['ventasProductosId']) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <input type="text" hidden name="tab" id="tab" value="food">

                                            <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
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
                            <a class="btn btn-primary custom" href="{{ route('drinksTab', ['venta' => $venta['ventaId'], 'clientId' => $client['id']]) }}" role="button">Anterior</a>
                        </div>
                        <div class="col-2 justify-content-center">
                            <a class="btn btn-outline-secondary custom" href="{{ route('cancelarVenta', $venta['ventaId']) }}" role="button">Regresar</a>
                        </div>
                        <div class="col-2 justify-content-center">
                            <a class="btn btn-primary custom" href="{{ route('resumeTab', ['venta' => $venta['ventaId'], 'clientId' => $client['id']]) }}" role="button">Siguiente</a>
                        </div>
                        <div class="col-3"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@include('puntoventa.comandas.dialogFoodDetails', ['piecesList' => $piecesList, 'flavorsList' => $flavorsList])
@include('puntoventa.comandas.dialogDetails')
@endsection
