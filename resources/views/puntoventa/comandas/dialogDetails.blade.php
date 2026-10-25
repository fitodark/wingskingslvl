<div class="modal fade" id="dialogDetails" tabindex="-1" role="dialog" aria-labelledby="dialogDetailsTitulo" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="dialogDetailsTitulo">Agregar</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST" action="{{ route('addProductVenta') }}">
          @csrf
          <div class="modal-body">
              <input type="hidden" name="idProduct">
              <input type="hidden" name="ventaId">
              <input type="hidden" name="tab">
              <input type="hidden" name="price">
              <p class="h5" id="productName"></p>
              <div class="form-group">
                  <label for="quantity" class="col-form-label">Cantidad:</label>
                  {{-- Botones - / + para no depender del teclado en tablet --}}
                  <div class="input-group wk-cantidad">
                      <div class="input-group-prepend">
                          <button type="button" class="btn btn-outline-secondary" data-cantidad-paso="-1" data-target-input="#quantity" aria-label="Menos">&minus;</button>
                      </div>
                      <input name="cantidad" type="number" class="form-control text-center" id="quantity"
                          min="1" step="1" value="1" inputmode="numeric" required>
                      <div class="input-group-append">
                          <button type="button" class="btn btn-outline-secondary" data-cantidad-paso="1" data-target-input="#quantity" aria-label="Más">+</button>
                      </div>
                  </div>
              </div>
              <div class="form-group">
                  <label for="productDescription" class="col-form-label">Descripción (opcional):</label>
                  <input name="description" type="text" class="form-control" id="productDescription" autocomplete="off">
              </div>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary">Agregar</button>
          </div>
      </form>
    </div>
  </div>
</div>
