{{--
    Selector de colonia del catálogo de envío (lógica en public/js/wk-common.js, WK.selectorColonia).
    Reemplaza al <datalist>, que en varias tablets no despliega opciones.
    Parámetros: $coloniasEnvio (composer), $nombre (name del input, opcional), $idInput, $valor, $claseInput.
--}}
<div class="wk-colonia" data-colonias="{{ json_encode($coloniasEnvio) }}">
    <input type="text" @if (!empty($nombre)) name="{{ $nombre }}" @endif
        @if (!empty($idInput)) id="{{ $idInput }}" @endif
        class="form-control wk-colonia-input {{ $claseInput ?? '' }}" maxlength="150"
        autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
        role="combobox" aria-autocomplete="list" aria-expanded="false"
        placeholder="Escribe para buscar o toca para ver la lista" value="{{ $valor ?? '' }}">
    <div class="list-group wk-colonia-lista" role="listbox" hidden></div>
    <div class="invalid-feedback">Elige una colonia de la lista.</div>
</div>
