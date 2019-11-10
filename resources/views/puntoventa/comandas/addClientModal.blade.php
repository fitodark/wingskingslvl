<div class="modal" id="addClientModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document" style="z-index: 999999999;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Agregar Cliente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST" action="{{ route('addClient') }}">
          @csrf
          <div class="modal-body">
              <input type="text" hidden name="ventaid" id="ventaid">
              <input type="text" hidden name="type" id="type">
              <div class="form-row">
                  <div class="form-group col-md-6">
                      <label for="clientName">Nombre</label>
                      <input id="clientName" name="clientName" type="text" class="form-control" placeholder="Nombre del cliente">
                  </div>
                  <div class="form-group col-md-6">
                      <label for="clientPhone">Teléfono</label>
                      <input id="clientPhone" name="clientPhone" type="text" class="form-control" placeholder="Teléfono del cliente">
                  </div>
              </div>
              <div class="form-row">
                  <div class="form-group col-md-6">
                      <label for="clientAddress">Dirección</label>
                      <input id="clientAddress" name="clientAddress" type="text" class="form-control" placeholder="Dirección del cliente">
                  </div>
                  <div class="form-group col-md-6">
                      <label for="clientReference">Referencia</label>
                      <input id="clientReference" name="clientReference" type="text" class="form-control" placeholder="Referencia">
                  </div>
              </div>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary">Agregar</button>
          </div>
      </form>
    </div>
  </div>
</div>
</div>
