<input type="text" hidden name="idInventarioProducto" id="idInventarioProducto">
<div class="row">
    <div class="form-group col-md-6">
        <strong>Producto:</strong>
        @if (empty($inventarioproductos))
            <select name="idProducto" id="idProducto" class="form-control">
            @foreach($items as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
            </select>
        @else
            <input id="idProducto" type="text" name="idProducto" class="form-control" 
                value="{{ $inventarioproductos->idProducto }}" required>
        @endif
        <div class="invalid-feedback">El producto es obligatorio.</div>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        <strong>Minimo:</strong>
        @if (empty($inventarioproductos))
            <input id="cantidadMinima" type="text" name="cantidadMinima" class="form-control" 
                placeholder="Cantidad Minima" value="{{ old('cantidadMinima') }}" required>
        @else
            <input id="cantidadMinima" type="text" name="cantidadMinima" class="form-control" 
                placeholder="Cantidad Minima" value="{{ $inventarioproductos->cantidadMinima }}" required>
        @endif
        <div class="invalid-feedback">El minimo es obligatorio.</div>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        <strong>Maximo:</strong>
        @if (empty($inventarioproductos))
            <input id="cantidadMaxima" type="text" name="cantidadMaxima" class="form-control" 
                placeholder="Cantidad Maxima" value="{{ old('cantidadMaxima') }}" required>
        @else
            <input id="cantidadMaxima" type="text" name="cantidadMaxima" class="form-control" 
                placeholder="Cantidad Maxima" value="{{ $inventarioproductos->cantidadMaxima }}" required>
        @endif
        <div class="invalid-feedback">El maximo es obligatorio.</div>
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
