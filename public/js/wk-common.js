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

    /*
     * Selector de colonia (puntoventa/clientes/selectorColonia.blade.php).
     * Reemplaza al <datalist>, que en varias tablets no despliega nada: al tocar el campo sale la
     * lista completa y al escribir se filtra (sin importar acentos ni mayúsculas); un toque elige.
     * Solo acepta colonias del catálogo: otro texto deja el campo inválido ("Elige una colonia de la lista.").
     * Al elegir dispara "change" en el input.
     */
    WK.normalizar = function (texto) {
        return String(texto || '').normalize('NFD').replace(/[̀-ͯ]/g, '')
            .toLowerCase().replace(/\s+/g, ' ').trim();
    };

    var colonia = {
        catalogo: function ($c) {
            var cat = $c.data('wkCatalogo');
            if (!cat) {
                cat = $.map($c.data('colonias') || [], function (o) {
                    return { nombre: o.colonia, clave: WK.normalizar(o.colonia), soloExpress: !!o.solo_express };
                });
                $c.data('wkCatalogo', cat);
            }
            return cat;
        },
        buscarExacta: function ($c, texto) {
            var clave = WK.normalizar(texto), hallada = null;
            $.each(colonia.catalogo($c), function (i, o) {
                if (o.clave === clave) { hallada = o; return false; }
            });
            return hallada;
        },
        // Primero las que empiezan con lo escrito, luego las que lo contienen.
        filtrar: function ($c, texto) {
            var clave = WK.normalizar(texto), cat = colonia.catalogo($c);
            if (clave === '') { return cat; }
            var inicio = [], contiene = [];
            $.each(cat, function (i, o) {
                var pos = o.clave.indexOf(clave);
                if (pos === 0) { inicio.push(o); } else if (pos > 0) { contiene.push(o); }
            });
            return inicio.concat(contiene);
        },
        abrir: function ($input) {
            var $c = $input.closest('.wk-colonia'), $lista = $c.find('.wk-colonia-lista').empty();
            var opciones = colonia.filtrar($c, $input.val());
            if (!opciones.length) {
                $lista.append($('<div class="list-group-item text-muted">')
                    .text('Sin coincidencias. Prueba con otra parte del nombre.'));
            }
            $.each(opciones, function (i, o) {
                var $op = $('<button type="button" class="list-group-item list-group-item-action wk-colonia-op" role="option" tabindex="-1">')
                    .data('valor', o.nombre)
                    .append($('<span>').text(o.nombre));
                if (o.soloExpress) {
                    $op.append($('<span class="badge badge-warning">').text('Solo express'));
                }
                if (i === 0) { $op.addClass('active'); }
                $lista.append($op);
            });
            $lista.prop('hidden', false).scrollTop(0);
            $input.attr('aria-expanded', 'true').removeClass('is-invalid');
        },
        cerrar: function ($c) {
            $c.find('.wk-colonia-lista').prop('hidden', true).empty();
            $c.find('.wk-colonia-input').attr('aria-expanded', 'false');
        },
        estaAbierto: function ($input) {
            return !$input.closest('.wk-colonia').find('.wk-colonia-lista').prop('hidden');
        },
        // Ajusta el texto a como está en el catálogo y marca el campo inválido si no existe.
        validar: function ($input, marcar) {
            var texto = $.trim($input.val());
            var hallada = texto === '' ? null : colonia.buscarExacta($input.closest('.wk-colonia'), texto);
            if (hallada) { $input.val(hallada.nombre); }
            var valido = texto === '' || !!hallada;
            $input[0].setCustomValidity(valido ? '' : 'Elige una colonia de la lista.');
            if (marcar) { $input.toggleClass('is-invalid', !valido); }
            return valido;
        },
        elegir: function ($input, valor) {
            $input.val(valor);
            colonia.cerrar($input.closest('.wk-colonia'));
            colonia.validar($input, true);
            $input.trigger('change');
        }
    };
    WK.selectorColonia = colonia;

    $(document)
        .on('focus click', '.wk-colonia-input', function () {
            var $input = $(this);
            if (!colonia.estaAbierto($input)) { colonia.abrir($input); }
        })
        .on('input', '.wk-colonia-input', function () {
            this.setCustomValidity('');
            colonia.abrir($(this));
        })
        .on('keydown', '.wk-colonia-input', function (e) {
            var $input = $(this);
            if (!colonia.estaAbierto($input)) { return; }
            var $lista = $input.closest('.wk-colonia').find('.wk-colonia-lista');
            var $ops = $lista.find('.wk-colonia-op'), $activa = $ops.filter('.active');
            if (e.which === 13) { // Enter elige la marcada
                e.preventDefault();
                e.stopImmediatePropagation();
                if ($activa.length) { colonia.elegir($input, $activa.data('valor')); }
            } else if (e.which === 27) { // Esc
                colonia.cerrar($input.closest('.wk-colonia'));
            } else if (e.which === 40 || e.which === 38) { // flechas
                e.preventDefault();
                var i = $ops.index($activa) + (e.which === 40 ? 1 : -1);
                i = Math.max(0, Math.min($ops.length - 1, i));
                $ops.removeClass('active').eq(i).addClass('active');
                var op = $ops.get(i);
                if (op && op.scrollIntoView) { op.scrollIntoView({ block: 'nearest' }); }
            }
        })
        // Que tocar la lista no le quite el foco al campo (si no, se cerraría antes del click).
        .on('mousedown pointerdown', '.wk-colonia-lista', function (e) { e.preventDefault(); })
        .on('click', '.wk-colonia-op', function () {
            colonia.elegir($(this).closest('.wk-colonia').find('.wk-colonia-input'), $(this).data('valor'));
        })
        .on('blur', '.wk-colonia-input', function () {
            var $input = $(this);
            colonia.cerrar($input.closest('.wk-colonia'));
            colonia.validar($input, true);
        })
        // Un modal que se vuelve a abrir no arrastra la lista ni el error de la vez anterior.
        .on('show.bs.modal', '.modal', function () {
            $(this).find('.wk-colonia').each(function () {
                var $c = $(this);
                colonia.cerrar($c);
                $c.find('.wk-colonia-input').removeClass('is-invalid').each(function () { this.setCustomValidity(''); });
            });
        });

    // Antes de cualquier validación de formulario: revisa las colonias (fase de captura).
    document.addEventListener('submit', function (event) {
        $(event.target).find('.wk-colonia-input').each(function () { colonia.validar($(this), false); });
    }, true);
    // Valor precargado (edición de cliente): si no es del catálogo, el formulario no se manda sin corregirlo.
    $(function () {
        $('.wk-colonia-input').each(function () { colonia.validar($(this), false); });
    });

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
