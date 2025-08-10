<div class="modal-dialog" role="document" style="z-index: 999999999;">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Resumen de Ventas</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <table id="salesTable" class="table sales-list table-striped table-sm">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody id="salesTableBody">

                </tbody>
            </table>
        </div>
        <div class="alert alert-danger" style="display:none"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
    </div>
</div>