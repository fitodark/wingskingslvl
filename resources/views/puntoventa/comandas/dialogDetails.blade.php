<div class="modal" id="dialogDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document" style="z-index: 999999999;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Agregar</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="formQuantity" method="POST" action="{{ route('addProductVenta') }}">
          @csrf
          <div class="modal-body">
              <input type="text" hidden name="idProduct" id="idProduct">
              <input type="text" hidden name="ventaId" id="ventaId">
              <input type="text" hidden name="tab" id="tab">
              <input type="text" hidden name="price" id="price">
              <input type="text" hidden name="description" id="description">
              <div class="container">
              <div class="row">
                  <div class="col-md-12">
                      <h5 class="modal-title">
                          <label id='productName'></label>
                      </h5>
                  </div>
              </div>
              <div class="row">
                  <div class="col-md-12">
                      <div class="form-group">
                          <label for="quantity" class="col-form-label">Cantidad:</label>
                          <input name="cantidad" type="text" class="form-control" id="quantity">
                      </div>
                      <div class="form-group">
                          <label for="description" class="col-form-label">Descripcion:</label>
                          <input name="description" type="text" class="form-control" id="description">
                      </div>
                  </div>
              </div>
              </div>

          </div>
          <div class="modal-footer">
              <button type="button" class="btn-outline-secondary custom" data-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary custom">Agregar</button>
          </div>
      </form>
    </div>
  </div>
</div>
</div>
