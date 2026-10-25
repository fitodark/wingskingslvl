<div class="modal fade" id="eliminarProductoModal" tabindex="-1" role="dialog" aria-labelledby="eliminarProductoTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eliminarProductoTitulo">Confirme la eliminación del Producto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{-- Se envía por AJAX (public/js/comandas.js) --}}
            <form method="POST" action="{{ route('eliminarProducto') }}">
            @csrf
                <div class="modal-body">
                    <input type="hidden" name="productoid">
                    <input type="hidden" name="modaltab">
                    <div class="form-group">
                        <label for="pinEliminar">PIN de quien autoriza:</label>
                        <input id="pinEliminar" class="form-control form-control-lg" type="password" name="pin"
                            inputmode="numeric" autocomplete="off" required>
                    </div>
                    <div class="alert alert-danger" style="display:none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="eliminarProducto">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>
