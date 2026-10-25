<div class="modal fade" id="cerrarcortecajamodal" tabindex="-1" role="dialog" aria-labelledby="cerrarCorteTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cerrarCorteTitulo">Cerrar Corte de Caja</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{-- Se envía por AJAX (public/js/cortecaja.js) --}}
            <form method="POST" action="{{ route('cerrarcortecaja') }}">
            @csrf
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="idcorte">Folio</label>
                            <input class="form-control" id="idcorte" type="text" readonly>
                        </div>
                        <div class="form-group col-6">
                            <label for="fechaapertura">Fecha Apertura</label>
                            <input class="form-control" id="fechaapertura" type="text" readonly>
                        </div>
                    </div>
                    <div class="alert alert-danger" style="display:none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="cerrarcortecaja">Cerrar Corte</button>
                </div>
            </form>
        </div>
    </div>
</div>
