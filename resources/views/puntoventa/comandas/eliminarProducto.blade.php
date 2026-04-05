<div class="modal" id="eliminarProductoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document" style="z-index: 999999999;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Confirme la eliminación del Producto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('eliminarProducto') }}">
            @csrf
                <div class="modal-body">
                    <input type="text" hidden name="productoid" id="productoid">
                    <input type="text" hidden name="modaltab" id="modaltab">
                    <div class="container">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label>PIN:</label>
                            <input id="pin" class="form-control" type="password" name="pin" value="{{ old('pin') }}" required>
                            <div class="alert alert-danger" style="display:none"></div>
                        </div>
                    </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-outline-secondary custom" data-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary custom" id="eliminarProducto">Confirmar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
