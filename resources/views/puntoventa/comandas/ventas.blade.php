<table class="table table-striped table-sm">
    <thead>
    <tr>
        <th width="5%">#</th>
        <th width="40%">Ubicación</th>
        <th width="10%">Tipo</th>
        <th width="10%">Estatus</th>
        <th width="15%">Total</th>
        @if (Route::currentRouteName() == 'comandas')
            <th width="20%">Accion</th>
        @endif
    </tr>
    </thead>
    @forelse ($ventas as $venta)
    <tr>
        <td>{{ $venta->ventaId }}</td>
        <td>
            @if ($venta->type === 1)
                <b>{{ $venta->dinerstable->name }}</b><br>
                {{ !empty($venta->client)? $venta->client->name:'' }}
                {{ !empty($venta->client)? '('.$venta->client->phone.')':'' }}
            @endif 
            @if ($venta->type === 2)
                {{ $venta->client->name }} ( {{ $venta->client->phone }} )<br>
                {{ $venta->client->address }}
            @endif
            @if ($venta->type === 3)
                Para Llevar (Pasan a recoger)<br>
                {{ !empty($venta->client)? $venta->client->name:'' }}
                {{ !empty($venta->client)? '('.$venta->client->phone.')':'' }}
            @endif
        </td>
        <td>@ventaType($venta->type)</td>
        <td>@ventaEstatus($venta->estatus)</td>
        <td>@money($venta->montoTotal)<br>
            @discountApply($venta->apply_discount)
        </td>
        @if (Route::currentRouteName() == 'comandas')
        <td>
            <form method="POST">

                <!-- <button type="button" class="btn btn-info" data-toggle="modal" data-target="#exampleModal">Detalles</button> -->

                @if ($venta->estatus == 1)
                    <a class="ico-add" href="{{ route('addMoreProducts', [$venta->ventaId, $venta->client_id]) }}"></a>
                @else
                    <button type="ico-add" class="btn btn-outline-info"></button>
                @endif

                <a class="ico-print" href="{{ route('ventaprint', $venta->ventaId) }}"></a>

                @if ($venta->estatus == 1)
                    <button type="button" class="btn" data-toggle="modal"
                        data-target="#finalizarVentaModal"
                        data-ventaid="{{ $venta->ventaId }}"
                        data-location="{{ ($venta->type === 1)? $venta->dinerstable->name:$venta->client->address }}"
                        data-total="{{ $venta->montoTotal }}"
                        data-montototaldescuento="{{ $venta->montoTotalDescuento }}"
                        data-discountpercentage="{{ $discountPercentageObject->value }}"
                        data-applydiscount="{{ $venta->apply_discount }}">
                        <i class="ico-delete"></i>
                    </button>
                @else
                    <button type="ico-delete" class="btn btn-outline-success"></button>
                @endif

                <a class="ico-details" href="{{ route('resumeTab', [$venta->ventaId, $venta->client_id]) }}"></a>
            </form>
        </td>
        @endif
    </tr>
    @empty
    <tr>
        <td colspan="4">Sin registros</td>
    </tr>
    @endforelse
</table>
{!! $ventas->links() !!}
