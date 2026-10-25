/*
 * Código común a todas las pantallas. Requiere jQuery y Bootstrap (public/vendor).
 * Las rutas del servidor vienen en window.WK.rutas (layouts/app.blade.php).
 */
(function ($, WK) {
    'use strict';

    // Todas las peticiones AJAX llevan el token CSRF.
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': WK.csrf } });

    // Muestra una lista de errores en un .alert (los controladores responden {errors: [...]}).
    WK.mostrarErrores = function ($alerta, errores) {
        var $lista = $('<ul class="mb-0">');
        $.each(errores, function (i, texto) { $lista.append($('<li>').text(texto)); });
        $alerta.empty().append($lista).show();
    };

    WK.ocultarErrores = function ($alerta) {
        $alerta.empty().hide();
    };

    // Mensaje cuando la petición ni siquiera llegó a responder {errors}.
    WK.errorDeConexion = function (xhr) {
        if (xhr.status === 419) { return 'La sesión expiró. Recarga la página e inténtalo de nuevo.'; }
        if (xhr.status === 401) { return 'Tu sesión terminó. Vuelve a ingresar.'; }
        if (xhr.status === 403) { return 'No tienes permiso para esta acción.'; }
        if (xhr.status === 0) { return 'Sin conexión con el servidor. Revisa la red e inténtalo de nuevo.'; }
        return 'Ocurrió un error en el servidor (' + xhr.status + '). Inténtalo de nuevo.';
    };

    /*
     * POST por AJAX para los modales (cobrar, eliminar con PIN, cerrar corte).
     * Bloquea el botón mientras responde para que un doble toque no mande dos veces.
     * Si todo sale bien el servidor responde {url} y se navega ahí.
     */
    WK.enviar = function (opciones) {
        var $boton = opciones.boton;
        var $alerta = opciones.alerta;
        if ($boton.prop('disabled')) { return; }
        $boton.prop('disabled', true);
        WK.ocultarErrores($alerta);

        $.ajax({ url: opciones.url, method: 'POST', data: opciones.datos })
            .done(function (data) {
                if (data && data.errors) {
                    WK.mostrarErrores($alerta, data.errors);
                    $boton.prop('disabled', false);
                    return;
                }
                if (opciones.alTerminar) { opciones.alTerminar(data); }
                if (data && data.url) {
                    window.location = data.url;
                } else {
                    $boton.prop('disabled', false);
                }
            })
            .fail(function (xhr) {
                WK.mostrarErrores($alerta, [WK.errorDeConexion(xhr)]);
                $boton.prop('disabled', false);
            });
    };

    // Validación de Bootstrap para formularios con class="needs-validation" (alta/edición de cliente).
    // Va antes del bloqueo de doble envío para que un formulario inválido no quede bloqueado.
    $(document).on('submit', 'form.needs-validation', function (event) {
        if (!this.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        $(this).addClass('was-validated');
    });

    // Evita que un doble toque envíe dos veces el mismo formulario (p. ej. agregar dos veces un producto).
    // Los formularios que se mandan por AJAX ya cancelan el envío normal y aquí no se tocan.
    $(document).on('submit', 'form', function (event) {
        if (event.isDefaultPrevented()) { return; }
        var $form = $(this);
        if ($form.data('wkEnviando')) {
            event.preventDefault();
            return;
        }
        $form.data('wkEnviando', true);
        var $botones = $form.find('[type=submit]');
        if (this.id) { $botones = $botones.add('[type=submit][form="' + this.id + '"]'); }
        // Solo se marca visualmente (no "disabled") para no perder el valor del botón en el envío.
        $botones.addClass('wk-enviando');
        // Si la navegación no ocurre (por ejemplo, se canceló la descarga), se libera después de un rato.
        setTimeout(function () {
            $form.data('wkEnviando', false);
            $botones.removeClass('wk-enviando');
        }, 8000);
    });

    // Al volver con el botón "atrás" del navegador la página puede venir de caché con los botones marcados.
    $(window).on('pageshow', function () {
        $('form').data('wkEnviando', false);
        $('.wk-enviando').removeClass('wk-enviando');
    });

    // Campos de fecha (solo si la vista cargó el datepicker).
    $(function () {
        if (!$.fn.datepicker) { return; }
        $('.datepicker').datepicker({ format: 'dd/mm/yyyy', language: 'es', autoclose: true });
        $('.datepickercorteformat').datepicker({ format: 'yyyy-mm-dd', language: 'es', autoclose: true });
    });
})(jQuery, window.WK);
