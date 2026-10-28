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
    $gratuito = (string) old('envio_gratuito', $tarifa->envio_gratuito ? '1' : '0');
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
        <label for="envio_gratuito"><strong>Cobertura:</strong></label>
        <select id="envio_gratuito" name="envio_gratuito" class="custom-select">
            <option value="1" {{ $gratuito === '1' ? 'selected' : '' }}>Con envío gratuito</option>
            <option value="0" {{ $gratuito === '0' ? 'selected' : '' }}>Solo envío express (por distancia)</option>
        </select>
    </div>
    <div class="form-group col-md-6">
        <label for="monto_express"><strong>Tarifa express:</strong></label>
        <input type="number" id="monto_express" name="monto_express" class="form-control" min="0.01" max="99999" step="0.01"
            value="{{ old('monto_express', $tarifa->monto_express) }}" placeholder="Ej. 45.00">
        <small class="form-text text-muted">Obligatoria si solo hay envío express. Vacía = sin tarifa express cargada.</small>
    </div>
</div>
