<div class="row">
    <div class="form-group col-md-6">
        <strong>Descripcion:</strong>
        <input id="descripcion" type="text" name="descripcion" class="form-control" 
            placeholder="Descripcion del movimiento" value="{{ old('descripcion') }}" required>
        <div class="invalid-feedback">La descripcion es obligatoria.</div>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        <strong>Monto:</strong>
        <input id="monto" type="text" name="monto" class="form-control" 
            placeholder="Monto" value="{{ old('monto') }}" required>
        <div class="invalid-feedback">El monto es obligatorio.</div>
    </div>
</div>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
