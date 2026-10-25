<div class="modal fade" id="finalizarVentaModal" tabindex="-1" role="dialog" aria-labelledby="finalizarVentaTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="finalizarVentaTitulo">Cobrar venta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{-- Se envía por AJAX (public/js/comandas.js); Enter en "Recibido" también cobra. --}}
            <form method="POST" action="{{ route('cerrarVenta') }}">
            @csrf
                <input type="hidden" name="applydiscount" id="applydiscount">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-4">
                            <label for="folio">Folio</label>
                            <input class="form-control" id="folio" type="text" readonly>
                        </div>
                        <div class="form-group col-8">
                            <label for="location">Ubicación</label>
                            <input class="form-control" id="location" type="text" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12">
                            <label for="total">Monto Total</label>
                            <input class="form-control" id="total" type="text" readonly>
                        </div>
                    </div>
                    <div class="form-row" id="rowdiscount">
                        <div class="form-group col-6">
                            <label for="montototaldescuento">Total con descuento</label>
                            <input class="form-control" id="montototaldescuento" type="text" readonly>
                        </div>
                        <div class="form-group col-6">
                            <label for="discountpercentage">% Descuento</label>
                            <input class="form-control" id="discountpercentage" type="text" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12">
                            <label for="paymenttype">Tipo de pago</label>
                            <select name="paymenttype" id="paymenttype" class="form-control">
                                @foreach (\App\Venta::PAGOS as $clave => $nombre)
                                    <option value="{{ $clave }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row align-items-end">
                        <div class="form-group col-8">
                            <label for="quantity">Recibido</label>
                            <input type="number" class="form-control form-control-lg" name="quantity" id="quantity"
                                inputmode="decimal" min="0" step="0.01" autocomplete="off">
                        </div>
                        <div class="form-group col-4">
                            <button type="button" class="btn btn-outline-secondary btn-block" id="pagoExacto">Pago exacto</button>
                        </div>
                    </div>
                    <p class="h5 mb-2" id="cambio"></p>
                    <div class="alert alert-danger" style="display:none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="finalizarVenta">Cobrar y cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
