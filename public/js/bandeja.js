/*
 * Bandeja de comandas: refresco automático, aviso visual y sonido de pedidos nuevos de WhatsApp.
 * Requiere wk-common.js y window.WK.bandeja (puntoventa/comandas/index.blade.php).
 *
 * Cada WK.bandeja.intervalo segundos pregunta a /comandas/bandeja si cambió la firma de las ventas
 * abiertas. Sin cambios el servidor responde 204; con cambios manda la tabla ya pintada.
 * Cuando haya websockets (BANDEJA_WEBSOCKETS=true), el evento solo tiene que llamar a
 * WK.bandeja.refrescar() y avisar con WK.bandeja.socketConectado(true|false).
 * Ver docs/plan-bandeja-tiempo-real.md.
 */
(function ($, WK) {
    'use strict';
    var cfg = WK.bandeja;
    if (!cfg || cfg.iniciado) { return; }
    cfg.iniciado = true;

    var $bandeja = $('#wkBandeja');
    var $avisos = $('#wkAvisos');
    var estado = {
        firma: cfg.firma,
        maxFolio: cfg.maxFolio,
        avisados: {},       // folios ya avisados (no repetir mientras haya un modal abierto)
        enCurso: false,
        detenido: false,
        temporizador: null,
        socket: false
    };

    /* ---------- Sonido (Web Audio, sin archivos) ---------- */

    var audio = null;
    var $activarSonido = $('#wkActivarSonido');

    function sonidoListo() {
        return !!(audio && audio.state === 'running');
    }

    function pintarBotonSonido() {
        $activarSonido.toggle(!sonidoListo());
    }

    // Los navegadores solo dejan sonar audio después de que el usuario toca la pantalla.
    function desbloquearSonido() {
        var Contexto = window.AudioContext || window.webkitAudioContext;
        if (!Contexto) { return; }
        if (!audio) { audio = new Contexto(); }
        if (audio.state === 'suspended') {
            audio.resume().then(pintarBotonSonido, pintarBotonSonido);
        }
        pintarBotonSonido();
    }

    function tono(frecuencia, inicio, duracion) {
        var osc = audio.createOscillator();
        var vol = audio.createGain();
        osc.type = 'sine';
        osc.frequency.value = frecuencia;
        vol.gain.setValueAtTime(0.0001, inicio);
        vol.gain.exponentialRampToValueAtTime(0.5, inicio + 0.02);
        vol.gain.exponentialRampToValueAtTime(0.0001, inicio + duracion);
        osc.connect(vol);
        vol.connect(audio.destination);
        osc.start(inicio);
        osc.stop(inicio + duracion + 0.05);
    }

    // "Ding-dong" dos veces.
    function sonar() {
        if (!sonidoListo()) { pintarBotonSonido(); return; }
        var t = audio.currentTime;
        [0, 0.9].forEach(function (desfase) {
            tono(1046, t + desfase, 0.35);
            tono(784, t + desfase + 0.3, 0.5);
        });
    }

    $(document).on('pointerdown keydown', desbloquearSonido);
    $activarSonido.on('click', function () { desbloquearSonido(); sonar(); });
    pintarBotonSonido();

    /* ---------- Aviso visual ---------- */

    function avisar(nuevas) {
        var hubo = false;
        $.each(nuevas || [], function (i, venta) {
            if (estado.avisados[venta.folio]) { return; }
            estado.avisados[venta.folio] = true;
            hubo = true;

            var $aviso = $('<div class="wk-aviso" role="alert">');
            var $texto = $('<div class="wk-aviso-texto">')
                .append($('<div class="wk-aviso-titulo">')
                    .append('<i class="fa-solid fa-bell"></i> ')
                    .append(document.createTextNode('Nuevo pedido WhatsApp #' + venta.folio)))
                .append($('<div>').text([venta.cliente, venta.tipo].filter(Boolean).join(' · ')));
            if (venta.express) {
                $texto.append($('<span class="badge badge-warning">').text('Express'));
            }
            var $cerrar = $('<button type="button" class="btn btn-sm btn-light wk-aviso-cerrar" title="Cerrar aviso">')
                .append('<i class="fa-solid fa-xmark"></i>');
            $aviso.append($texto, $cerrar).appendTo($avisos);
        });
        // Máximo 5 avisos a la vista.
        $avisos.children().slice(0, -5).remove();
        if (hubo) { sonar(); }
    }

    // Tocar el aviso lo cierra (y de paso habilita el sonido).
    $avisos.on('click', '.wk-aviso', function () { $(this).remove(); });

    /* ---------- Refresco ---------- */

    function pagina() {
        var m = /[?&]page=(\d+)/.exec(window.location.search);
        return m ? m[1] : 1;
    }

    function hayModalAbierto() {
        return $('.modal.show').length > 0;
    }

    function pintar(data) {
        var anterior = estado.maxFolio;
        $bandeja.html(data.html);
        $bandeja.find('tr[data-folio]').filter(function () {
            return parseInt($(this).data('folio'), 10) > anterior;
        }).addClass('wk-fila-nueva');
        estado.firma = data.firma;
        estado.maxFolio = data.maxFolio;
    }

    function intervalo() {
        return (cfg.websockets && estado.socket ? cfg.intervaloRespaldo : cfg.intervalo) * 1000;
    }

    function programar() {
        clearTimeout(estado.temporizador);
        if (estado.detenido || document.hidden) { return; }
        estado.temporizador = setTimeout(refrescar, intervalo());
    }

    function detener(mensaje) {
        estado.detenido = true;
        clearTimeout(estado.temporizador);
        $('<div class="wk-aviso wk-aviso-error" role="alert">')
            .append($('<div class="wk-aviso-texto">').text(mensaje))
            .appendTo($avisos);
    }

    function refrescar() {
        if (estado.enCurso || estado.detenido) { return; }
        estado.enCurso = true;
        $.ajax({
            url: cfg.url,
            method: 'GET',
            dataType: 'json',
            cache: false,
            data: { firma: estado.firma, desde: estado.maxFolio, page: pagina() }
        }).done(function (data, status, xhr) {
            if (xhr.status === 204 || !data) { return; }
            avisar(data.nuevas);
            // Con un modal abierto (cobrar, etc.) no se toca la tabla; se reintenta al cerrarlo.
            if (!hayModalAbierto()) { pintar(data); }
        }).fail(function (xhr) {
            if (xhr.status === 401 || xhr.status === 419) {
                detener('Tu sesión terminó: recarga la página para seguir viendo pedidos nuevos.');
            } else if (xhr.status === 403) {
                detener('Sin permiso para ver la bandeja.');
            }
            // Otros errores (red, servidor): se reintenta en la siguiente vuelta.
        }).always(function () {
            estado.enCurso = false;
            programar();
        });
    }

    $(document).on('hidden.bs.modal', function () {
        if (!hayModalAbierto()) { refrescar(); }
    });

    // Con la pestaña oculta no se consulta; al volver se consulta de inmediato.
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            clearTimeout(estado.temporizador);
        } else {
            refrescar();
        }
    });

    // Para websockets: el evento llama a refrescar() y avisa si el socket está arriba.
    cfg.refrescar = refrescar;
    cfg.socketConectado = function (conectado) {
        estado.socket = !!conectado;
        programar();
    };

    programar();
})(jQuery, window.WK);
