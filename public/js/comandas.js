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
    // ultimaBusqueda: texto con que se encontró al cliente; "Cambiar" vuelve a esa lista.
    var paso1 = { cliente: null, buscarEnLlevar: false, busqueda: 0, temporizador: null, ultimaBusqueda: '' };
    // Envío (solo Domicilio). tarifa = respuesta de WK.rutas.tarifaEnvio; elegido = 'gratuito' | 'express';
    // acepta = null (sin respuesta) | true | false, solo para colonias que solo tienen express.
    // inicial = envío que ya traía la venta (agente o comanda reabierta).
    paso1.envio = { tarifa: null, elegido: null, acepta: null, cargando: false, error: null, consulta: 0, inicial: null };

    // La venta del agente ya trae el envío aceptado por WhatsApp: se respeta mientras no cambie el cliente.
    function envioAcordado() {
        var ini = paso1.envio.inicial;
        return !!(ini && ini.agente && paso1.cliente && paso1.cliente.id === ini.client_id);
    }

    function reiniciarEnvio() {
        var e = paso1.envio;
        e.consulta++; // una respuesta pendiente ya no aplica
        e.tarifa = null;
        e.elegido = null;
        e.acepta = null;
        e.cargando = false;
        e.error = null;
        $('#envioColoniaInput').val('');
        $('#envioColoniaError').text('');
        $('#montoAcordadoInput').val('');
    }

    // Valores con que arranca el panel al recibir la tarifa.
    function preseleccionarEnvio(tarifa) {
        var e = paso1.envio, ini = e.inicial;
        var reabierta = ini && paso1.cliente && paso1.cliente.id === ini.client_id;
        e.tarifa = tarifa;
        e.elegido = tarifa.envio_gratuito && !(reabierta && ini.tipo_envio === 2) ? 'gratuito' : 'express';
        // Una comanda reabierta que ya era express en colonia solo express: ya se había aceptado.
        e.acepta = (!tarifa.envio_gratuito && reabierta && ini.tipo_envio === 2) ? true : null;
        // Comanda reabierta con express acordado en colonia gratuita: conserva el monto capturado.
        $('#montoAcordadoInput').val(reabierta && ini.tipo_envio === 2 && ini.monto !== null ? ini.monto : '');
    }

    function consultarEnvio() {
        var e = paso1.envio, cliente = paso1.cliente;
        if (tipoElegido() !== 2 || !cliente || envioAcordado()) { return; }
        var numero = ++e.consulta;
        e.cargando = true;
        e.error = null;
        $.get(WK.rutas.tarifaEnvio + '/' + cliente.id)
            .done(function (data) {
                if (numero !== e.consulta) { return; }
                e.cargando = false;
                preseleccionarEnvio(data);
            })
            .fail(function (xhr) {
                if (numero !== e.consulta) { return; }
                e.cargando = false;
                e.error = WK.errorDeConexion(xhr);
            })
            .always(function () {
                if (numero === e.consulta) { pintarPaso1(); }
            });
    }

    function esExpress() {
        var t = paso1.envio.tarifa;
        if (!t || !t.encontrada) { return false; }
        return t.envio_gratuito ? paso1.envio.elegido === 'express' : paso1.envio.acepta === true;
    }

    // Express pedido por el cliente en colonia gratuita sin tarifa: el monto se acuerda por teléfono y se captura.
    function pideMontoAcordado() {
        var t = paso1.envio.tarifa;
        return esExpress() && t.envio_gratuito && (t.monto_express === null || t.monto_express === undefined);
    }

    // Monto capturado si es válido (mayor a 0 y hasta 9,999.99); null si no.
    function montoAcordado() {
        var texto = $.trim($('#montoAcordadoInput').val());
        var n = parseFloat(texto);
        return texto !== '' && !isNaN(n) && n > 0 && n <= 9999.99 ? n : null;
    }

    function envioListo() {
        var e = paso1.envio, t = e.tarifa;
        if (envioAcordado()) { return true; }
        if (!t || !t.encontrada) { return false; }
        if (!t.envio_gratuito && e.acepta !== true) { return false; }
        if (esExpress() && !t.express_disponible) { return false; }
        return !(pideMontoAcordado() && montoAcordado() === null);
    }

    // Monto express para mostrar; sin monto (ventas viejas) se acordó por teléfono.
    function montoExpress(monto) {
        return dinero(monto) || 'monto por acordar';
    }

    // Texto para la barra inferior: "· Gratuito" o "· Express $ 45.00".
    function resumenEnvio() {
        var ini = paso1.envio.inicial, t = paso1.envio.tarifa;
        if (envioAcordado()) {
            return ini.tipo_envio === 2 ? ' · Express ' + montoExpress(ini.monto) : ' · Gratuito';
        }
        if (!envioListo()) { return ''; }
        if (!esExpress()) { return ' · Gratuito'; }
        return ' · Express ' + montoExpress(pideMontoAcordado() ? montoAcordado() : t.monto_express);
    }

    function ayudaEnvio() {
        var e = paso1.envio, t = e.tarifa;
        if (e.cargando) { return 'Consultando la tarifa de envío...'; }
        if (e.error) { return e.error; }
        if (!t) { return ''; }
        if (!t.encontrada) { return 'Elige la colonia del cliente'; }
        if (e.acepta === false) { return 'Cambia a Para llevar o cancela la comanda'; }
        if (pideMontoAcordado()) {
            return $.trim($('#montoAcordadoInput').val()) === ''
                ? 'Escribe el monto express acordado con el cliente'
                : 'El monto debe ser mayor a 0 y hasta $ 9,999.99';
        }
        return 'Confirma con el cliente el envío express';
    }

    function mensajeEnvio(clase, partes) {
        var $m = $('#envioMensaje').attr('class', 'alert mb-2 ' + clase).empty();
        $.each(partes, function (i, p) {
            $m.append(typeof p === 'string' ? document.createTextNode(p) : p);
        });
    }

    function pintarEnvio(tipo) {
        var e = paso1.envio, t = e.tarifa, cliente = paso1.cliente;
        var visible = tipo === 2 && !!cliente;
        var opciones = false, respuesta = false, declinado = false, colonia = false;
        $('#panelEnvio').prop('hidden', !visible);

        if (visible && envioAcordado()) {
            mensajeEnvio('alert-info', ['Envío acordado por WhatsApp: ', $('<strong>').text(e.inicial.etiqueta || '')]);
        } else if (visible && e.cargando) {
            mensajeEnvio('alert-secondary', ['Consultando la tarifa de envío...']);
        } else if (visible && e.error) {
            mensajeEnvio('alert-danger', [e.error]);
        } else if (visible && t && !t.encontrada) {
            mensajeEnvio('alert-warning', ['Esta dirección no tiene una colonia del catálogo.'
                + (t.colonia ? ' Tiene capturado: "' + t.colonia + '".' : '')]);
            colonia = true;
        } else if (visible && t && t.envio_gratuito) {
            mensajeEnvio('alert-success', ['Envío gratuito a ' + t.colonia + '.'
                + (t.express_disponible ? ' Si el cliente lo pide, puede ser express.' : '')]);
            opciones = t.express_disponible;
            $('#envioExpressTexto').text('Envío express · ' + (t.monto_express !== null && t.monto_express !== undefined
                ? dinero(t.monto_express) : 'monto acordado con el cliente por teléfono'));
            $('#envioGratuito').prop('checked', e.elegido !== 'express');
            $('#envioExpress').prop('checked', e.elegido === 'express');
        } else if (visible && t && e.acepta === false) {
            mensajeEnvio('alert-warning', ['El cliente no aceptó el envío express.']);
            declinado = true;
        } else if (visible && t) {
            mensajeEnvio('alert-warning', ['Por la distancia, ' + t.colonia + ' solo tiene envío express: ',
                $('<strong>').text(dinero(t.monto_express) + ' adicional que el cliente le paga directo al repartidor'), '.',
                e.acepta === true ? ' El cliente aceptó.' : '']);
            respuesta = true;
        }

        $('#envioOpciones').prop('hidden', !opciones).find('input').prop('disabled', !opciones);
        // El monto solo se envía cuando aplica; si no, el servidor lo toma del catálogo.
        var monto = opciones && pideMontoAcordado();
        $('#envioMontoAcordado').prop('hidden', !monto);
        $('#montoAcordadoInput').prop('disabled', !monto);
        $('#envioRespuesta').prop('hidden', !respuesta);
        $('#aceptaExpress').toggleClass('btn-success', e.acepta === true).toggleClass('btn-outline-success', e.acepta !== true)
            .attr('aria-pressed', e.acepta === true ? 'true' : 'false');
        $('#envioDeclinado').prop('hidden', !declinado);
        $('#envioColonia').prop('hidden', !colonia);
        if (colonia && !$('#envioColoniaInput').val()) { $('#envioColoniaInput').val(t.colonia || ''); }
        $('#aceptaExpressInput').val(visible && e.acepta === true ? '1' : '');
    }

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
        $('#telefonoRecoge').prop('disabled', tipo !== 3);
        $('#abrirBuscarCliente').prop('hidden', verCliente);
        $('#tituloCliente').text(tipo === 2 ? 'Cliente' : 'Cliente registrado (opcional)');

        $('#ventaClientId').val(cliente && tipo !== 1 ? cliente.id : '');
        $('#buscador').prop('hidden', !!cliente);
        $('#fichaCliente').prop('hidden', !cliente);
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
            listo = !!cliente && envioListo();
            resumen = 'Domicilio' + (cliente ? ' · ' + cliente.name + resumenEnvio() : '');
            ayuda = !cliente ? 'Busca o registra al cliente' : (listo ? cliente.address : ayudaEnvio());
        } else if (tipo === 3) {
            // Sin cliente registrado se piden nombre y teléfono (10 dígitos) de quien recoge.
            var telefono = $('#telefonoRecoge').val().replace(/\D/g, '');
            var telefonoOk = telefono === '' ? !!cliente : telefono.length === 10;
            listo = (cuenta !== '' || !!cliente) && telefonoOk;
            resumen = 'Para llevar' + (cuenta ? ' · ' + cuenta : '') + (telefono ? ' · ' + telefono : '')
                + (cliente ? ' · ' + cliente.name : '');
            ayuda = listo ? 'Listo para capturar bebidas'
                : (cuenta === '' && !cliente ? 'Escribe quién recoge o elige un cliente'
                    : (telefono === '' ? 'Escribe el teléfono de quien recoge' : 'El teléfono debe tener 10 dígitos'));
        }
        pintarEnvio(tipo);
        $('#resumenPaso1').text(resumen);
        $('#ayudaPaso1').text(ayuda);
        $('#btnSiguiente').prop('disabled', !listo);
    }

    // Al elegir un registro se quitan las demás coincidencias; "Cambiar" las vuelve a mostrar.
    function elegirCliente(c) {
        paso1.cliente = c;
        paso1.ultimaBusqueda = $.trim($('#buscarCliente').val());
        paso1.busqueda++; // una búsqueda pendiente ya no debe pintar resultados
        clearTimeout(paso1.temporizador);
        $('#buscarCliente').val('');
        $('#resultadosCliente').empty();
        $.get(WK.rutas.cliente, { id: c.id }, function (data) {
            ponerPromocion(data && data.length ? data[0].discountPercentage : 0);
        });
        reiniciarEnvio();
        consultarEnvio();
        pintarPaso1();
    }

    function guardarColonia() {
        var cliente = paso1.cliente, e = paso1.envio;
        var $boton = $('#guardarColonia'), $error = $('#envioColoniaError').text('');
        var colonia = $.trim($('#envioColoniaInput').val());
        if (!cliente) { return; }
        if (colonia === '') { $error.text('Elige una colonia de la lista.'); return; }
        $boton.prop('disabled', true);
        $.post(WK.rutas.clienteColonia, { clientId: cliente.id, colonia: colonia })
            .done(function (data) {
                if (!paso1.cliente || paso1.cliente.id !== cliente.id) { return; }
                if (data && data.errors) { $error.text(data.errors.join(' ')); return; }
                e.consulta++;
                e.error = null;
                cliente.colonia = data.colonia; // queda guardada en el registro del cliente
                $('#envioColoniaInput').val('');
                preseleccionarEnvio(data);
                pintarPaso1();
            })
            .fail(function (xhr) { $error.text(WK.errorDeConexion(xhr)); })
            .always(function () { $boton.prop('disabled', false); });
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
        paso1.envio.inicial = $('#panelEnvio').data('envio') || null;
        consultarEnvio();

        // Cambiar de tipo reinicia el envío (y lo consulta si queda en Domicilio).
        $paso1.on('change', 'input[name=type]', function () {
            reiniciarEnvio();
            consultarEnvio();
        });
        $paso1.on('change', 'input[name=type], input[name=table]', pintarPaso1);
        $paso1.on('change', 'input[name=envio]', function () {
            paso1.envio.elegido = this.value;
            pintarPaso1();
        });
        $('#aceptaExpress').on('click', function () {
            paso1.envio.acepta = true;
            pintarPaso1();
        });
        $('#declinaExpress').on('click', function () {
            paso1.envio.acepta = false;
            pintarPaso1();
        });
        // Conserva el cliente: Para llevar lo muestra como cliente registrado.
        $('#cambiarALlevar').on('click', function () {
            $('#tipo3').prop('checked', true).trigger('change');
        });
        $('#guardarColonia').on('click', guardarColonia);
        $('#envioColoniaInput').on('keydown', function (ev) {
            if (ev.which === 13) { ev.preventDefault(); guardarColonia(); }
        });
        $paso1.on('input', '#cuentaMesa, #cuentaLlevar, #telefonoRecoge, #montoAcordadoInput', pintarPaso1);
        $('#abrirBuscarCliente').on('click', function () {
            paso1.buscarEnLlevar = true;
            pintarPaso1();
            $('#buscarCliente').trigger('focus');
        });
        $('#buscarCliente').on('input', function () {
            clearTimeout(paso1.temporizador);
            paso1.temporizador = setTimeout(buscarClientes, 250);
        });
        $('#resultadosCliente').on('click', '.list-group-item', function () {
            elegirCliente($(this).data('cliente'));
        });
        // Vuelve a la lista de la última búsqueda; si no la hubo (comanda reabierta o cliente
        // recién registrado), busca por el teléfono para mostrar todas sus direcciones.
        $('#cambiarCliente').on('click', function () {
            var texto = paso1.ultimaBusqueda || (paso1.cliente && paso1.cliente.phone) || '';
            paso1.cliente = null;
            ponerPromocion(0);
            reiniciarEnvio();
            pintarPaso1();
            $('#buscarCliente').val(texto).trigger('focus');
            buscarClientes();
        });
        // Enter en un campo de texto no debe mandar el formulario a medias.
        $paso1.on('keydown', 'input[type=text], input[type=search], input[type=number]', function (e) {
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
