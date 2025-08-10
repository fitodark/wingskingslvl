<div class="modal fade" id="addClientModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document" style="z-index: 999999999;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Agregar Cliente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form class="needs-validation" method="POST" action="{{ route('addClient') }}" novalidate>
          @csrf
          <div class="modal-body">
              @include('puntoventa.clientes.clientBodyForm', array('disabled'=>'false'))
          </div>
          <!-- <div class="alert alert-danger" style="display:none"></div> -->
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
              <!-- <button type="submit" class="btn btn-primary" id="btnAddClient">Agregar</button> -->
              <button type="submit" class="btn btn-primary">Agregar</button>
          </div>
      </form>
    </div>
  </div>
</div>
