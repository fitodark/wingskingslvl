<div class="row justify-content-center">
    <div class="col-6 py-2">
                <label for="location">Ubicación</label>
                <input class="form-control" id="folio" type="text" placeholder=".form-control-sm"
                value="{{ ($venta->type == 1)? 'Local':'Domicilio'}}" readonly>
    </div>
    <div class="col-6 py-2">
                @if ($venta->type == 2)
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
    <div class="col-6 py-2">
                <label for="total">Total</label>
                <input class="form-control" id="total" type="text" placeholder=".form-control-sm"
                value="@money($venta->montoTotal)" readonly>
    </div>
    <div class="col-6 py-2"></div>
</div>
<div class="row justify-content-center">
    <div class="col-6 py-5">
              <table class="table table-striped table-sm">
                  <tr>
                      <th>Nombre</th>
                      <th>Cantidad</th>
                      <th>Total</th>
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
                  </tr>
                  {{-- <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                  </tr> --}}
                  @empty
                  <tr>
                  </tr>
                  @endforelse
              </table>
    </div>
    <div class="col-6 py-5">
                <table class="table table-striped table-sm">
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                    </tr>
                    @forelse ( $arrayBebidas as $record)
                        @if ($venta->order == $record->order)
                            <tr class="table-success">
                        @else
                            <tr>
                        @endif
                        <td>{{ $record['product']->name }} - {{ $record['product']->detail }}</td>
                        <td>{{ $record['descripcion'] }}</td>
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