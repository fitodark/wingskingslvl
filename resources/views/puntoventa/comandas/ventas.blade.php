<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <table class="table table-striped">
                        <tr>
                            <th>#</th>
                            <th>Ubicación</th>
                            <th>Tipo</th>
                            <th>Estatus</th>
                            <th>Total</th>
                            <th width="280px">Action</th>
                        </tr>
                        @foreach ($ventas as $venta)
                        <tr>
                            <td>{{ $venta->ventaId }}</td>
                            <td>
                              @if ($venta->type === 1)
                                  {{ $venta->dinerstable->name }}
                              @else
                                  {{ $venta->client->address }}
                              @endif
                            </td>
                            <td>@ventaType($venta->type)</td>
                            <td>@ventaEstatus($venta->estatus)</td>
                            <td>@money($venta->montoTotal)</td>
                            <td>
                                <form method="POST">

                                    {{-- <a class="btn btn-info">Detalles</a> --}}
                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#exampleModal">
                                        Detalles
                                    </button>

                                    {{-- <a class="btn btn-primary">Agregar</a> --}}
                                    <a class="btn btn-info" href="{{ route('drinksTab', $venta->ventaId) }}">Agregar</a>

                                    {{-- <button type="submit" class="btn btn-success">Finalizar</button> --}}
                                    <button type="button" class="btn btn-success" data-toggle="modal"
                                        data-target="#finalizarVentaModal"
                                        data-ventaid="{{ $venta->ventaId }}"
                                        data-location="{{ ($venta->type === 1)? $venta->dinerstable->name:$venta->client->address }}"
                                        data-total="{{ $venta->montoTotal }}">
                                        Finalizar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                    {!! $ventas->links() !!}
                </li>
            </ul>
        </div>
    </div>
</div>
