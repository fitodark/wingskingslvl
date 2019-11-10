@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Seleccionar Bebidas</div>

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
                                        @foreach ($drinkProducts as $drink)
                                        <tr>
                                            <td>{{ $drink->name }} - {{ $drink->detail }}</td>
                                            <td>@money($drink->price)</td>
                                            <td>
                                                <button type="button" class="btn btn-success" data-toggle="modal"
                                                  data-tab="drinks"
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
                                        @forelse ( $arrayBebidas as $record)
                                        <tr>
                                            <td>{{ $record['product']->name }} - {{ $record['product']->detail }}</td>
                                            <td>{{ $record['cantidad'] }}</td>
                                            <td>@money($record['montoVenta'])</td>
                                            <td>
                                                <form action="{{ route('eliminarProducto', $record['ventasProductosId']) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="text" hidden name="tab" id="tab" value="drinks">

                                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                                </form>
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

                        </li>
                    </ul>
                </div>
            {{-- <form action="{{ route('foodsTab', $venta->ventaId) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('GET') --}}

                <div class="card-footer">
                    <div class="mx-auto" style="width: 200px;">
                        <nav aria-label="Page navigation example">
                          <ul class="pagination">
                            <li class="page-item">
                              {{-- <a class="page-link" href="#" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                                <span class="sr-only">Previous</span>
                              </a> --}}
                                <a class="btn btn-primary" href="{{ route('create', $venta['ventaId']) }}" role="button">Anterior</a>
                            </li>
                            <li class="page-item">
                              {{-- <a class="btn btn-warning" href="#" role="button" href="#">Cancelar</a> --}}
                                <a class="btn btn-warning" href="{{ route('cancelarVenta', $venta['ventaId']) }}" role="button">Cancelar</a>
                            </li>
                            <li class="page-item">
                                {{-- <button type="submit" class="btn btn-primary" name="next">Siguiente</button> --}}
                                <a class="btn btn-primary" href="{{ route('foodsTab', $venta['ventaId']) }}" role="button">Siguiente</a>
                            </li>
                          </ul>
                        </nav>
                    </div>
                </div>
            {{-- </form> --}}
            </div>
        </div>
    </div>
</div>
@include('puntoventa.comandas.dialogDetails')
@endsection
