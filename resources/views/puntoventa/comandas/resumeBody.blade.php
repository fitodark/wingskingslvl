<div class="row justify-content-center">
    <div class="col-6 py-2">
                <label for="location">Ubicación</label>
                <input class="form-control" id="folio" type="text" placeholder=".form-control-sm"
                value="{{ ($venta->type == 1)? $venta->dinerstable->name:(($venta->type == 2)? 'Domicilio':'Para Llevar (Pasan a recoger)')}}" readonly>
    </div>
    <div class="col-6 py-2">
                @if ($venta->type == 2 || $venta->type == 3)
                    <label for="location">Cliente</label>
                    <input class="form-control" id="location" type="text" placeholder=".form-control-sm"
                    value="{{ $venta->client->name }} ( {{ $venta->client->phone }} ) - {{ $venta->client->address }} - {{ $venta->client->reference }}" readonly>
                @elseif ($venta->type == 1)
                    <label for="location">Mesa</label>
                    <input class="form-control" id="location" type="text" placeholder=".form-control-sm"
                    value="{{ $venta->dinerstable->name }}" readonly>
                @endif
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-3 py-2">
                <label for="total">Total</label>
                <input class="form-control" id="total" type="text" placeholder=".form-control-sm"
                value="@money($venta->montoTotal)" readonly>
    </div>
    <div class="col-3 py-2">
                <label for="discountPercentage">Total con descuento</label>
                <input class="form-control" id="discountPercentage" type="text" placeholder=".form-control-sm"
                value="@money($montoDescuento)" readonly>
    </div>
    <div class="col-2 py-2">
                <label for="discountPercentage">Descuento</label>
                <input class="form-control" id="discountPercentage" type="text" placeholder=".form-control-sm"
                value="@discount($discountPercentage)" readonly>
    </div>
    <div class="col-2 py-2">
                <label for="totalVentas">Total de Ventas</label>
                <input class="form-control" id="totalVentas" type="text" placeholder=".form-control-sm"
                value="{{ !empty($objDiscountPercentage)? $objDiscountPercentage->total:
                    (!empty($objTotalSales)? $objTotalSales->totalVentas:0) }}" readonly>
    </div>
    <div class="col-2 py-2">
            <label for="clientSalesDetailsModal">Historial</label><br>
            <button type="button" id="clientSalesDetailsModal" class="btn btn-outline-primary" data-toggle="modal"
                data-target="#clientSalesDetailsModalResume"
                data-clientid="{{ !empty($venta->client)? $venta->client->id:'' }}"
                data-sales="">Ver detalle</button>
            @include('puntoventa.comandas.salesdetailmodal.clientSalesDetailsModalResume')
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-6 py-5">
              <table class="table table-striped table-sm">
                  <tr>
                      <th style="width: 70%;">Nombre</th>
                      <th style="width: 15%;">Cantidad</th>
                      <th style="width: 15%;">Total</th>
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
                  </tr>
                  @empty
                  <tr>
                  </tr>
                  @endforelse
              </table>
    </div>
    <div class="col-6 py-5">
                <table class="table table-striped table-sm">
                    <tr>
                        <th style="width: 70%;">Nombre</th>
                        <th style="width: 15%;">Cantidad</th>
                        <th style="width: 15%;">Total</th>
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
                    </tr>
                    @empty
                    <tr>
                    </tr>
                    @endforelse
                </table>
    </div>
</div>