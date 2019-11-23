<div class="modal fade bd-example-modal-lg" id="dialogFoodDetails" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document" style="z-index: 999999999;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Agregar</h5>
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
              <input type="text" hidden name="cantidad" id="cantidad" value="1">

              <div class="container">
              <div class="row">
                  <div class="col-md-12">
                      <h5 class="modal-title">
                          <label id='foodName'></label>
                      </h5>
                  </div>
              </div>
              <div class="row">
                  <div class="col-md-6">
                      <select class="custom-select" size="8" name="piecesnumber" id="pieces">
                          <option value="1">5 Piezas</option>
                          <option value="2">10 Piezas</option>
                          <option value="3">15 Piezas</option>
                          <option value="4">20 Piezas</option>
						  <option value="5">Mitad</option>
                          <option value="7">Todas</option>
                      </select>
                  </div>
                  <div class="col-md-6">
                      <select class="custom-select" size="8" name="flavors" id="flavors">
                          <option value="1">A la Diabla</option>
                          <option value="2">Habanero</option>
                          <option value="3">Bufalo</option>
                          <option value="4">Chipotle</option>
                          <option value="5">Mango Habanero</option>
                          <option value="6">Tamarindo</option>
						  <option value="7">Barbecue Hot</option>
                          <option value="8">Barbecue</option>
                          <option value="9">Parmesano</option>
						  <option value="10">Limon</option>
                      </select>
                  </div>
              </div>
              <br>
              <div class="row">
                  {{-- <div class="col-md-10"></div> --}}
                  <div class="col-md-3 offset-md-9">
                      <button type="button" class="btn btn-success" id="addrow">Agregar</button>
                  </div>
              </div>
              <br>
              <div class="row">
                  <table id="myTable" class="table order-list">
                  {{-- <table class="table table-striped"> --}}
                      <thead>
                          <tr>
                              <th scope="col">Cantidad</th>
                              <th scope="col">Sabor</th>
                              <th scope="col">Acción</th>
                          </tr>
                      </thead>
                      <tbody>

                      </tbody>
                  </table>
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
