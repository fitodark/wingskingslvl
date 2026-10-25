<div class="row justify-content-center">
    <div class="col-md-6 py-2">
                <label for="location">Ubicación</label>
                <input class="form-control" id="folio" type="text" placeholder=".form-control-sm"
                value="{{ $venta->ubicacion }}" readonly>
    </div>
    <div class="col-md-6 py-2">
                @if (!$venta->esLocal() && !empty($venta->client))
                    <label for="location">Cliente</label>
                    <input class="form-control" id="location" type="text" placeholder=".form-control-sm"
                    value="{{ $venta->client->name }} ( {{ $venta->client->phone }} ) - {{ $venta->direccion_envio ?: $venta->client->address }} - {{ $venta->client->reference }}" readonly>
                @elseif ($venta->esParaLlevar())
                    <label for="location">Cuenta</label>
                    <input class="form-control" id="location" type="text" placeholder=".form-control-sm"
                    value="{{ $venta->nombre_cuenta }}" readonly>
                @elseif ($venta->esLocal())
                    <label for="location">Mesa / Cuenta</label>
                    <input class="form-control" id="location" type="text" placeholder=".form-control-sm"
                    value="{{ $venta->etiqueta_cuenta ?: 'Sin mesa' }}" readonly>
                @endif
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-6 col-lg-3 py-2">
                <label for="total">Total</label>
                <input class="form-control" id="total" type="text" placeholder=".form-control-sm"
                value="@money($venta->montoTotal)" readonly>
    </div>
    <div class="col-6 col-lg-3 py-2">
                <label for="discountPercentage">Total con descuento</label>
                <input class="form-control" id="discountPercentage" type="text" placeholder=".form-control-sm"
                value="@money($montoDescuento)" readonly>
    </div>
    <div class="col-4 col-lg-2 py-2">
                <label for="discountPercentage">Descuento</label>
                <input class="form-control" id="discountPercentage" type="text" placeholder=".form-control-sm"
                value="@discount($discountPercentage)" readonly>
    </div>
    <div class="col-4 col-lg-2 py-2">
                <label for="totalVentas">Total de Ventas</label>
                <input class="form-control" id="totalVentas" type="text" placeholder=".form-control-sm"
                value="{{ !empty($objDiscountPercentage)? $objDiscountPercentage->total:
                    (!empty($objTotalSales)? $objTotalSales->totalVentas:0) }}" readonly>
    </div>
    <div class="col-4 col-lg-2 py-2">
            <label for="clientSalesDetailsModal">Historial</label><br>
            <button type="button" id="clientSalesDetailsModal" class="btn btn-outline-primary" data-toggle="modal"
                data-target="#clientSalesDetailsModalResume"
                data-clientid="{{ !empty($venta->client)? $venta->client->id:'' }}"
                data-sales="">Ver detalle</button>
            @include('puntoventa.comandas.salesdetailmodal.clientSalesDetailsModalResume')
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-12 col-lg-6 py-3">
              <p class="h5 text-black">Alimentos</p>
              <table class="table table-striped table-sm">
                  <tr>
                      <th style="width: 70%;">Nombre</th>
                      <th style="width: 15%;">Cantidad</th>
                      <th style="width: 15%;">Total</th>
                  </tr>
                  @forelse ( $arrayComidas as $record)
                    @if ($record->esPendienteDeRonda($venta))
                        <tr class="table-success">
                    @else
                        <tr>
                    @endif
                      <td>{{ $record['product']->name }} - {{ $record['product']->detail }}
                        @if (!$record->product->llevaSabores() && is_null($record['descripcion']) == false)
                            <br>[{{ $record['descripcion'] }}]
                        @endif
                        @if ($record->product->llevaSabores())
                            <br>
                            {{-- Sin el encabezado "Cantidad - Sabor"; un JSON mal formado no rompe la vista --}}
                            @foreach (\App\Support\SaboresOrden::renglones($record['descripcion']) ?: [] as $renglon)
                                    {{ $renglon['cantidad'] }} - {{ $renglon['sabor'] }} <br>
                            @endforeach
                            @if ($record->problema_sabores)
                                <span class="badge badge-danger">{{ $record->problema_sabores }}</span>
                            @endif
                        @endif
                        </td>
                        <td>{{ $record['cantidad'] }}</td>
                        <td>
                        @if ($record->estaActivo())
                            @money($record['montoVenta'])
                        @else
                            Eliminado
                        @endif
                        </td>
                  </tr>
                  @empty
                  <tr>
                  </tr>
                  @endforelse
              </table>
    </div>
    <div class="col-12 col-lg-6 py-3">
                <p class="h5 text-black">Bebidas</p>
                <table class="table table-striped table-sm">
                    <tr>
                        <th style="width: 70%;">Nombre</th>
                        <th style="width: 15%;">Cantidad</th>
                        <th style="width: 15%;">Total</th>
                    </tr>
                    @forelse ( $arrayBebidas as $record)
                        @if ($record->esPendienteDeRonda($venta))
                            <tr class="table-success">
                        @else
                            <tr>
                        @endif
                        <td>{{ $record['product']->name }} - {{ $record['product']->detail }}
                            @if (!$record->product->llevaSabores() && is_null($record['descripcion']) == false)
                                <br>[{{ $record['descripcion'] }}]
                            @endif
                        </td>
                        <td>{{ $record['cantidad'] }}</td>
                        <td>
                            @if ($record->estaActivo())
                                @money($record['montoVenta'])
                            @else
                                Eliminado
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                    </tr>
                    @endforelse
                </table>
    </div>
</div>
