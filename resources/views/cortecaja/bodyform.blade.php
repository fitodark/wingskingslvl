<div class="row">
    <div class="form-group col-md-6">
        <strong>Fecha Apertura:</strong>
        <input type="text" class="form-control datepickercorteformat" name="fechaapertura" 
            placeholder="Fecha Apertura">
        <div class="input-group-addon">
            <span class="glyphicon glyphicon-th"></span>
        </div>
        <div class="invalid-feedback">La fecha de apertura es obligatoria.</div>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        <strong>Monto Apertura:</strong>
        <input id="montoapertura" type="text" name="montoapertura" class="form-control" 
            placeholder="Monto Apertura" value="{{ old('montoapertura') }}" required>
        <div class="invalid-feedback">El monto de apertura es obligatorio.</div>
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
