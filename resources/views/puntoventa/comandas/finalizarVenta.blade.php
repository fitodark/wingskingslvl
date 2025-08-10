<div class="modal" id="finalizarVentaModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="z-index: 999999999;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Finalizar Venta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('cerrarVenta') }}">
            @csrf
                <input type="text" hidden name="applydiscount" id="applydiscount">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="location">Folio</label>
                            <input class="form-control form-control-sm" id="folio" type="text" placeholder=".form-control-sm" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="location">Ubicación</label>
                            <input class="form-control form-control-sm" id="location" type="text" placeholder=".form-control-sm" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="total">Monto Total</label>
                            <input class="form-control form-control-sm" id="total" type="text" placeholder=".form-control-sm" readonly>
                        </div>
                    </div>
                    <div class="form-row" id="rowdiscount">
                        <div class="form-group col-md-6">
                            <label for="total">Total con descuento</label>
                            <input class="form-control form-control-sm" id="montototaldescuento" type="text" placeholder=".form-control-sm" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="total">% Descuento</label>
                            <input class="form-control form-control-sm" id="discountpercentage" type="text" placeholder=".form-control-sm" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="quantity" class="col-form-label">Cantidad:</label>
                            <input name="cantidad" type="number" class="form-control" name="quantity" id="quantity" value="{{ old('quantiy') }}">
                            <div class="alert alert-danger" style="display:none"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary" id="finalizarVenta">Finalizar Venta</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
