<div class="table-responsive">
<table class="table table-striped table-sm wk-bandeja">
    <thead>
    <tr>
        <th>#</th>
        <th>Ubicación</th>
        <th>Tipo</th>
        <th>Estatus</th>
        <th>Total</th>
        @if (Route::currentRouteName() == 'comandas')
            <th class="wk-col-acciones">Acciones</th>
        @endif
    </tr>
    </thead>
    @forelse ($ventas as $venta)
    @if ($venta->venta_agente == true)
        <tr class="table-primary">
    @else
        <tr>
    @endif
        <td>
            {{ $venta->ventaId }}
            @if ($venta->venta_agente == true)
                <br><span class="badge badge-primary" title="Venta creada por el agente de WhatsApp">WhatsApp</span>
            @endif
            @if ($venta->estaAbierta() && $venta->sabores_con_problema > 0)
                <br><span class="badge badge-danger" title="Hay órdenes de alitas cuyas piezas por sabor no suman lo vendido; revisa el detalle">Revisar sabores</span>
            @endif
        </td>
        <td>
            @if ($venta->esLocal())
                <b>{{ $venta->etiqueta_cuenta ?: 'Sin mesa' }}</b><br>
                {{ !empty($venta->client)? $venta->client->name:'' }}
                {{ !empty($venta->client)? '('.$venta->client->phone.')':'' }}
            @endif
            @if ($venta->esDomicilio())
                {{ $venta->client->name }} ( {{ $venta->client->phone }} )<br>
                {{ $venta->direccion_envio ?: $venta->client->address }}
            @endif
            @if ($venta->esParaLlevar())
                Para Llevar (Pasan a recoger)<br>
                @if (!empty($venta->nombre_cuenta))
                    <b>{{ $venta->nombre_cuenta }}</b><br>
                @endif
                {{ !empty($venta->client)? $venta->client->name:'' }}
                {{ !empty($venta->client)? '('.$venta->client->phone.')':'' }}
            @endif
        </td>
        <td>@ventaType($venta->type)</td>
        <td>@ventaEstatus($venta->estatus)</td>
        <td class="text-nowrap">@money($venta->montoTotal)<br>
            @discountApply($venta->apply_discount)
            @paymenttype($venta->payment_type)
        </td>
        @if (Route::currentRouteName() == 'comandas')
        <td>
            {{-- Botones con ícono y texto, de tamaño cómodo para tablet --}}
            <div class="wk-acciones">
                @if ($venta->estaAbierta())
                    <form method="POST" action="{{ route('addMoreProducts', [$venta->ventaId, $venta->client_id]) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-success" title="Agregar productos">
                            <i class="fa-solid fa-plus"></i><span>Agregar</span>
                        </button>
                    </form>
                @endif

                <a class="btn btn-outline-secondary" href="{{ route('ventaprint', $venta->ventaId) }}" title="Imprimir ticket">
                    <i class="fa-solid fa-print"></i><span>Ticket</span>
                </a>

                @if ($venta->estaAbierta())
                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" title="Cobrar y cerrar la venta"
                        data-target="#finalizarVentaModal"
                        data-ventaid="{{ $venta->ventaId }}"
                        data-location="{{ ($venta->esDomicilio())? ($venta->direccion_envio ?: $venta->client->address):$venta->ubicacion }}"
                        data-total="{{ $venta->montoTotal }}"
                        data-montototaldescuento="{{ $venta->montoTotalDescuento }}"
                        data-discountpercentage="{{ optional($discountPercentageObject)->value ?? 0 }}"
                        data-applydiscount="{{ $venta->apply_discount }}">
                        <i class="fa-solid fa-cash-register"></i><span>Cobrar</span>
                    </button>
                @endif

                <a class="btn btn-outline-info" href="{{ route('resumeTab', [$venta->ventaId, $venta->client_id]) }}" title="Ver detalle">
                    <i class="fa-solid fa-list"></i><span>Detalle</span>
                </a>
            </div>
        </td>
        @endif
    </tr>
    @empty
    <tr>
        <td colspan="6">Sin registros</td>
    </tr>
    @endforelse
</table>
</div>
{!! $ventas->links() !!}
