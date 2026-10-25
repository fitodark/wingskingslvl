<div class="modal fade" id="dialogFoodDetails" tabindex="-1" role="dialog" aria-labelledby="foodName" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="foodName">Agregar</h5>
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
              {{-- Lo llena public/js/comandas.js con el JSON de sabores --}}
              <input type="hidden" name="description">
              <input type="hidden" name="cantidad" value="1">

              <div class="row">
                  <div class="col-6">
                      <label for="pieces">Cantidad</label>
                      <select class="custom-select wk-lista" size="7" name="piecesnumber" id="pieces">
                          @foreach ($piecesList as $key => $value)
                          <option value="{{ $key }}">{{$value->value}}</option>
                          @endforeach
                      </select>
                  </div>
                  <div class="col-6">
                      <label for="flavors">Sabor</label>
                      <select class="custom-select wk-lista" size="7" name="flavors" id="flavors">
                          @foreach ($flavorsList as $key => $value)
                          <option value="{{ $key }}">{{$value->value}}</option>
                          @endforeach
                      </select>
                  </div>
              </div>
              <div class="d-flex align-items-center justify-content-between my-3">
                  {{-- "Llevas X de Y piezas" --}}
                  <strong id="piezasContador"></strong>
                  <button type="button" class="btn btn-success" id="addrow">
                      <i class="fa-solid fa-plus"></i> Agregar sabor
                  </button>
              </div>
              <table class="table order-list mb-0">
                  <thead>
                      <tr>
                          <th scope="col">Cantidad</th>
                          <th scope="col">Sabor</th>
                          <th scope="col"></th>
                      </tr>
                  </thead>
                  <tbody id="tableBody">
                  </tbody>
              </table>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary" id="addFoodRow" disabled>Agregar a la cuenta</button>
          </div>
      </form>
    </div>
  </div>
</div>
