<div class="row justify-content-center">

        <table class="table table-striped">
            <tr>
                <th>#</th>
                <th>Ubicación</th>
                <th>Tipo</th>
                <th>Estatus</th>
                <th>Total</th>
                @if (Route::currentRouteName() == 'comandas')
                  <th width="440px">Accion</th>
                @endif
            </tr>
            @foreach ($ventas as $venta)
            <tr>
                <td>{{ $venta->ventaId }}</td>
                <td>
                  @if ($venta->type === 1)
                      {{ $venta->dinerstable->name }}
                  @else
                      {{ $venta->client->name }} ( {{ $venta->client->phone }} )<br>
                      {{ $venta->client->address }}
                  @endif
                </td>
                <td>@ventaType($venta->type)</td>
                <td>@ventaEstatus($venta->estatus)</td>
                <td>@money($venta->montoTotal)</td>
                @if (Route::currentRouteName() == 'comandas')
                <td>
                    <form method="POST">

                        <!-- <button type="button" class="btn btn-info" data-toggle="modal" data-target="#exampleModal">Detalles</button> -->

                        @if ($venta->estatus == 1)
                            <a class="btn btn-info" href="{{ route('addMoreProducts', $venta->ventaId) }}">Agregar</a>
                        @else
                            <button type="button" class="btn btn-outline-info">Agregar</button>
                        @endif

                        <a class="btn btn-primary" href="{{ route('ventaprint', $venta->ventaId) }}">Imprimir Cuenta</a>

                        @if ($venta->estatus == 1)
                            <button type="button" class="btn btn-success" data-toggle="modal"
                                data-target="#finalizarVentaModal"
                                data-ventaid="{{ $venta->ventaId }}"
                                data-location="{{ ($venta->type === 1)? $venta->dinerstable->name:$venta->client->address }}"
                                data-total="{{ $venta->montoTotal }}">
                                Finalizar
                            </button>
                        @else
                            <button type="button" class="btn btn-outline-success">Finalizar</button>
                        @endif

                        <a class="btn btn-secondary" href="{{ route('resumeTab', [$venta->ventaId, 0]) }}">Detalles</a>
                    </form>
                </td>
                @endif
            </tr>
            @endforeach
        </table>
        {!! $ventas->links() !!}

</div>
