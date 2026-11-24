{{-- Campos compartidos por alta y edición de una colonia del catálogo de envío. --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Revisa los datos capturados:</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $cobertura = (string) old('cobertura', $tarifa->cobertura);
@endphp

<div class="row">
    <div class="form-group col-md-6">
        <label for="colonia"><strong>Colonia o agencia:</strong></label>
        <input type="text" id="colonia" name="colonia" class="form-control" maxlength="150" required
            value="{{ old('colonia', $tarifa->colonia) }}" placeholder="Ej. Acatlima">
        <small class="form-text text-muted">Tal como la conoce el cliente; el agente la busca por este nombre.</small>
    </div>
    <div class="form-group col-md-6">
        <label for="tipo"><strong>Tipo:</strong></label>
        <select id="tipo" name="tipo" class="custom-select">
            @foreach (\App\TarifaEnvio::TIPOS as $clave => $texto)
                <option value="{{ $clave }}" {{ old('tipo', $tarifa->tipo) == $clave ? 'selected' : '' }}>{{ $texto }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-6">
        <label for="cobertura"><strong>Cobertura:</strong></label>
        <select id="cobertura" name="cobertura" class="custom-select">
            @foreach (\App\TarifaEnvio::COBERTURAS as $clave => $texto)
                <option value="{{ $clave }}" {{ $cobertura === $clave ? 'selected' : '' }}>{{ $texto }}</option>
            @endforeach
        </select>
        <small class="form-text text-muted">
            Con "gratuito y express" el cliente elige; si pide express se cobra la tarifa de abajo.
        </small>
    </div>
    <div class="form-group col-md-6" id="grupoMontoExpress">
        <label for="monto_express"><strong>Tarifa express:</strong></label>
        <input type="number" id="monto_express" name="monto_express" class="form-control" min="0.01" max="99999" step="0.01"
            value="{{ old('monto_express', $tarifa->monto_express) }}" placeholder="Ej. 45.00">
        <small class="form-text text-muted">Tarifa del servicio express para esta colonia. Lo paga el cliente al repartidor.</small>
    </div>
</div>

@push('scripts')
<script>
    // Solo gratuito no lleva tarifa: se oculta y no se envía; las otras dos coberturas la exigen.
    $(function () {
        function pintarCobertura() {
            var conTarifa = $('#cobertura').val() !== '{{ \App\TarifaEnvio::COBERTURA_GRATUITO }}';
            $('#grupoMontoExpress').prop('hidden', !conTarifa);
            $('#monto_express').prop('disabled', !conTarifa).prop('required', conTarifa);
        }
        $('#cobertura').on('change', pintarCobertura);
        pintarCobertura();
    });
</script>
@endpush
