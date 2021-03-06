@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-16">
            <div class="card">
                <div class="card-header">Agregar Producto Nuevo
                  <div class="float-right">
                      <a class="btn btn-primary" href="{{ route('catalogos.index') }}">Regresar</a>
                  </div>
                </div>

                  @if ($errors->any())
                      <div class="alert alert-danger">
                          <strong>Whoops!</strong> There were some problems with your input.<br><br>
                          <ul>
                              @foreach ($errors->all() as $error)
                                  <li>{{ $error }}</li>
                              @endforeach
                          </ul>
                      </div>
                  @endif

                  <div class="card-body">
                      <form action="{{ route('catalogos.store') }}" method="POST" enctype="multipart/form-data">
                          @csrf

                           <div class="row">
                              <div class="col-xs-12 col-sm-12 col-md-12">
                                  <div class="form-group">
                                      <strong>Nombre:</strong>
                                      <input type="text" name="name" class="form-control" placeholder="Nombre" value="{{ old('name') }}">
                                  </div>
                              </div>
                              <div class="col-xs-12 col-sm-12 col-md-12">
                                  <div class="form-group">
                                      <strong>Descripción:</strong>
                                      <textarea class="form-control" style="height:100px" name="detail" placeholder="Descripcion"
                                         value="{{ old('detail') }}"></textarea>
                                  </div>
                              </div>

                              <div class="col-xs-12 col-sm-12 col-md-12">
                                  <div class="form-group">
                                      <strong>Precio:</strong>
                                      <input type="number" name="price" class="form-control" placeholder="Precio"
                                      pattern="[0-9]+([\.,][0-9]+)?" step="0.01" title="This should be a number with up to 2 decimal places."
                                      value="{{ old('price') }}">
                                  </div>
                              </div>

                              <div class="col-xs-12 col-sm-12 col-md-12">
                                  <div class="form-group">
                                      <strong>Categoria:</strong>
                                      <select class="custom-select" name="type"
                                          value="{{ old('type') }}">
                                          @foreach ($categorias as $key => $value)
                                          <option value="{{ $key }}" {{ ($key == 0) ? 'selected' : '' }}>{{$value}}</option>
                                          @endforeach
                                      </select>
                                  </div>
                              </div>

                              {{-- <div class="col-xs-12 col-sm-12 col-md-12">
                                  <div class="form-group"> --}}
                                      {{-- <strong>Imagen:</strong>
                                          <div class="custom-file">
                                              <input type="file" name="image" class="custom-file-input" id="image"
                                              aria-describedby="inputGroupFileAddon01">
                                              <label class="custom-file-label" for="image">Seleccionar imagen</label>
                                          </div> --}}
                                          {{-- <strong>Imagen:</strong>
                                              <input type="file" class="form-control-file" name="product_image" id="exampleInputFile" aria-describedby="fileHelp">
                                              <small id="fileHelp" class="form-text text-muted">Please upload a valid image file. Size of image should not be more than 2MB.</small>
                                          </div>
                                  </div>
                              </div> --}}

                              <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                                      <button type="submit" class="btn btn-primary">Guardar</button>
                              </div>
                          </div>

                      </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
