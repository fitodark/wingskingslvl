/*
 * Comandas: bandeja, wizard de venta (cliente/ubicación, bebidas, comida, resumen) y sus modales.
 * Requiere wk-common.js. En la pantalla de cliente también bootstrap3-typeahead.
 */
(function ($, WK) {
    'use strict';
    if (WK.comandasCargado) { return; }
    WK.comandasCargado = true;

    function dinero(valor) {
        var n = parseFloat(valor);
        return isNaN(n) ? '' : '$ ' + n.toFixed(2);
    }

    /* ---------- Paso 1: cliente y ubicación ---------- */

    // 1 = Local, 2 = Domicilio, 3 = Para llevar.
    // La mesa solo aplica en Local; el nombre de la cuenta en Local y Para llevar.
    function setTipoVenta(tipo) {
        $('#type').val(tipo);
        if (tipo == 1) {
            $('#collapseOne').collapse('show');
        } else {
            $('#collapseOne').collapse('hide');
            $('#table').val('');
        }
        if (tipo == 2) {
            $('#cuentaGroup').hide();
            $('#nombre_cuenta').val('');
        } else {
            $('#cuentaGroup').show();
        }
    }
    $('#customRadio1').on('change', function () { setTipoVenta(1); });
    $('#customRadio2').on('change', function () { setTipoVenta(2); });
    $('#customRadio3').on('change', function () { setTipoVenta(3); });

    function ponerPromocion(porcentaje) {
        $('#promotion').val(porcentaje + ' %');
        $('[name=discountPercentage]').val(porcentaje);
    }

    if ($.fn.typeahead) {
        $('input.typeahead').typeahead({
            source: function (query, process) {
                return $.get(WK.rutas.buscarCliente, { query: query }, function (data) {
                    return process(data);
                });
            },
            displayText: function (item) {
                return '[' + item.phone + '] ' + item.name + ' - ' + item.address;
            },
            afterSelect: function (item) {
                $('#client').val(item);
                $('#clientId').val(item.id);
                $('#clientName').val(item.name);
                $('#clientPhone').val(item.phone);
                $('#clientAddress').val(item.address);
                $('#clientReference').val(item.reference);

                $.get(WK.rutas.cliente, { id: item.id }, function (data) {
                    ponerPromocion(data && data.length ? data[0].discountPercentage : 0);
                });
            }
        });
    }

    $('#cleanClient').on('click', function () {
        $('#clientId, #clientName, #clientPhone, #clientAddress, #clientReference, #findClient').val('');
        ponerPromocion(0);
        $('#customRadio1').prop('checked', true);
        setTipoVenta(1);
    });

    // Los dos modales de cliente usan el mismo formulario parcial, así que se llenan por "name" dentro de cada modal.
    $('#addClientModal').on('show.bs.modal', function (event) {
        var $modal = $(this);
        $modal.find('form').removeClass('was-validated');
        $modal.find('[name=ventaid]').val($(event.relatedTarget).data('ventaid'));
        $modal.find('[name=clientId], [name=name], [name=phone], [name=address], [name=reference]').val('');
    });

    $('#editClientModal').on('show.bs.modal', function (event) {
        var clientId = $('#clientId').val();
        if (!clientId) {
            // Sin cliente seleccionado no hay nada que editar.
            event.preventDefault();
            return;
        }
        var $modal = $(this);
        $modal.find('form').removeClass('was-validated');
        $modal.find('[name=ventaid]').val($(event.relatedTarget).data('ventaid'));
        $modal.find('[name=clientId]').val(clientId);
        $modal.find('[name=name]').val($('#clientName').val());
        $modal.find('[name=phone]').val($('#clientPhone').val());
        $modal.find('[name=address]').val($('#clientAddress').val());
        $modal.find('[name=reference]').val($('#clientReference').val());
    });

    // Historial de compras del cliente. En el paso 1 el cliente sale de #clientId (puede cambiar con el buscador);
    // en el resumen viene en el botón.
    function cargarHistorial($modal, clientId) {
        var $cuerpo = $modal.find('tbody').empty();
        var fila = function (texto) {
            $cuerpo.append($('<tr>').append($('<td colspan="3" class="text-center text-muted">').text(texto)));
        };
        if (!clientId) {
            fila('No hay cliente seleccionado.');
            return;
        }
        fila('Cargando...');
        $.get(WK.rutas.historialCliente, { id: clientId })
            .done(function (data) {
                $cuerpo.empty();
                if (!data || !data.length) {
                    fila('El cliente no tiene ventas registradas.');
                    return;
                }
                $.each(data, function (i, venta) {
                    $cuerpo.append($('<tr>')
                        .append($('<td>').text(venta.ventaId))
                        .append($('<td>').text(dinero(venta.montoTotal)))
                        .append($('<td>').text(venta.date)));
                });
            })
            .fail(function (xhr) {
                $cuerpo.empty();
                fila(WK.errorDeConexion(xhr));
            });
    }
    $('#clientSalesDetailsModal').on('show.bs.modal', function () {
        cargarHistorial($(this), $('#clientId').val());
    });
    $('#clientSalesDetailsModalResume').on('show.bs.modal', function (event) {
        cargarHistorial($(this), $(event.relatedTarget).data('clientid'));
    });

    /* ---------- Pasos 2 y 3: agregar productos ---------- */

    // Bebidas y comida sin sabores.
    $('#dialogDetails').on('show.bs.modal', function (event) {
        var $boton = $(event.relatedTarget);
        var $modal = $(this);
        $modal.find('#productName').text($boton.data('productname'));
        $modal.find('[name=idProduct]').val($boton.data('productid'));
        $modal.find('[name=ventaId]').val($boton.data('ventaid'));
        $modal.find('[name=tab]').val($boton.data('tab'));
        $modal.find('[name=price]').val($boton.data('price'));
        $modal.find('[name=cantidad]').val(1);
        $modal.find('[name=description]').val('');
    }).on('shown.bs.modal', function () {
        $(this).find('[name=cantidad]').trigger('focus').trigger('select');
    });

    // Botones +/- junto a la cantidad (más fácil que el teclado en tablet).
    $(document).on('click', '[data-cantidad-paso]', function () {
        var $input = $($(this).data('target-input'));
        var valor = (parseInt($input.val(), 10) || 0) + parseInt($(this).data('cantidad-paso'), 10);
        $input.val(Math.max(1, valor));
    });

    /*
     * Sabores de una orden de alitas (modal dialogFoodDetails).
     * Guarda el mismo formato JSON de siempre (lo leen la impresión, las vistas y el agente):
     * [[encabezado], [{key: índice cantidad, value: "10 Piezas"}, {key: índice sabor, value: "Habanero"}], ...]
     * El servidor vuelve a validar todo (App\Support\SaboresOrden).
     */
    var $modalSabores = $('#dialogFoodDetails');
    var saboresOrden = {
        renglones: [],
        piezasProducto: null,
        piezasDe: function (cantidad) {
            if (cantidad === 'Todas') { return this.piezasProducto || 0; }
            if (cantidad === 'Mitad') { return (this.piezasProducto || 0) / 2; }
            var n = parseInt(cantidad, 10);
            return isNaN(n) ? 0 : n;
        },
        suma: function () {
            var self = this;
            return this.renglones.reduce(function (total, r) { return total + self.piezasDe(r.cantidad.value); }, 0);
        },
        cuadra: function () {
            if (this.renglones.length === 0) { return false; }
            if (!this.piezasProducto) { return true; }
            return Math.abs(this.suma() - this.piezasProducto) < 0.001;
        },
        pintar: function () {
            var $body = $modalSabores.find('#tableBody').empty();
            $.each(this.renglones, function (i, r) {
                // .text() evita que un sabor con HTML se ejecute en la página.
                $body.append($('<tr>')
                    .append($('<td>').text(r.cantidad.value))
                    .append($('<td>').text(r.sabor.value))
                    .append($('<td class="text-right">').append(
                        $('<button type="button" class="ibtnDel btn btn-outline-danger btn-sm">Quitar</button>').attr('data-index', i)
                    )));
            });

            var $contador = $modalSabores.find('#piezasContador');
            if (this.piezasProducto) {
                var suma = this.suma();
                $contador
                    .text('Llevas ' + suma + ' de ' + this.piezasProducto + ' piezas')
                    .toggleClass('text-success', this.cuadra())
                    .toggleClass('text-danger', !this.cuadra() && suma > this.piezasProducto);
            } else {
                $contador.text('').removeClass('text-success text-danger');
            }

            var datos = [[{ 'key': '', 'value': 'Cantidad' }, { 'key': '', 'value': 'Sabor' }]];
            $.each(this.renglones, function (i, r) { datos.push([r.cantidad, r.sabor]); });
            $modalSabores.find('[name=description]').val(this.renglones.length ? JSON.stringify(datos) : '');
            $modalSabores.find('#addFoodRow').prop('disabled', !this.cuadra());
        }
    };

    $modalSabores.on('show.bs.modal', function (event) {
        var $boton = $(event.relatedTarget);
        $modalSabores.find('#foodName').text($boton.data('foodname'));
        $modalSabores.find('[name=idProduct]').val($boton.data('productid'));
        $modalSabores.find('[name=ventaId]').val($boton.data('ventaid'));
        $modalSabores.find('[name=tab]').val($boton.data('tab'));
        $modalSabores.find('[name=price]').val($boton.data('price'));
        $modalSabores.find('#pieces, #flavors').val('');
        // Cada producto empieza sin sabores; piezas = las que trae la orden (vacío = no se valida).
        saboresOrden.renglones = [];
        saboresOrden.piezasProducto = parseInt($boton.data('piezas'), 10) || null;
        saboresOrden.pintar();
    });

    $modalSabores.on('click', '#addrow', function () {
        var $cantidad = $modalSabores.find("select[name='piecesnumber'] option:selected");
        var $sabor = $modalSabores.find("select[name='flavors'] option:selected");
        if (!$cantidad.length || !$sabor.length) {
            $modalSabores.find('#piezasContador').text('Selecciona la cantidad y el sabor.')
                .removeClass('text-success').addClass('text-danger');
            return;
        }
        saboresOrden.renglones.push({
            cantidad: { 'key': String($cantidad.val()), 'value': $cantidad.text() },
            sabor: { 'key': String($sabor.val()), 'value': $sabor.text() }
        });
        // Se deja listo para el siguiente sabor.
        $modalSabores.find('#flavors').val('');
        saboresOrden.pintar();
    });

    $modalSabores.on('click', '.ibtnDel', function () {
        saboresOrden.renglones.splice(parseInt($(this).attr('data-index'), 10), 1);
        saboresOrden.pintar();
    });

    /* ---------- Eliminar producto ya comandado (requiere PIN) ---------- */

    var $modalEliminar = $('#eliminarProductoModal');
    $modalEliminar.on('show.bs.modal', function (event) {
        var $boton = $(event.relatedTarget);
        $modalEliminar.find('[name=productoid]').val($boton.data('productoid'));
        $modalEliminar.find('[name=modaltab]').val($boton.data('tab'));
        $modalEliminar.find('[name=pin]').val('');
        WK.ocultarErrores($modalEliminar.find('.alert-danger'));
    }).on('shown.bs.modal', function () {
        $modalEliminar.find('[name=pin]').trigger('focus');
    });

    $modalEliminar.find('form').on('submit', function (event) {
        event.preventDefault();
        WK.enviar({
            url: WK.rutas.eliminarProducto,
            boton: $modalEliminar.find('#eliminarProducto'),
            alerta: $modalEliminar.find('.alert-danger'),
            datos: {
                productoid: $modalEliminar.find('[name=productoid]').val(),
                pin: $modalEliminar.find('[name=pin]').val(),
                tab: $modalEliminar.find('[name=modaltab]').val()
            },
            alTerminar: function () { $modalEliminar.find('[name=pin]').val(''); }
        });
    });

    /* ---------- Bandeja: cobrar / finalizar venta ---------- */

    var $modalCobrar = $('#finalizarVentaModal');

    function totalACobrar() {
        var conDescuento = $modalCobrar.find('#applydiscount').val() == 1;
        return parseFloat($modalCobrar.find(conDescuento ? '#montototaldescuento' : '#total').val()) || 0;
    }

    function pintarCambio() {
        var recibido = parseFloat($modalCobrar.find('#quantity').val());
        var $cambio = $modalCobrar.find('#cambio');
        if (isNaN(recibido)) {
            $cambio.text('').removeClass('text-danger text-success');
            return;
        }
        var diferencia = recibido - totalACobrar();
        if (diferencia < 0) {
            $cambio.text('Faltan ' + dinero(-diferencia)).addClass('text-danger').removeClass('text-success');
        } else {
            $cambio.text('Cambio: ' + dinero(diferencia)).addClass('text-success').removeClass('text-danger');
        }
    }

    $modalCobrar.on('show.bs.modal', function (event) {
        var $boton = $(event.relatedTarget);
        var aplicaDescuento = $boton.data('applydiscount');
        $modalCobrar.find('#location').val($boton.data('location'));
        $modalCobrar.find('#total').val($boton.data('total'));
        $modalCobrar.find('#folio').val($boton.data('ventaid'));
        $modalCobrar.find('#quantity').val('');
        $modalCobrar.find('#paymenttype').val('1');
        $modalCobrar.find('#montototaldescuento').val($boton.data('montototaldescuento'));
        $modalCobrar.find('#discountpercentage').val($boton.data('discountpercentage'));
        $modalCobrar.find('#applydiscount').val(aplicaDescuento);
        $modalCobrar.find('#rowdiscount').toggle(aplicaDescuento == 1);
        WK.ocultarErrores($modalCobrar.find('.alert-danger'));
        pintarCambio();
    }).on('shown.bs.modal', function () {
        $modalCobrar.find('#quantity').trigger('focus');
    });

    $modalCobrar.on('input', '#quantity', pintarCambio);

    // "Pago exacto": pone el total como cantidad recibida (útil en transferencia).
    $modalCobrar.on('click', '#pagoExacto', function () {
        $modalCobrar.find('#quantity').val(totalACobrar());
        pintarCambio();
    });

    $modalCobrar.find('form').on('submit', function (event) {
        event.preventDefault();
        WK.enviar({
            url: WK.rutas.cerrarVenta,
            boton: $modalCobrar.find('#finalizarVenta'),
            alerta: $modalCobrar.find('.alert-danger'),
            datos: {
                quantity: $modalCobrar.find('#quantity').val(),
                ventaid: $modalCobrar.find('#folio').val(),
                discountpercentage: $modalCobrar.find('#discountpercentage').val(),
                paymenttype: $modalCobrar.find('#paymenttype').val()
            }
        });
    });
})(jQuery, window.WK);
