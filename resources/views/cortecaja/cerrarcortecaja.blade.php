<div class="modal" id="cerrarcortecajamodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="z-index: 999999999;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cerrar Corte de Caja</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('cerrarcortecaja') }}">
            @csrf
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="idcorte">Folio</label>
                            <input class="form-control form-control-sm" id="idcorte" type="text" placeholder=".form-control-sm" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="fechaapertura">Fecha Apertura</label>
                            <input class="form-control form-control-sm" id="fechaapertura" type="text" placeholder=".form-control-sm" readonly>
                        </div>
                        <div class="alert alert-danger" style="display:none"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary" id="cerrarcortecaja">Cerrar Corte</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
