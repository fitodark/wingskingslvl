/*
 * Comandas: bandeja, wizard de venta (cliente/ubicación, bebidas, comida, resumen) y sus modales.
 * Requiere wk-common.js.
 */
(function ($, WK) {
    'use strict';
    if (WK.comandasCargado) { return; }
    WK.comandasCargado = true;

    function dinero(valor) {
        var n = parseFloat(valor);
        return isNaN(n) ? '' : '$ ' + n.toFixed(2);
    }

    /* ---------- Paso 1: para dónde es (mesa, domicilio, para llevar) ---------- */

    // 1 = Mesa (local), 2 = Domicilio, 3 = Para llevar. Sin tipo preseleccionado.
    // Las reglas son las mismas que valida el servidor (VentasController@update):
    // mesa o nombre de cuenta; domicilio con cliente; para llevar con nombre o cliente.
    // Cada registro de cliente es una dirección: un teléfono puede tener varios.
    var $paso1 = $('#tableSelectForm.wk-paso1-form');
    var paso1 = { cliente: null, buscarEnLlevar: false, busqueda: 0, temporizador: null };

    function ponerPromocion(porcentaje) {
        $('[name=discountPercentage]').val(porcentaje);
    }

    function tipoElegido() {
        return parseInt($paso1.find('input[name=type]:checked').val(), 10) || 0;
    }

    function textoCuentas(n) {
        return n + (n === 1 ? ' cuenta abierta' : ' cuentas abiertas');
    }

    function filaCliente(c, textoBoton) {
        var $fila = $('<button type="button" class="list-group-item list-group-item-action">');
        $fila.append($('<span>')
            .append($('<strong>').text(c.name))
            .append($('<small>').text(c.address + (c.reference ? ' · ' + c.reference : ''))));
        $fila.append($('<span class="text-nowrap">').text(textoBoton || c.phone));
        $fila.data('cliente', c);
        return $fila;
    }

    function pintarPaso1() {
        var tipo = tipoElegido();
        var cliente = paso1.cliente;
        var verCliente = tipo === 2 || (tipo === 3 && (paso1.buscarEnLlevar || !!cliente));

        $('#wkSinTipo').prop('hidden', tipo !== 0);
        $('#panelMesa').prop('hidden', tipo !== 1);
        $('#panelLlevar').prop('hidden', tipo !== 3);
        $('#panelCliente').prop('hidden', !verCliente);
        // Solo se envía el nombre de cuenta del panel visible (los dos se llaman nombre_cuenta).
        $('#cuentaMesa').prop('disabled', tipo !== 1);
        $('#cuentaLlevar').prop('disabled', tipo !== 3);
        $('#abrirBuscarCliente').prop('hidden', verCliente);
        $('#tituloCliente').text(tipo === 2 ? 'Cliente' : 'Cliente registrado (opcional)');

        $('#ventaClientId').val(cliente && tipo !== 1 ? cliente.id : '');
        $('#buscador').prop('hidden', !!cliente);
        $('#fichaCliente').prop('hidden', !cliente);
        if (!cliente) { $('#otrasDirecciones').prop('hidden', true); }
        if (cliente) {
            $('#fichaNombre').text(cliente.name);
            $('#fichaTelefono').text(cliente.phone);
            $('#fichaDireccion').text(cliente.address + (cliente.colonia ? ', Col. ' + cliente.colonia : ''));
            $('#fichaReferencia').text(cliente.reference || '—');
        }

        // Mesa con cuentas abiertas: esta comanda es una cuenta separada.
        var $mesa = $paso1.find('input[name=table]:checked');
        var cuentas = $mesa.length ? ($mesa.data('cuentas') || []) : [];
        if (tipo === 1 && cuentas.length) {
            $('#avisoMesa').prop('hidden', false).text($mesa.data('nombre') + ' ya tiene ' + textoCuentas(cuentas.length)
                + ': ' + cuentas.join(', ') + '. Esta comanda será una cuenta separada; ponle nombre para distinguirla.');
        } else {
            $('#avisoMesa').prop('hidden', true).text('');
        }

        var nombreMesa = $mesa.length ? $mesa.data('nombre') : '';
        var cuenta = $.trim(tipo === 3 ? $('#cuentaLlevar').val() : $('#cuentaMesa').val());
        var listo = false, resumen = 'Sin elegir', ayuda = 'Elige Mesa, Domicilio o Para llevar';
        if (tipo === 1) {
            listo = ($mesa.length && $mesa.val() !== '') || cuenta !== '';
            resumen = [nombreMesa || null, cuenta || null].filter(Boolean).join(' · ') || 'Mesa';
            ayuda = listo ? 'Listo para capturar bebidas' : 'Elige una mesa o escribe el nombre de la cuenta';
        } else if (tipo === 2) {
            listo = !!cliente;
            resumen = 'Domicilio' + (cliente ? ' · ' + cliente.name : '');
            ayuda = cliente ? cliente.address : 'Busca o registra al cliente';
        } else if (tipo === 3) {
            listo = cuenta !== '' || !!cliente;
            resumen = 'Para llevar' + (cuenta ? ' · ' + cuenta : '') + (cliente ? ' · ' + cliente.name : '');
            ayuda = listo ? 'Listo para capturar bebidas' : 'Escribe quién recoge o elige un cliente';
        }
        $('#resumenPaso1').text(resumen);
        $('#ayudaPaso1').text(ayuda);
        $('#btnSiguiente').prop('disabled', !listo);
    }

    function cargarOtrasDirecciones() {
        var cliente = paso1.cliente;
        var $lista = $('#listaDirecciones').empty();
        $('#otrasDirecciones').prop('hidden', true);
        if (!cliente || !cliente.phone) { return; }
        $.get(WK.rutas.direccionesCliente, { phone: cliente.phone }, function (data) {
            if (!paso1.cliente || paso1.cliente.id !== cliente.id) { return; }
            var otras = $.grep(data || [], function (c) { return c.id !== cliente.id; });
            $.each(otras, function (i, c) { $lista.append(filaCliente(c, 'Enviar aquí')); });
            $('#otrasDirecciones').prop('hidden', otras.length === 0);
        });
    }

    function elegirCliente(c) {
        paso1.cliente = c;
        $('#buscarCliente').val('');
        $('#resultadosCliente').empty();
        $.get(WK.rutas.cliente, { id: c.id }, function (data) {
            ponerPromocion(data && data.length ? data[0].discountPercentage : 0);
        });
        cargarOtrasDirecciones();
        pintarPaso1();
    }

    function buscarClientes() {
        var texto = $.trim($('#buscarCliente').val());
        var $resultados = $('#resultadosCliente').empty();
        var numero = ++paso1.busqueda;
        if (texto.length < 2) {
            $('#buscarAyuda').text('Escribe al menos 2 letras o números.');
            return;
        }
        $('#buscarAyuda').text('Buscando...');
        $.get(WK.rutas.buscarCliente, { query: texto })
            .done(function (data) {
                if (numero !== paso1.busqueda) { return; } // llegó una búsqueda más nueva
                $.each(data || [], function (i, c) { $resultados.append(filaCliente(c)); });
                $('#buscarAyuda').text(data && data.length
                    ? (data.length >= 20 ? 'Se muestran los primeros 20; escribe más para afinar.' : 'Elige la dirección a la que se envía.')
                    : 'Sin resultados. Usa "Cliente nuevo" para registrarlo.');
            })
            .fail(function (xhr) {
                if (numero === paso1.busqueda) { $('#buscarAyuda').text(WK.errorDeConexion(xhr)); }
            });
    }

    if ($paso1.length) {
        paso1.cliente = $('#panelCliente').data('cliente') || null;
        if (paso1.cliente) { cargarOtrasDirecciones(); }

        $paso1.on('change', 'input[name=type], input[name=table]', pintarPaso1);
        $paso1.on('input', '#cuentaMesa, #cuentaLlevar', pintarPaso1);
        $('#abrirBuscarCliente').on('click', function () {
            paso1.buscarEnLlevar = true;
            pintarPaso1();
            $('#buscarCliente').trigger('focus');
        });
        $('#buscarCliente').on('input', function () {
            clearTimeout(paso1.temporizador);
            paso1.temporizador = setTimeout(buscarClientes, 250);
        });
        $('#resultadosCliente, #listaDirecciones').on('click', '.list-group-item', function () {
            elegirCliente($(this).data('cliente'));
        });
        $('#cambiarCliente').on('click', function () {
            paso1.cliente = null;
            ponerPromocion(0);
            pintarPaso1();
            $('#buscarCliente').trigger('focus');
        });
        // Enter en un campo de texto no debe mandar el formulario a medias.
        $paso1.on('keydown', 'input[type=text], input[type=search]', function (e) {
            if (e.which === 13) { e.preventDefault(); }
        });
        pintarPaso1();
    }

    // Los dos modales de cliente usan el mismo formulario parcial, así que se llenan por "name" dentro de cada modal.
    // Llevan el tipo elegido para que al recargar el paso 1 no se pierda.
    $('#addClientModal').on('show.bs.modal', function (event) {
        var $modal = $(this);
        var $boton = $(event.relatedTarget);
        $modal.find('form').removeClass('was-validated');
        $modal.find('[name=ventaid]').val($boton.data('ventaid'));
        $modal.find('[name=type]').val(tipoElegido() || '');
        $modal.find('[name=clientId], [name=name], [name=phone], [name=address], [name=colonia], [name=reference]').val('');
        if ($boton.data('mismo-telefono') && paso1.cliente) {
            // Nueva dirección para el mismo cliente/teléfono.
            $modal.find('[name=name]').val(paso1.cliente.name);
            $modal.find('[name=phone]').val(paso1.cliente.phone);
        }
    });

    $('#editClientModal').on('show.bs.modal', function (event) {
        var cliente = paso1.cliente;
        if (!cliente) {
            // Sin cliente seleccionado no hay nada que editar.
            event.preventDefault();
            return;
        }
        var $modal = $(this);
        $modal.find('form').removeClass('was-validated');
        $modal.find('[name=ventaid]').val($(event.relatedTarget).data('ventaid'));
        $modal.find('[name=type]').val(tipoElegido() || '');
        $modal.find('[name=clientId]').val(cliente.id);
        $modal.find('[name=name]').val(cliente.name);
        $modal.find('[name=phone]').val(cliente.phone);
        $modal.find('[name=address]').val(cliente.address);
        $modal.find('[name=colonia]').val(cliente.colonia || '');
        $modal.find('[name=reference]').val(cliente.reference);
    });

    // Historial de compras del cliente. En el paso 1 el cliente sale de #ventaClientId (cambia al elegir otro);
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
        cargarHistorial($(this), $('#ventaClientId').val());
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
