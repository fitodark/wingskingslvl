<div class="row">
    <div class="form-group col-md-6">
        <strong>Proveedor:</strong>
        <input id="proveedor" type="text" name="proveedor" class="form-control" 
            placeholder="Proveedor" value="{{ $compra->proveedor }}">
    </div>
    <div class="form-group col-md-6">
        <strong>Observaciones:</strong>
        <input id="observaciones" type="text" name="observaciones" class="form-control" 
            placeholder="Observaciones" value="{{ $compra->observaciones }}">
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        <strong>Monto:</strong>
        <input id="monto" type="text" name="monto" class="form-control" 
            placeholder="Monto de la nota" value="{{ $compra->monto }}">
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
