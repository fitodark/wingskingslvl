@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Seleccionar Alimentos</div>

                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">

                                <div class="row justify-content-md-center">
                                    <div class="col">
                                        <table class="table table-striped">
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Precio</th>
                                                <th>Acción</th>
                                            </tr>
                                            @foreach ($foodProducts as $food)
                                            <tr>
                                                <td>{{ $food->name }} - {{ $food->detail }}</td>
                                                <td>@money($food->price)</td>
                                                @if ($food->type === 2)
                                                  <td>
                                                      <button type="button" class="btn btn-success" data-toggle="modal"
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
                                                      <button type="button" class="btn btn-success" data-toggle="modal"
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
                                    <div class="col col-lg-1">

                                    </div>
                                    <div class="col">
                                        <table class="table table-striped">
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

                                                        <button type="submit" class="btn btn-danger">Eliminar</button>
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


                        </li>
                    </ul>
                </div>

                <div class="card-footer">
                    <div class="mx-auto" style="width: 200px;">
                        <nav aria-label="Page navigation example">
                          <ul class="pagination">
                            <li class="page-item">
                                <a class="btn btn-primary" href="{{ route('drinksTab', $venta['ventaId']) }}" role="button">Anterior</a>
                            </li>
                            <li class="page-item">
                                <a class="btn btn-warning" href="{{ route('cancelarVenta', $venta['ventaId']) }}" role="button">Cancelar</a>
                            </li>
                            <li class="page-item">
                                <a class="btn btn-primary" href="{{ route('resumeTab', $venta['ventaId']) }}" role="button">Siguiente</a>
                            </li>
                          </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('puntoventa.comandas.dialogFoodDetails', ['piecesList' => $piecesList, 'flavorsList' => $flavorsList])
@include('puntoventa.comandas.dialogDetails')
@endsection
