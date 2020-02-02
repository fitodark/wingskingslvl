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
                          @foreach ($piecesList as $key => $value)
                          <option value="{{ $key }}">{{$value->value}}</option>
                          @endforeach
                      </select>
                  </div>
                  <div class="col-md-6">
                      <select class="custom-select" size="8" name="flavors" id="flavors">
                          @foreach ($flavorsList as $key => $value)
                          <option value="{{ $key }}">{{$value->value}}</option>
                          @endforeach
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
                      <tbody id="tableBody">

                      </tbody>
                  </table>
              </div>
              </div>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary" id="addFoodRow">Agregar</button>
          </div>
      </form>
    </div>
  </div>
</div>
</div>
