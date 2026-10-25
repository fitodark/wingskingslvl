/*
 * Cortes de caja: modal para cerrar el corte. Requiere wk-common.js.
 */
(function ($, WK) {
    'use strict';
    if (WK.corteCajaCargado) { return; }
    WK.corteCajaCargado = true;

    var $modal = $('#cerrarcortecajamodal');

    $modal.on('show.bs.modal', function (event) {
        var $boton = $(event.relatedTarget);
        $modal.find('#idcorte').val($boton.data('idcorte'));
        $modal.find('#fechaapertura').val($boton.data('fechaapertura'));
        WK.ocultarErrores($modal.find('.alert-danger'));
    });

    $modal.find('form').on('submit', function (event) {
        event.preventDefault();
        WK.enviar({
            url: WK.rutas.cerrarCorteCaja,
            boton: $modal.find('#cerrarcortecaja'),
            alerta: $modal.find('.alert-danger'),
            datos: { idcorte: $modal.find('#idcorte').val() }
        });
    });
})(jQuery, window.WK);
