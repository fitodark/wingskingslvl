<input type="text" hidden name="ventaid" id="ventaid">
<input type="text" hidden name="clientId" id="clientId">
<div class="row">
    <div class="form-group col-md-6">
        <strong>Nombre:</strong>
        @if (empty($cliente))
            <input id="modalClientName" type="text" name="name" class="form-control" placeholder="Nombre" value="{{ old('name') }}" required>
        @else
            <input id="modalClientName" type="text" name="name" class="form-control" placeholder="Nombre" value="{{ $cliente->name }}" required>
        @endif
        <div class="invalid-feedback">
            El nombre del cliente es obligatorio.
        </div>
    </div>
    <div class="form-group col-md-6">
        <strong>Dirección:</strong>
        @if (empty($cliente))
            <input id="modalClientAddress" type="text" name="address" class="form-control" placeholder="Dirección" value="{{ old('address') }}" required>
        @else
            <input id="modalClientAddress" type="text" name="address" class="form-control" placeholder="Dirección" value="{{ $cliente->address }}" required>
        @endif
        <div class="invalid-feedback">
            El Telefono del cliente es obligatorio.
        </div>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        <strong>Teléfono:</strong>
        @if (empty($cliente))
            <input id="modalClientPhone" type="text" name="phone" class="form-control" placeholder="Teléfono" value="{{ old('phone') }}" required>
        @else
            <input id="modalClientPhone" type="text" name="phone" class="form-control" placeholder="Teléfono" value="{{ $cliente->phone }}" required>
        @endif
        <div class="invalid-feedback">
            La dirección del cliente es obligatoria.
        </div>
    </div>

    <div class="form-group col-md-6">
        <strong>Referencia:</strong>
        @if (empty($cliente))
            <input id="modalClientReference" type="text" name="reference" class="form-control" placeholder="Referencia" value="{{ old('reference') }}" required>
        @else
            <input id="modalClientReference" type="text" name="reference" class="form-control" placeholder="Referencia" value="{{ $cliente->reference }}" required>
        @endif
        <!-- <div id="clientReferenceError" class="alert alert-danger alert-danger-reference" style="display:none"></div> -->
        <div class="invalid-feedback">
            La referencia del cliente es obligatoria.
        </div>
    </div>
</div>
